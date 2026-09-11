<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'auth.php';
sacbaeRequireAuthentication();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel biométrico | SACBAE</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1100px; margin: 32px auto; padding: 0 18px; color: #17212b; background: #f8fafc; }
        h1 { margin-bottom: 6px; }.muted { color: #64748b; }
        .nav { margin: 16px 0 22px; }.nav a { color: #0369a1; text-decoration: none; }
        .device-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; margin: 18px 0 26px; }
        .device-card, .table-card { border: 1px solid #dbe3ea; border-radius: 10px; padding: 16px; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
        .device-card h2 { font-size: 1rem; margin: 0 0 7px; }.device-card p { margin: 7px 0; font-size: .9rem; color: #475569; }
        .status { display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: .82rem; }.status::before { content: ''; width: 9px; height: 9px; border-radius: 50%; background: #94a3b8; }
        .status.listening { color: #15803d; }.status.listening::before { background: #22c55e; }.status.waiting_listener { color: #b45309; }.status.waiting_listener::before { background: #f59e0b; }.status.connector_missing { color: #b91c1c; }.status.connector_missing::before { background: #ef4444; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: .9rem; } th, td { border-bottom: 1px solid #dbe3ea; padding: 10px 8px; text-align: left; } th { background: #f1f5f9; } .empty { color: #64748b; padding: 20px 0; }
        @media (max-width: 700px) { .table-card { overflow-x: auto; } table { min-width: 800px; } }
    </style>
</head>
<body>
    <h1>Panel de biométricos</h1>
    <p class="muted">El estado se actualiza cada 5 segundos. Para conectar los equipos, ejecuta <code>scripts/iniciar-todos-eventos.bat</code> y deja abiertas sus ventanas.</p>
    <p class="nav"><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/'), ENT_QUOTES, 'UTF-8') ?>">Inicio</a> · <a href="<?= htmlspecialchars(sacbaeUrl('sacbae/students.php'), ENT_QUOTES, 'UTF-8') ?>">Estudiantes</a> · <a href="<?= htmlspecialchars(sacbaeUrl('sacbae/devices.php'), ENT_QUOTES, 'UTF-8') ?>">Dispositivos</a> · <a href="<?= htmlspecialchars(sacbaeUrl('sacbae/admins.php'), ENT_QUOTES, 'UTF-8') ?>">Administradores</a> · <a href="<?= htmlspecialchars(sacbaeUrl('sacbae/logout.php'), ENT_QUOTES, 'UTF-8') ?>">Cerrar sesión</a></p>

    <section class="device-grid" id="devices" aria-live="polite"><p class="empty">Comprobando dispositivos...</p></section>

    <section class="table-card">
        <h2>Eventos de acceso</h2>
        <table>
            <thead><tr><th>Fecha</th><th>Dispositivo</th><th>Tarjeta</th><th>Nombre</th><th>Correo institucional</th><th>Person ID</th><th>Puerta</th><th>Lector</th><th>Verificación</th><th>Tipo</th></tr></thead>
            <tbody id="events"><tr><td colspan="10" class="empty">Esperando eventos...</td></tr></tbody>
        </table>
    </section>

    <script>
        const eventsBody = document.getElementById('events');
        const devicesContainer = document.getElementById('devices');
        const text = value => value === undefined || value === null || value === '' ? '-' : String(value);
        const statusLabel = { listening: 'Listener conectado', waiting_listener: 'Listener no iniciado', connector_missing: 'Conector sin compilar' };

        async function loadDeviceStatus() {
            try {
                const response = await fetch('status.php', { cache: 'no-store' });
                const payload = await response.json();
                if (!payload.ok) throw new Error('No autorizado');
                devicesContainer.innerHTML = payload.devices.map(device => {
                    const eventTime = device.last_event_at ? new Date(device.last_event_at).toLocaleString('es-CO') : 'Sin eventos registrados';
                    return `<article class="device-card"><h2>${text(device.name)}</h2><p>${text(device.host)}:${text(device.port)}</p><span class="status ${device.status}">${statusLabel[device.status]}</span><p>Última actividad: ${eventTime}</p></article>`;
                }).join('');
            } catch (error) {
                devicesContainer.innerHTML = '<p class="empty">No se pudo comprobar el estado de los dispositivos.</p>';
            }
        }

        async function loadEvents() {
            try {
                const response = await fetch('events.php', { cache: 'no-store' });
                const payload = await response.json();
                eventsBody.innerHTML = '';
                if (!payload.events || payload.events.length === 0) {
                    eventsBody.innerHTML = '<tr><td colspan="10" class="empty">Esperando eventos...</td></tr>';
                    return;
                }
                payload.events.forEach(event => {
                    const row = document.createElement('tr');
                    const date = `${event.year}-${String(event.month).padStart(2, '0')}-${String(event.day).padStart(2, '0')} ${String(event.hour).padStart(2, '0')}:${String(event.minute).padStart(2, '0')}:${String(event.second).padStart(2, '0')}`;
                    const employeeName = event.employee_name && event.employee_name.trim() ? event.employee_name : 'Sin nombre';
                    [date, event.device_name, event.card_number, employeeName, event.institutional_email, event.employee_number, event.door, event.reader, event.verify, event.event_type].forEach(value => {
                        const cell = document.createElement('td'); cell.textContent = text(value); row.appendChild(cell);
                    });
                    eventsBody.appendChild(row);
                });
            } catch (error) {
                eventsBody.innerHTML = '<tr><td colspan="10" class="empty">No se pudo leer el archivo de eventos.</td></tr>';
            }
        }

        loadEvents(); loadDeviceStatus();
        setInterval(loadEvents, 2000); setInterval(loadDeviceStatus, 5000);
    </script>
</body>
</html>
