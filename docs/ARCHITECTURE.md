# Arquitectura de SACBAE

```text
Navegador
  └─ sacbae/ (portada y login)
       └─ app/ (sesión y credenciales)
            └─ modules/hikvision/web/ (panel y API de eventos)
                 └─ modules/hikvision/runtime/ (conector y eventos)
                      └─ vendor/hikvision-sdk/ (dependencia nativa)
```

El único punto de entrada es `index.php`, que redirige a `sacbae/`. El inicio de sesión crea una sesión PHP y el panel Hikvision, así como su endpoint de eventos, requieren esa sesión.

Los listeners se inician mediante `scripts/iniciar-todos-eventos.bat`. El runtime permanece junto al ejecutable porque las DLL de Hikvision se resuelven desde esa carpeta.
