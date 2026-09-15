<?php

declare(strict_types=1);

$result = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $config = require __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
    $host = trim((string) ($_POST['host'] ?? ''));
    $port = filter_var($_POST['port'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($host === '' || strlen($host) > 128 || $port === false || $username === '' || strlen($username) > 63 || $password === '') {
        $result = ['ok' => false, 'stage' => 'input', 'error_message' => 'Revisa los datos de conexion.'];
    } elseif (!is_file($config['connector'])) {
        $result = ['ok' => false, 'stage' => 'configuration', 'error_message' => 'No se encontro el conector compilado.'];
    } else {
        $command = '"' . str_replace('"', '\\"', $config['connector']) . '" ' . escapeshellarg($host) . ' ' . escapeshellarg((string) $port) . ' ' . escapeshellarg($username);
        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open($command, $descriptors, $pipes);
        if (is_resource($process)) {
            fwrite($pipes[0], $password . PHP_EOL);
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            $lines = array_values(array_filter(array_map('trim', explode(PHP_EOL, $stdout))));
            $result = json_decode((string) end($lines), true);
            if (!is_array($result)) {
                $result = ['ok' => false, 'stage' => 'connector', 'error_message' => 'El conector no devolvio JSON valido.'];
            }
        } else {
            $result = ['ok' => false, 'stage' => 'process', 'error_message' => 'No se pudo iniciar el conector.'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conexión Hikvision - SACBAE</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../../sacbae/assets/css/estilos.css">
    <link rel="stylesheet" href="../../../sacbae/assets/css/dashboard.css">
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
            <a href="#" class="navbar-brand d-flex align-items-center gap-2" style="text-decoration:none">
                <div class="logo-box"><i class="bi bi-fingerprint"></i></div>
                <div>
                    <span class="brand-title">SACBAE</span>
                    <small>Sistema Biométrico</small>
                </div>
            </a>
            <ul class="dash-nav-links">
                <li><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main -->
    <main class="dash-main">
        <div class="container" style="max-width: 600px;">
            <!-- Page header -->
            <div class="dash-page-header">
                <span class="dash-page-label"><span class="dot"></span> DIAGNÓSTICO</span>
                <h1>Conexión <span>Hikvision</span></h1>
                <p>Prueba la conexión con un dispositivo biométrico Hikvision.</p>
            </div>

            <!-- Alerts -->
            <?php if (is_array($result)): ?>
                <?php if (!empty($result['ok'])): ?>
                    <div class="dash-alert dash-alert-success mb-4">
                        <i class="bi bi-check-circle-fill"></i> Conexión exitosa
                    </div>
                <?php else: ?>
                    <div class="dash-alert dash-alert-error mb-4">
                        <i class="bi bi-exclamation-triangle-fill"></i> No se pudo conectar
                    </div>
                <?php endif; ?>
                <div class="dash-card mb-4">
                    <div class="dash-card-header">
                        <h2 class="dash-card-title"><span class="dash-card-icon"><i class="bi bi-code-square"></i></span> Resultado JSON</h2>
                    </div>
                    <pre class="dash-pre m-0 p-3 text-light rounded" style="white-space: pre-wrap; font-size: 0.9em; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);"><?= htmlspecialchars((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></pre>
                </div>
            <?php endif; ?>

            <!-- Content cards -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h2 class="dash-card-title"><span class="dash-card-icon"><i class="bi bi-plug"></i></span> Probar conexión</h2>
                </div>
                <div class="dash-card-body">
                    <form method="post" autocomplete="off">
                        <div class="dash-form-group mb-3">
                            <label class="form-label">IP o host</label>
                            <div class="dash-input-wrapper">
                                <i class="bi bi-hdd-network input-icon"></i>
                                <input type="text" name="host" class="dash-input form-control" maxlength="128" required value="<?= htmlspecialchars((string) ($_POST['host'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ej. 192.168.1.100">
                            </div>
                        </div>
                        
                        <div class="dash-form-group mb-3">
                            <label class="form-label">Puerto SDK</label>
                            <div class="dash-input-wrapper">
                                <i class="bi bi-diagram-3 input-icon"></i>
                                <input type="number" name="port" class="dash-input form-control" min="1" max="65535" required value="<?= htmlspecialchars((string) ($_POST['port'] ?? '8000'), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                        
                        <div class="dash-form-group mb-3">
                            <label class="form-label">Usuario</label>
                            <div class="dash-input-wrapper">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" name="username" class="dash-input form-control" maxlength="63" required value="<?= htmlspecialchars((string) ($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                        
                        <div class="dash-form-group mb-4">
                            <label class="form-label">Contraseña</label>
                            <div class="dash-input-wrapper">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" name="password" class="dash-input form-control" required>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn dash-btn-primary">
                                <i class="bi bi-plug"></i> Probar conexión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="dashboard.php" class="text-decoration-none" style="color: var(--dash-text-muted); transition: color 0.2s;" onmouseover="this.style.color='var(--dash-text)'" onmouseout="this.style.color='var(--dash-text-muted)'">
                    <i class="bi bi-arrow-right-circle"></i> Ver eventos en tiempo real
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="dash-footer mt-5">
        <div class="container">
            <div class="dash-footer-content">
                <span class="dash-footer-brand"><i class="bi bi-fingerprint"></i> SACBAE</span>
                <span>I.E.T. María Inmaculada</span>
                <span>&copy; <?php echo date("Y"); ?></span>
            </div>
        </div>
    </footer>
</body>
</html>
