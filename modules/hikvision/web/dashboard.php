<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'auth.php';
sacbaeRequireAuthentication();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel biométrico | SACBAE</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= htmlspecialchars(sacbaeUrl('sacbae/assets/css/estilos.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(sacbaeUrl('sacbae/assets/css/dashboard.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dash-page">
    <!-- Background -->
    <div class="dash-bg">
        <div class="dash-grid"></div>
        <div class="dash-glow dash-glow-1"></div>
        <div class="dash-glow dash-glow-2"></div>
    </div>

    <!-- Navbar -->
    <nav class="dash-navbar">
        <div class="container">
            <a href="<?= htmlspecialchars(sacbaeUrl('sacbae/'), ENT_QUOTES, 'UTF-8') ?>" class="navbar-brand d-flex align-items-center gap-2" style="text-decoration:none">
                <div class="logo-box"><i class="bi bi-fingerprint"></i></div>
                <div>
                    <span class="brand-title">SACBAE</span>
                    <small>Sistema Biométrico</small>
                </div>
            </a>
            <ul class="dash-nav-links">
                <li><a href="<?= htmlspecialchars(sacbaeUrl('modules/hikvision/web/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>" class="active"><i class="bi bi-grid-1x2"></i> Panel</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/students.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-people"></i> Estudiantes</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/devices.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-router"></i> Dispositivos</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/admins.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-shield-lock"></i> Administradores</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/logout.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main -->
    <main class="dash-main">
        <div class="container">
            <!-- Page header -->
            <div class="dash-page-header">
                <span class="dash-page-label"><span class="dot"></span> MONITOREO EN TIEMPO REAL</span>
                <h1>Panel <span>Biométrico</span></h1>
                <p>El estado se actualiza cada 5 segundos. Para conectar los equipos, ejecuta <code class="dash-code">scripts/iniciar-todos-eventos.bat</code>.</p>
            </div>

            <!-- Devices grid -->
            <div id="devices" class="dash-device-grid" aria-live="polite">
                <p class="empty text-muted">Comprobando dispositivos...</p>
            </div>

            <!-- Content cards -->
            <div class="dash-card mt-4">
                <div class="dash-card-header">
                    <h2 class="dash-card-title"><span class="dash-card-icon"><i class="bi bi-activity"></i></span> Eventos de acceso</h2>
                    <span id="events-database-status" class="text-muted small">Comprobando base de datos...</span>
                </div>
                
                <div class="dash-table-wrapper">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Dispositivo</th>
                                <th>Tarjeta</th>
                                <th>DNI</th>
                                <th>Nombre</th>
                                <th>Correo institucional</th>
                                <th>Person ID</th>
                                <th>Puerta</th>
                                <th>Lector</th>
                                <th>Verificación</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody id="events">
                            <tr><td colspan="10" class="empty text-center text-muted py-4">Esperando eventos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="dash-footer">
        <div class="container">
            <div class="dash-footer-content">
                <span class="dash-footer-brand"><i class="bi bi-fingerprint"></i> SACBAE</span>
                <span>I.E.T. María Inmaculada</span>
                <span>&copy; <?php echo date("Y"); ?></span>
            </div>
        </div>
    </footer>

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
                    return `<article class="dash-device-card">
                        <h3>${text(device.name)}</h3>
                        <p class="device-ip">${text(device.host)}:${text(device.port)}</p>
                        <span class="dash-status ${device.status}">${statusLabel[device.status]}</span>
                        <p class="device-last">Última actividad: ${eventTime}</p>
                    </article>`;
                }).join('');
            } catch (error) {
                devicesContainer.innerHTML = '<p class="empty text-muted">No se pudo comprobar el estado de los dispositivos.</p>';
            }
        }

        async function loadEvents() {
            try {
                const response = await fetch('events.php', { cache: 'no-store' });
                const payload = await response.json();
                const databaseStatus = document.getElementById('events-database-status');
                if (databaseStatus) {
                    databaseStatus.textContent = payload.database_connected
                        ? 'Base de datos conectada'
                        : (payload.database_error || 'Base de datos desconectada');
                    databaseStatus.className = payload.database_connected ? 'text-success small' : 'text-danger small';
                }
                eventsBody.innerHTML = '';
                if (!payload.events || payload.events.length === 0) {
                    eventsBody.innerHTML = '<tr><td colspan="11" class="empty text-center text-muted py-4">Esperando eventos...</td></tr>';
                    return;
                }
                payload.events.forEach(event => {
                    const row = document.createElement('tr');
                    const date = `${event.year}-${String(event.month).padStart(2, '0')}-${String(event.day).padStart(2, '0')} ${String(event.hour).padStart(2, '0')}:${String(event.minute).padStart(2, '0')}:${String(event.second).padStart(2, '0')}`;
                    const employeeName = event.employee_name && event.employee_name.trim() ? event.employee_name : 'Sin nombre';
                    [date, event.device_name, event.card_number, event.dni, employeeName, event.institutional_email, event.employee_number, event.door, event.reader, event.verify, event.event_type].forEach(value => {
                        const cell = document.createElement('td'); cell.textContent = text(value); row.appendChild(cell);
                    });
                    eventsBody.appendChild(row);
                });
            } catch (error) {
                eventsBody.innerHTML = '<tr><td colspan="11" class="empty text-center text-danger py-4">No se pudo leer el archivo de eventos.</td></tr>';
            }
        }

        loadEvents(); loadDeviceStatus();
        setInterval(loadEvents, 2000); setInterval(loadDeviceStatus, 5000);
    </script>
</body>
</html>
