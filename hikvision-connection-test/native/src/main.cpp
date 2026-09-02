#include <windows.h>
#include <iostream>
#include <fstream>
#include <mutex>
#include <csignal>
#include <string>
#include <cstdlib>
#include <cstring>
#include <unordered_map>
#include <condition_variable>
#include <chrono>
#include "HCNetSDK.h"

namespace {

std::string JsonEscape(const std::string& value);

std::ofstream eventFile;
std::mutex eventFileMutex;
volatile std::sig_atomic_t keepListening = 1;
LONG alarmHandle = -1;
LONG gCurrentUserId = -1;
std::unordered_map<std::string, std::string> cardNames;
std::unordered_map<DWORD, std::string> employeeNames;
std::mutex cardNamesMutex;
std::condition_variable cardNamesReady;
bool cardNamesFinished = false;

std::string TrimNullTerminatedString(const std::string& value)
{
    const std::string::size_type terminator = value.find('\0');
    if (terminator != std::string::npos)
    {
        return value.substr(0, terminator);
    }
    return value;
}

bool IsFaceEventType(BYTE eventType)
{
    switch (eventType)
    {
    case EVENT_ACS_FACE_VERIFY_PASS:
    case EVENT_ACS_FACE_VERIFY_FAIL:
    case EVENT_ACS_FACE_AND_FP_VERIFY_PASS:
    case EVENT_ACS_FACE_AND_FP_VERIFY_FAIL:
    case EVENT_ACS_FACE_AND_FP_VERIFY_TIMEOUT:
    case EVENT_ACS_FACE_AND_PW_VERIFY_PASS:
    case EVENT_ACS_FACE_AND_PW_VERIFY_FAIL:
    case EVENT_ACS_FACE_AND_PW_VERIFY_TIMEOUT:
    case EVENT_ACS_FACE_AND_CARD_VERIFY_PASS:
    case EVENT_ACS_FACE_AND_CARD_VERIFY_FAIL:
    case EVENT_ACS_FACE_AND_CARD_VERIFY_TIMEOUT:
    case EVENT_ACS_FACE_AND_PW_AND_FP_VERIFY_PASS:
    case EVENT_ACS_FACE_AND_PW_AND_FP_VERIFY_FAIL:
    case EVENT_ACS_FACE_AND_PW_AND_FP_VERIFY_TIMEOUT:
    case EVENT_ACS_FACE_AND_CARD_AND_FP_VERIFY_PASS:
    case EVENT_ACS_FACE_AND_CARD_AND_FP_VERIFY_FAIL:
    case EVENT_ACS_FACE_AND_CARD_AND_FP_VERIFY_TIMEOUT:
    case EVENT_ACS_EMPLOYEENO_AND_FACE_VERIFY_PASS:
    case EVENT_ACS_EMPLOYEENO_AND_FACE_VERIFY_FAIL:
    case EVENT_ACS_EMPLOYEENO_AND_FACE_VERIFY_TIMEOUT:
    case EVENT_ACS_FACE_RECOGNIZE_FAIL:
        return true;
    default:
        return false;
    }
}

bool IsFaceVerifyMode(BYTE verifyMode)
{
    switch (verifyMode)
    {
    case 10: case 11: case 12: case 13: case 14:
    case 19: case 20: case 21: case 22: case 23: case 24: case 25:
    case 26: case 28: case 29: case 30: case 31: case 33: case 34:
        return true;
    default:
        return false;
    }
}

std::string ResolveCardUserName(const std::string& cardNumber, DWORD employeeNumber)
{
    if (!cardNumber.empty())
    {
        std::lock_guard<std::mutex> lock(cardNamesMutex);
        const auto cardName = cardNames.find(cardNumber);
        if (cardName != cardNames.end())
        {
            return JsonEscape(cardName->second);
        }
    }

    std::lock_guard<std::mutex> lock(cardNamesMutex);
    const auto employeeName = employeeNames.find(employeeNumber);
    if (employeeName == employeeNames.end())
    {
        return {};
    }
    return JsonEscape(employeeName->second);
}

void CALLBACK CardConfigCallback(DWORD callbackType, void* buffer, DWORD bufferLength, void*)
{
    if (callbackType == NET_SDK_CALLBACK_TYPE_DATA && buffer != nullptr && bufferLength >= sizeof(NET_DVR_CARD_CFG_V50))
    {
        const auto* card = static_cast<const NET_DVR_CARD_CFG_V50*>(buffer);
        const std::string cardNumber = TrimNullTerminatedString(std::string(reinterpret_cast<const char*>(card->byCardNo), ACS_CARD_NO_LEN));
        const std::string name = TrimNullTerminatedString(std::string(reinterpret_cast<const char*>(card->byName), NAME_LEN));
        if (!cardNumber.empty() && !name.empty())
        {
            std::lock_guard<std::mutex> lock(cardNamesMutex);
            cardNames[cardNumber] = name;
            if (card->dwEmployeeNo != 0)
            {
                employeeNames[card->dwEmployeeNo] = name;
            }
        }
        return;
    }

    if (callbackType == NET_SDK_CALLBACK_TYPE_STATUS && buffer != nullptr && bufferLength >= sizeof(DWORD))
    {
        const DWORD status = *static_cast<const DWORD*>(buffer);
        if (status == NET_SDK_CALLBACK_STATUS_SUCCESS || status == NET_SDK_CALLBACK_STATUS_EXCEPTION || status == NET_SDK_CALLBACK_STATUS_FAILED)
        {
            {
                std::lock_guard<std::mutex> lock(cardNamesMutex);
                cardNamesFinished = true;
            }
            cardNamesReady.notify_one();
        }
    }
}

void LoadCardNames(LONG userId)
{
    NET_DVR_CARD_CFG_COND condition = {};
    condition.dwSize = sizeof(condition);
    condition.dwCardNum = 0xffffffff;

    {
        std::lock_guard<std::mutex> lock(cardNamesMutex);
        cardNamesFinished = false;
    }
    LONG handle = NET_DVR_StartRemoteConfig(userId, NET_DVR_GET_CARD_CFG_V50, &condition, sizeof(condition), CardConfigCallback, nullptr);
    if (handle < 0)
    {
        return;
    }

    {
        std::unique_lock<std::mutex> lock(cardNamesMutex);
        cardNamesReady.wait_for(lock, std::chrono::seconds(10), [] { return cardNamesFinished; });
    }
    NET_DVR_StopRemoteConfig(handle);
}

std::string JsonEscape(const std::string& value)
{
    std::string result;
    for (const char character : value)
    {
        switch (character)
        {
        case '\\': result += "\\\\"; break;
        case '"': result += "\\\""; break;
        case '\n': result += "\\n"; break;
        case '\r': result += "\\r"; break;
        case '\t': result += "\\t"; break;
        default: result += character; break;
        }
    }
    return result;
}

void PrintFailure(const char* stage, DWORD errorCode)
{
    LONG mutableErrorCode = static_cast<LONG>(errorCode);
    const char* errorMessage = NET_DVR_GetErrorMsg(&mutableErrorCode);
    const bool alarmLimitReached = errorCode == NET_ERR_DEPLOY_EXCEED_MAX;
    const char* fallbackMessage = alarmLimitReached
        ? "El dispositivo ya alcanzo el maximo de receptores de alarmas; cierra por completo iVMS y sus procesos en segundo plano."
        : "Unknown SDK error";
    std::cout << "{\"ok\":false,\"stage\":\"" << stage
              << "\",\"error_code\":" << errorCode
              << ",\"error_message\":\""
              << JsonEscape(alarmLimitReached ? fallbackMessage : (errorMessage == nullptr ? fallbackMessage : errorMessage))
              << "\"}" << std::endl;
}

bool IsValidPort(const char* value, unsigned short& port)
{
    if (value == nullptr || *value == '\0')
    {
        return false;
    }

    char* end = nullptr;
    const unsigned long parsed = std::strtoul(value, &end, 10);
    if (*end != '\0' || parsed == 0 || parsed > 65535)
    {
        return false;
    }

    port = static_cast<unsigned short>(parsed);
    return true;
}

void CALLBACK MessageCallback(LONG command, NET_DVR_ALARMER*, char* alarmInfo, DWORD alarmInfoLength, void*)
{
    if (command != COMM_ALARM_ACS || alarmInfo == nullptr || alarmInfoLength < sizeof(NET_DVR_ACS_ALARM_INFO))
    {
        return;
    }

    NET_DVR_ACS_ALARM_INFO alarm = {};
    memcpy(&alarm, alarmInfo, sizeof(alarm));
    const NET_DVR_ACS_EVENT_INFO& event = alarm.struAcsEventInfo;
    BYTE currentVerifyMode = 0;
    if (alarm.byAcsEventInfoExtend == 1 && alarm.pAcsEventInfoExtend != nullptr)
    {
        NET_DVR_ACS_EVENT_INFO_EXTEND eventExtend = {};
        memcpy(&eventExtend, alarm.pAcsEventInfoExtend, sizeof(eventExtend));
        currentVerifyMode = eventExtend.byCurrentVerifyMode;
    }
    if (!IsFaceEventType(event.byType) && !IsFaceVerifyMode(currentVerifyMode))
    {
        return;
    }

    std::string cardNumber(reinterpret_cast<const char*>(event.byCardNo), ACS_CARD_NO_LEN);
    cardNumber = TrimNullTerminatedString(cardNumber);
    const std::string employeeName = ResolveCardUserName(cardNumber, event.dwEmployeeNo);

    std::lock_guard<std::mutex> lock(eventFileMutex);
    if (eventFile.is_open())
    {
        eventFile << "{\"major\":" << alarm.dwMajor
                  << ",\"minor\":" << alarm.dwMinor
                  << ",\"year\":" << alarm.struTime.dwYear
                  << ",\"month\":" << alarm.struTime.dwMonth
                  << ",\"day\":" << alarm.struTime.dwDay
                  << ",\"hour\":" << alarm.struTime.dwHour
                  << ",\"minute\":" << alarm.struTime.dwMinute
                  << ",\"second\":" << alarm.struTime.dwSecond
                  << ",\"card_number\":\"" << JsonEscape(cardNumber)
                  << "\",\"employee_name\":\"" << employeeName
                  << "\",\"employee_number\":" << event.dwEmployeeNo
                  << ",\"door\":" << event.dwDoorNo
                  << ",\"reader\":" << event.dwCardReaderNo
                  << ",\"verify\":" << event.dwVerifyNo
                  << ",\"event_type\":" << static_cast<unsigned int>(event.byType)
                  << ",\"verification_mode\":" << static_cast<unsigned int>(currentVerifyMode)
                  << "}" << std::endl;
    }
}

BOOL WINAPI ConsoleHandler(DWORD signal)
{
    if (signal == CTRL_C_EVENT || signal == CTRL_BREAK_EVENT || signal == CTRL_CLOSE_EVENT)
    {
        keepListening = 0;
        return TRUE;
    }
    return FALSE;
}

} // namespace

