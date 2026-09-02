@echo off
setlocal
cd /d "%~dp0"

set "HOST2=192.168.1.194"
set /p "PASSWORD=Contrasena comun de los tres biometricos: "
if "%PASSWORD%"=="" goto :missingPassword

echo Iniciando los dos biometricos de estudiantes...
start "Estudiantes 1" "%ComSpec%" /k call "%~dp0iniciar-eventos.bat" "192.168.1.91" "%PASSWORD%"
start "Estudiantes 2" "%ComSpec%" /k call "%~dp0iniciar-eventos-2.bat" "%HOST2%" "%PASSWORD%"
echo.
echo Se abrieron dos ventanas usando el mismo usuario admin y la misma contrasena.
set "PASSWORD="
pause
exit /b

:missingIp
echo Debes escribir la IP del segundo biometrico de estudiantes.
pause
exit /b

:missingPassword
echo Debes escribir la contrasena comun de los tres biometricos.
pause