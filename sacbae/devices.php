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
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Biométricos | SACBAE</title><style>body{font-family:Arial,sans-serif;max-width:900px;margin:32px auto;padding:0 18px;color:#17212b}form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;border:1px solid #dbe3ea;padding:18px;border-radius:9px;margin:18px 0}label{display:grid;gap:5px;font-weight:700}input,select{padding:9px;border:1px solid #cbd5e1;border-radius:6px}.full{grid-column:1/-1}button{padding:10px 15px;border:0;border-radius:6px;background:#0369a1;color:#fff;font-weight:700;cursor:pointer;margin-right:7px}.error{color:#b91c1c}.success{color:#15803d}table{width:100%;border-collapse:collapse}th,td{padding:9px;border-bottom:1px solid #dbe3ea;text-align:left}pre{background:#f1f5f9;padding:12px;overflow:auto}@media(max-width:600px){form{grid-template-columns:1fr}}</style></head><body>
<h1>Dispositivos biométricos</h1><p><a href="<?= htmlspecialchars(sacbaeUrl('modules/hikvision/web/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>">Volver al panel</a></p><p>Guarda aquí la IP actual de cada equipo. La contraseña se usa solo para la prueba y no se almacena.</p>
<?php if ($error !== ''): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><?php if ($success !== ''): ?><p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post"><label>Dispositivo<select name="label"><?php foreach ($deviceFiles as $deviceLabel => $_): ?><option><?= htmlspecialchars($deviceLabel, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label><label>IP o host<input name="host" placeholder="192.168.1.40" maxlength="128" required></label><label>Puerto SDK<input name="port" type="number" min="1" max="65535" value="8000" required></label><label>Usuario del biométrico<input name="username" value="admin" maxlength="63"></label><label class="full">Contraseña del biométrico <input name="password" type="password" autocomplete="new-password"></label><div class="full"><button name="action" value="save">Guardar IP</button><button name="action" value="test">Probar conexión</button></div></form>
<?php if (is_array($testResult)): ?><h2 class="<?= !empty($testResult['ok']) ? 'success' : 'error' ?>"><?= !empty($testResult['ok']) ? 'Conexión exitosa' : 'No se pudo conectar' ?></h2><pre><?= htmlspecialchars((string) json_encode($testResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></pre><?php endif; ?>
<table><thead><tr><th>Dispositivo</th><th>IP / Host</th><th>Puerto</th></tr></thead><tbody><?php foreach ($devices as $device): ?><tr><td><?= htmlspecialchars($device['label'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($device['host'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $device['sdk_port'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table></body></html>