int main(int argc, char* argv[])
{
    const bool listenMode = argc == 6 && std::string(argv[1]) == "--listen";
    if (argc != 4 && !listenMode)
    {
        std::cout << "{\"ok\":false,\"stage\":\"input\",\"error_message\":\"Usage: hikvision-connector.exe <host> <port> <username> or --listen <host> <port> <username> <events-file>, password on stdin\"}" << std::endl;
        return 2;
    }

    const int hostIndex = listenMode ? 2 : 1;
    const int portIndex = listenMode ? 3 : 2;
    const int usernameIndex = listenMode ? 4 : 3;
    unsigned short port = 0;
    if (!IsValidPort(argv[portIndex], port) || std::string(argv[hostIndex]).empty() || std::string(argv[usernameIndex]).empty())
    {
        std::cout << "{\"ok\":false,\"stage\":\"input\",\"error_message\":\"Invalid host, port or username\"}" << std::endl;
        return 2;
    }

    std::string password;
    std::getline(std::cin, password);
    if (password.empty() || password.size() >= NET_DVR_LOGIN_PASSWD_MAX_LEN)
    {
        std::cout << "{\"ok\":false,\"stage\":\"input\",\"error_message\":\"Password is required and must be shorter than 64 characters\"}" << std::endl;
        return 2;
    }

    if (!NET_DVR_Init())
    {
        PrintFailure("init", NET_DVR_GetLastError());
        return 1;
    }

    NET_DVR_SetConnectTime(5000, 1);
    NET_DVR_SetReconnect(30000, TRUE);

    if (listenMode)
    {
        eventFile.open(argv[5], std::ios::out | std::ios::trunc);
        if (!eventFile.is_open())
        {
            PrintFailure("events_file", ERROR_OPEN_FAILED);
            NET_DVR_Cleanup();
            return 1;
        }
        SetConsoleCtrlHandler(ConsoleHandler, TRUE);
        if (!NET_DVR_SetDVRMessageCallBack_V51(0, MessageCallback, nullptr))
        {
            PrintFailure("callback", NET_DVR_GetLastError());
            eventFile.close();
            NET_DVR_Cleanup();
            return 1;
        }
    }

    LONG userId = -1;
    NET_DVR_DEVICEINFO_V40 deviceInfo = {};
    NET_DVR_USER_LOGIN_INFO loginInfo = {};
    loginInfo.wPort = port;
    loginInfo.bUseAsynLogin = FALSE;
    loginInfo.byHttps = 0;
    loginInfo.byLoginMode = 0;
    strcpy_s(loginInfo.sDeviceAddress, argv[hostIndex]);
    strcpy_s(loginInfo.sUserName, argv[usernameIndex]);
    strcpy_s(loginInfo.sPassword, password.c_str());

    userId = NET_DVR_Login_V40(&loginInfo, &deviceInfo);
    if (userId < 0)
    {
        PrintFailure("login", NET_DVR_GetLastError());
        NET_DVR_Cleanup();
        return 1;
    }

    gCurrentUserId = userId;

    if (listenMode)
    {
        NET_DVR_SETUPALARM_PARAM_V50 alarmParameters = {};
        alarmParameters.dwSize = sizeof(alarmParameters);
        alarmParameters.byLevel = 0;
        alarmParameters.byRetAlarmTypeV40 = 1;
        alarmParameters.byRetDevInfoVersion = 1;
        alarmHandle = NET_DVR_SetupAlarmChan_V50(userId, &alarmParameters, nullptr, 0);
        if (alarmHandle < 0)
        {
            PrintFailure("setup_alarm", NET_DVR_GetLastError());
            NET_DVR_Logout_V30(userId);
            eventFile.close();
            NET_DVR_Cleanup();
            return 1;
        }
    }

    std::cout << "{\"ok\":true,\"stage\":\"login\",\"user_id\":" << userId
              << ",\"protocol\":\"" << (deviceInfo.byLoginMode == 0 ? "private" : "isapi")
              << "\",\"password_level\":" << static_cast<unsigned int>(deviceInfo.byPasswordLevel)
              << ",\"stream_encryption\":" << static_cast<unsigned int>(deviceInfo.bySupportStreamEncrypt)
              << "}" << std::endl;

    if (listenMode)
    {
        std::cout << "{\"ok\":true,\"stage\":\"listening\"}" << std::endl;
        LoadCardNames(userId);
        while (keepListening != 0)
        {
            Sleep(500);
        }
    }

    if (alarmHandle >= 0)
    {
        NET_DVR_CloseAlarmChan_V30(alarmHandle);
        alarmHandle = -1;
    }

    const bool logoutOk = NET_DVR_Logout_V30(userId) == TRUE;
    const DWORD logoutError = logoutOk ? 0 : NET_DVR_GetLastError();
    const bool cleanupOk = NET_DVR_Cleanup() == TRUE;
    if (eventFile.is_open())
    {
        eventFile.close();
    }

    if (!logoutOk || !cleanupOk)
    {
        std::cout << "{\"ok\":false,\"stage\":\"cleanup\",\"logout\":"
                  << (logoutOk ? "true" : "false") << ",\"logout_error_code\":" << logoutError
                  << ",\"cleanup\":" << (cleanupOk ? "true" : "false") << "}" << std::endl;
        return 1;
    }

    return 0;
}
