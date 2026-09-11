# SACBAE

Sistema de Control de Asistencia Biométrica de la I.E.T. María Inmaculada.

## Estructura

- `sacbae/`: interfaz web pública, portada, inicio de sesión y cierre de sesión.
- `app/`: configuración y autenticación compartida.
- `modules/hikvision/`: conector C++, panel de eventos y runtime de Hikvision.
- `vendor/hikvision-sdk/`: SDK original de Hikvision, aislado de la aplicación.
- `scripts/`: accesos para iniciar los listeners de los biométricos.
- `docs/`: documentación general del proyecto.

## Inicio local

Abre `http://localhost:8080/Project_Final/`. La cuenta inicial local es `admin` con contraseña `Cambiar123!`; cámbiala antes de publicar modificando el hash de `app/credentials.php` o definiendo las variables de entorno `SACBAE_ADMIN_USERNAME` y `SACBAE_ADMIN_PASSWORD_HASH`.

Para recibir eventos, ejecuta `scripts/iniciar-todos-eventos.bat` y después inicia sesión para abrir el panel.
