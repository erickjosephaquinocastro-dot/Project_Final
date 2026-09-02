<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$runtimeDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'Release';
$eventFiles = [
    'Estudiantes 1' => $runtimeDirectory . DIRECTORY_SEPARATOR . 'events-1.jsonl',
    'Estudiantes 2' => $runtimeDirectory . DIRECTORY_SEPARATOR . 'events-2.jsonl',
    'Docentes y personal' => $runtimeDirectory . DIRECTORY_SEPARATOR . 'events-3.jsonl',
];
$events = [];
foreach ($eventFiles as $deviceName => $eventFile) {
    if (is_file($eventFile)) {
        $lines = file($eventFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_slice($lines ?: [], -50) as $line) {
            $event = json_decode($line, true);
            if (is_array($event)) {
                $event['device_name'] = $deviceName;
                $events[] = $event;
            }
        }
    }
}

usort($events, static function (array $first, array $second): int {
    $firstTime = sprintf('%04d%02d%02d%02d%02d%02d', $first['year'] ?? 0, $first['month'] ?? 0, $first['day'] ?? 0, $first['hour'] ?? 0, $first['minute'] ?? 0, $first['second'] ?? 0);
    $secondTime = sprintf('%04d%02d%02d%02d%02d%02d', $second['year'] ?? 0, $second['month'] ?? 0, $second['day'] ?? 0, $second['hour'] ?? 0, $second['minute'] ?? 0, $second['second'] ?? 0);
    return strcmp($secondTime, $firstTime);
});

echo json_encode(['ok' => true, 'events' => array_slice($events, 0, 100)], JSON_UNESCAPED_SLASHES);
