# Prueba minima de conexion Hikvision

Este proyecto esta separado del SDK original. Su primera version solo valida:

`PHP -> ejecutable C++ -> HCNetSDK -> login -> logout -> cleanup`

## Estructura

- `native/`: proyecto Visual Studio x64 del conector nativo.
- `web/`: formulario PHP y endpoint para probar desde XAMPP.
- `runtime/`: salida del ejecutable y DLLs necesarias del SDK.
- `docs/`: notas de configuracion y pruebas.

## Compilar

1. Abrir `native/hikvision-connector.sln` con Visual Studio 2026 o una versión que tenga C++ x64 y el toolset `v145`.
2. Seleccionar `Release` y `x64`.
3. Compilar el proyecto.
4. Copiar las DLL del SDK desde `EN-HCNetSDK.../lib/` a `runtime/Release/`, conservando `HCNetSDKCom/`.
5. Confirmar que `hikvision-connector.exe` y `HCNetSDK.dll` quedan en la misma carpeta.

El SDK original no se modifica.

## Ejecutar sin PHP

Desde `runtime/Release/`:

```text
cmd /c "echo TU_PASSWORD|hikvision-connector.exe 192.168.1.64 8000 admin"
```

No guardes la contraseña en scripts ni en el repositorio. El ejemplo anterior solo ilustra el formato; usa una terminal local y elimina cualquier historial sensible.

## Ejecutar desde XAMPP

En esta instalación Apache usa el puerto `8080`. Abre `http://localhost:8080/Project_Final/` para cargar el índice general. En `web/config.php`, ajusta la ruta absoluta de `hikvision-connector.exe` después de compilar.

La página web valida la entrada y devuelve el JSON del conector. Esta primera versión está pensada para pruebas locales y no debe publicarse en Internet.

## Escuchar eventos ACS

Por el momento se utilizaran los dos dispositivos de estudiantes. El segundo biometrico usa actualmente la IP `192.168.1.194`. Luego puedes agregar el biometrico de docentes configurando su IP. Para iniciar los dos actuales con un solo archivo:

```text
\.\iniciar-todos-eventos.bat
```

Se abrirán dos ventanas, una por dispositivo de estudiantes. El usuario `admin` y la contraseña se solicitan una sola vez y se reutilizan para ambos equipos. Visita `http://localhost:8080/Project_Final/hikvision-connection-test/web/dashboard.php`. Cada acceso recibido se guardará en `events-1.jsonl` o `events-2.jsonl` y aparecerá con la etiqueta del dispositivo. No abras iVMS mientras los listeners estén activos.

## Problemas comunes

- `init`: falta una DLL, la arquitectura no coincide o el runtime no está completo.
- `login`: IP, puerto SDK, usuario, contraseña o protocolo no coinciden.
- El equipo debe permitir conexiones SDK por el puerto configurado, normalmente `8000`.
- No se deben registrar contraseñas ni colocar el runtime dentro de una carpeta pública sin protección.
