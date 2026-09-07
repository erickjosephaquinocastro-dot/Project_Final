@echo off
setlocal
cd /d "%~dp0"

set "HOST1=192.168.1.40"
set "HOST2=192.168.1.91"
set "HOST3=192.168.1.46"
set /p "PASSWORD=Contrasena comun de los tres biometricos: "
if "%PASSWORD%"=="" goto :missingPassword

echo Iniciando los tres biometricos...
start "Bio1" "%ComSpec%" /k call "%~dp0iniciar-eventos.bat" "%HOST1%" "%PASSWORD%"
start "Bio3" "%ComSpec%" /k call "%~dp0iniciar-eventos-2.bat" "%HOST2%" "%PASSWORD%"
start "Bio2" "%ComSpec%" /k call "%~dp0iniciar-eventos-3.bat" "%HOST3%" "%PASSWORD%"
echo.
echo Se abrieron tres ventanas usando el mismo usuario admin y la misma contrasena.
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