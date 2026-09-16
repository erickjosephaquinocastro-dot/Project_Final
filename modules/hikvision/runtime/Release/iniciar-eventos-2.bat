@echo off
setlocal
cd /d "%~dp0"
if exist "%~dp0devices.ini" for /f "usebackq tokens=1,* delims==" %%A in ("%~dp0devices.ini") do set "%%A=%%B"
if "%~1"=="" (set "HOST=%HOST2%") else (set "HOST=%~1")
if "%PORT2%"=="" (set "PORT=8000") else (set "PORT=%PORT2%")
set "USER=admin"
set "EVENTS=%~dp0events-2.jsonl"

echo Iniciando listener Hikvision para %HOST%:%PORT%...
echo Escribe la contrasena cuando el programa la solicite.
echo No cierres esta ventana mientras quieras recibir eventos.
echo.
if "%~2"=="" (hikvision-connector.exe --listen "%HOST%" "%PORT%" "%USER%" "%EVENTS%") else (echo %~2|hikvision-connector.exe --listen "%HOST%" "%PORT%" "%USER%" "%EVENTS%")
echo.
echo El listener termino con codigo %ERRORLEVEL%.
pause