<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Eventos Hikvision</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 32px auto; padding: 0 18px; color: #17212b; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border-bottom: 1px solid #dbe3ea; padding: 10px 8px; text-align: left; }
        th { background: #f1f5f9; }
        .empty { color: #64748b; padding: 20px 0; }
    </style>
</head>
<body>
    <h1>Eventos de acceso</h1>
    <p>Actualizacion automatica cada 2 segundos. Antes de marcar, ejecuta <code>runtime/Release/iniciar-eventos.bat</code> y deja abierta esa ventana.</p>
    <table>
        <thead><tr><th>Fecha</th><th>Dispositivo</th><th>Tarjeta</th><th>Nombre</th><th>Empleado</th><th>Puerta</th><th>Lector</th><th>Verificacion</th><th>Tipo</th></tr></thead>
        <tbody id="events"><tr><td colspan="9" class="empty">Esperando eventos...</td></tr></tbody>
    </table>
    <script>
        const eventsBody = document.getElementById('events');
        const text = value => value === undefined || value === null || value === '' ? '-' : String(value);
        async function loadEvents() {
            try {
                const response = await fetch('events.php', { cache: 'no-store' });
                const payload = await response.json();
                eventsBody.innerHTML = '';
                if (!payload.events || payload.events.length === 0) {
                    eventsBody.innerHTML = '<tr><td colspan="9" class="empty">Esperando eventos...</td></tr>';
                    return;
                }
                payload.events.forEach(event => {
                    const row = document.createElement('tr');
                    const date = `${event.year}-${String(event.month).padStart(2, '0')}-${String(event.day).padStart(2, '0')} ${String(event.hour).padStart(2, '0')}:${String(event.minute).padStart(2, '0')}:${String(event.second).padStart(2, '0')}`;
                    const employeeName = event.employee_name && event.employee_name.trim() ? event.employee_name : 'Sin nombre';
                    [date, event.device_name, event.card_number, employeeName, event.employee_number, event.door, event.reader, event.verify, event.event_type].forEach(value => {
                        const cell = document.createElement('td');
                        cell.textContent = text(value);
                        row.appendChild(cell);
                    });
                    eventsBody.appendChild(row);
                });
            } catch (error) {
                eventsBody.innerHTML = '<tr><td colspan="9" class="empty">No se pudo leer el archivo de eventos.</td></tr>';
            }
        }
        loadEvents();
        setInterval(loadEvents, 2000);
    </script>
</body>
</html>
