@echo off
setlocal
cd /d "%~dp0"
if not defined HOST set "HOST=REEMPLAZA_CON_IP_DEL_BIOMETRICO_DE_DOCENTES"
set "PORT=8000"
set "USER=admin"
set "EVENTS=%~dp0events-3.jsonl"

echo Iniciando listener Hikvision para %HOST%:%PORT%...
echo Escribe la contrasena cuando el programa la solicite.
echo No cierres esta ventana mientras quieras recibir eventos.
echo.
hikvision-connector.exe --listen "%HOST%" "%PORT%" "%USER%" "%EVENTS%"
echo.
echo El listener termino con codigo %ERRORLEVEL%.
pause