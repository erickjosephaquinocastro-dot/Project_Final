<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'auth.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'database.php';
sacbaeRequireAdministrator();

$database = sacbaeDatabase();
$error = '';
$success = '';
$testResult = null;
$deviceFiles = ['Bio1' => 'events-1.jsonl', 'Bio3' => 'events-2.jsonl', 'Bio2' => 'events-3.jsonl'];
$runtimeDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'hikvision' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'Release';
$deviceConfigPath = $runtimeDirectory . DIRECTORY_SEPARATOR . 'devices.ini';

if ($database === null) {
    $error = 'Inicia MySQL desde XAMPP para configurar los biométricos.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $label = (string) ($_POST['label'] ?? '');
    $host = trim((string) ($_POST['host'] ?? ''));
    $port = filter_var($_POST['port'] ?? 8000, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);

    if (!isset($deviceFiles[$label]) || $host === '' || strlen($host) > 128 || $port === false) {
        $error = 'Escribe una IP o host válido y un puerto entre 1 y 65535.';
    } elseif ($action === 'save') {
        $statement = $database->prepare(
            'INSERT INTO biometric_devices (label, host, sdk_port, event_file)
             VALUES (:label, :host, :sdk_port, :event_file)
             ON DUPLICATE KEY UPDATE host = VALUES(host), sdk_port = VALUES(sdk_port), active = 1'
        );
        $statement->execute(['label' => $label, 'host' => $host, 'sdk_port' => $port, 'event_file' => $deviceFiles[$label]]);
        $success = "La dirección de {$label} fue guardada.";
    } elseif ($action === 'test') {
        $username = trim((string) ($_POST['username'] ?? 'admin'));
        $password = (string) ($_POST['password'] ?? '');
        $connector = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'hikvision' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'Release' . DIRECTORY_SEPARATOR . 'hikvision-connector.exe';
        if ($username === '' || $password === '') {
            $error = 'Escribe el usuario y la contraseña del biométrico para probar la conexión.';
        } elseif (!is_file($connector)) {
            $error = 'No se encontró hikvision-connector.exe. Compila primero el conector.';
        } else {
            $command = '"' . $connector . '" ' . escapeshellarg($host) . ' ' . escapeshellarg((string) $port) . ' ' . escapeshellarg($username);
            $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (is_resource($process)) {
                fwrite($pipes[0], $password . PHP_EOL);
                fclose($pipes[0]);
                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                $lines = array_values(array_filter(array_map('trim', explode(PHP_EOL, $output))));
                $testResult = json_decode((string) end($lines), true);
                $testResult = is_array($testResult) ? $testResult : ['ok' => false, 'error_message' => 'El conector no devolvió una respuesta válida.'];
            } else {
                $error = 'No se pudo iniciar el conector.';
            }
        }
    }
}

