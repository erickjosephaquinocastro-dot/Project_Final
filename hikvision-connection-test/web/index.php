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
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conexion Hikvision</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 540px; margin: 40px auto; padding: 0 20px; color: #17212b; }
        form { display: grid; gap: 12px; }
        label { display: grid; gap: 5px; }
        input, button { box-sizing: border-box; padding: 10px; font: inherit; }
        button { cursor: pointer; background: #166534; color: white; border: 0; }
        pre { white-space: pre-wrap; background: #f1f5f9; padding: 14px; }
        .success { color: #166534; }
        .error { color: #b91c1c; }
    </style>
</head>
<body>
    <h1>Probar conexion Hikvision</h1>
    <form method="post" autocomplete="off">
        <label>IP o host <input name="host" maxlength="128" required value="<?= htmlspecialchars((string) ($_POST['host'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
        <label>Puerto SDK <input name="port" type="number" min="1" max="65535" value="<?= htmlspecialchars((string) ($_POST['port'] ?? '8000'), ENT_QUOTES, 'UTF-8') ?>" required></label>
        <label>Usuario <input name="username" maxlength="63" required value="<?= htmlspecialchars((string) ($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
        <label>Contraseña <input name="password" type="password" required></label>
        <button type="submit">Probar conexion</button>
    </form>
    <p><a href="/Project_Final/hikvision-connection-test/web/dashboard.php">Ver eventos en tiempo real</a></p>
    <?php if (is_array($result)): ?>
        <h2 class="<?= !empty($result['ok']) ? 'success' : 'error' ?>"><?= !empty($result['ok']) ? 'Conexion exitosa' : 'No se pudo conectar' ?></h2>
        <pre><?= htmlspecialchars((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></pre>
    <?php endif; ?>
</body>
</html>
