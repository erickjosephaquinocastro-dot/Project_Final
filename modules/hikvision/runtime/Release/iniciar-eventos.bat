@echo off
setlocal
cd /d "%~dp0"
if "%~1"=="" (set "HOST=192.168.1.91") else (set "HOST=%~1")
set "PORT=8000"
set "USER=admin"
set "EVENTS=%~dp0events-1.jsonl"

echo Iniciando listener Hikvision para %HOST%:%PORT%...
echo Escribe la contrasena cuando el programa la solicite.
echo No cierres esta ventana mientras quieras recibir eventos.
echo.
if "%~2"=="" (hikvision-connector.exe --listen "%HOST%" "%PORT%" "%USER%" "%EVENTS%") else (echo %~2|hikvision-connector.exe --listen "%HOST%" "%PORT%" "%USER%" "%EVENTS%")
echo.
echo El listener termino con codigo %ERRORLEVEL%.
pause