$devices = $database === null ? [] : $database->query('SELECT label, host, sdk_port FROM biometric_devices WHERE active = 1 ORDER BY id')->fetchAll();
if ($devices !== []) {
    $configLines = [];
    foreach (['Bio1' => 1, 'Bio3' => 2, 'Bio2' => 3] as $label => $number) {
        foreach ($devices as $device) {
            if ($device['label'] === $label) {
                $configLines[] = "HOST{$number}={$device['host']}";
                $configLines[] = "PORT{$number}={$device['sdk_port']}";
                break;
            }
        }
    }
    if ($configLines !== []) {
        file_put_contents($deviceConfigPath, implode(PHP_EOL, $configLines) . PHP_EOL, LOCK_EX);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispositivos Biométricos | SACBAE</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
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
                <li><a href="<?= htmlspecialchars(sacbaeUrl('modules/hikvision/web/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-grid-1x2"></i> Panel</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/students.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-people"></i> Estudiantes</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/devices.php'), ENT_QUOTES, 'UTF-8') ?>" class="active"><i class="bi bi-hdd-network"></i> Dispositivos</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/admins.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-shield-lock"></i> Administradores</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main -->
    <main class="dash-main">
        <div class="container">
            <!-- Page header -->
            <div class="dash-page-header">
                <span class="dash-page-label"><span class="dot"></span> CONFIGURACIÓN</span>
                <h1>Dispositivos <span>Biométricos</span></h1>
                <p>Configura la dirección IP de cada equipo Hikvision. La contraseña se usa solo para la prueba y no se almacena.</p>
            </div>

            <!-- Alerts -->
            <?php if ($error !== ''): ?>
                <div class="dash-alert dash-alert-error mb-4">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="dash-alert dash-alert-success mb-4">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <!-- Configurar dispositivo -->
                    <div class="dash-card h-100">
                        <div class="dash-card-header">
                            <h2 class="dash-card-title"><span class="dash-card-icon"><i class="bi bi-cpu"></i></span> Configurar dispositivo</h2>
                        </div>
                        <div class="dash-card-body">
                            <form method="post">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="dash-form-group">
                                            <label class="form-label">Dispositivo</label>
                                            <div class="dash-input-wrapper">
                                                <i class="bi bi-pc-display"></i>
                                                <select name="label" class="dash-input form-select" required>
                                                    <?php foreach ($deviceFiles as $deviceLabel => $_): ?>
                                                        <option value="<?= htmlspecialchars($deviceLabel, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($deviceLabel, ENT_QUOTES, 'UTF-8') ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="dash-form-group">
                                            <label class="form-label">IP o host</label>
                                            <div class="dash-input-wrapper">
                                                <i class="bi bi-hdd-network"></i>
                                                <input type="text" name="host" class="dash-input form-control" placeholder="192.168.1.40" maxlength="128" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="dash-form-group">
                                            <label class="form-label">Puerto SDK</label>
                                            <div class="dash-input-wrapper">
                                                <i class="bi bi-diagram-3"></i>
                                                <input type="number" name="port" class="dash-input form-control" min="1" max="65535" value="8000" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="dash-form-group">
                                            <label class="form-label">Usuario del biométrico</label>
                                            <div class="dash-input-wrapper">
                                                <i class="bi bi-person"></i>
                                                <input type="text" name="username" class="dash-input form-control" value="admin" maxlength="63">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="dash-form-group">
                                            <label class="form-label">Contraseña del biométrico</label>
                                            <div class="dash-input-wrapper">
                                                <i class="bi bi-lock"></i>
                                                <input type="password" name="password" class="dash-input form-control" autocomplete="new-password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4 d-flex gap-2">
                                        <button type="submit" name="action" value="save" class="btn btn-primary dash-btn-primary px-4">
                                            <i class="bi bi-save"></i> Guardar IP
                                        </button>
                                        <button type="submit" name="action" value="test" class="btn btn-secondary dash-btn-secondary px-4">
                                            <i class="bi bi-wifi"></i> Probar conexión
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <!-- Dispositivos configurados -->
                    <div class="dash-card h-100">
                        <div class="dash-card-header">
                            <h2 class="dash-card-title"><span class="dash-card-icon"><i class="bi bi-hdd-network"></i></span> Dispositivos configurados</h2>
                        </div>
                        <div class="dash-card-body p-0">
                            <div class="dash-table-wrapper border-0">
                                <table class="dash-table m-0">
                                    <thead>
                                        <tr>
                                            <th>Dispositivo</th>
                                            <th>IP / Host</th>
                                            <th>Puerto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($devices) === 0): ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">No hay dispositivos configurados.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($devices as $device): ?>
                                                <tr>
                                                    <td class="fw-medium text-white"><?= htmlspecialchars($device['label'], ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><code><?= htmlspecialchars($device['host'], ENT_QUOTES, 'UTF-8') ?></code></td>
                                                    <td><?= htmlspecialchars((string) $device['sdk_port'], ENT_QUOTES, 'UTF-8') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (is_array($testResult)): ?>
                <div class="dash-card mt-2">
                    <div class="dash-card-header">
                        <h2 class="dash-card-title <?= !empty($testResult['ok']) ? 'text-success' : 'text-danger' ?>">
                            <span class="dash-card-icon <?= !empty($testResult['ok']) ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                                <i class="bi <?= !empty($testResult['ok']) ? 'bi-check-circle' : 'bi-x-circle' ?>"></i>
                            </span> 
                            <?= !empty($testResult['ok']) ? 'Conexión exitosa' : 'No se pudo conectar' ?>
                        </h2>
                    </div>
                    <div class="dash-card-body">
                        <pre class="dash-pre m-0 p-3 bg-dark text-light rounded border border-secondary" style="overflow-x: auto;"><code><?= htmlspecialchars((string) json_encode($testResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></code></pre>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Footer -->
    <footer class="dash-footer">
        <div class="container">
            <div class="dash-footer-content">
                <span class="dash-footer-brand"><i class="bi bi-fingerprint"></i> SACBAE</span>
                <span>I.E.T. María Inmaculada</span>
                <span>&copy; <?= date("Y") ?></span>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
