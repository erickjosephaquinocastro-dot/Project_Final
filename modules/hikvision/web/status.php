<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!sacbaeIsAuthenticated()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$runtimeDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'Release';
$connectorAvailable = is_file($runtimeDirectory . DIRECTORY_SEPARATOR . 'hikvision-connector.exe');
$devices = [
    ['name' => 'Bio1', 'host' => '192.168.1.40', 'event_file' => 'events-1.jsonl'],
    ['name' => 'Bio3', 'host' => '192.168.1.91', 'event_file' => 'events-2.jsonl'],
    ['name' => 'Bio2', 'host' => '192.168.1.46', 'event_file' => 'events-3.jsonl'],
];

$listenerCount = null;
if ($connectorAvailable && function_exists('shell_exec')) {
    $result = shell_exec('tasklist /FI "IMAGENAME eq hikvision-connector.exe" /FO CSV /NH 2>NUL');
    if (is_string($result)) {
        $listenerCount = substr_count(strtolower($result), 'hikvision-connector.exe');
    }
}

foreach ($devices as &$device) {
    $eventPath = $runtimeDirectory . DIRECTORY_SEPARATOR . $device['event_file'];
    $device['last_event_at'] = is_file($eventPath) && filesize($eventPath) > 0 ? gmdate('c', filemtime($eventPath)) : null;
    $device['status'] = !$connectorAvailable
        ? 'connector_missing'
        : (($listenerCount ?? 0) >= count($devices) ? 'listening' : 'waiting_listener');
    unset($device['event_file']);
}
unset($device);

echo json_encode([
    'ok' => true,
    'connector_available' => $connectorAvailable,
    'listener_count' => $listenerCount,
    'devices' => $devices,
], JSON_UNESCAPED_SLASHES);
