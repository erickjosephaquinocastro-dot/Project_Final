<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'auth.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'database.php';

header('Content-Type: application/json; charset=utf-8');

if (!sacbaeIsAuthenticated()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$runtimeDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'Release';
$eventFiles = [
    'Bio1 (192.168.1.40)' => $runtimeDirectory . DIRECTORY_SEPARATOR . 'events-1.jsonl',
    'Bio3 (192.168.1.91)' => $runtimeDirectory . DIRECTORY_SEPARATOR . 'events-2.jsonl',
    'Bio2 (192.168.1.46)' => $runtimeDirectory . DIRECTORY_SEPARATOR . 'events-3.jsonl',
];
$events = [];
$database = sacbaeDatabase();
$studentCache = [];
$insertEvent = $database === null ? null : $database->prepare(
    'INSERT IGNORE INTO attendance_events
        (source_key, student_id, person_id, device_name, card_number, occurred_at, door_number, reader_number, verification_number, event_type, raw_event)
     VALUES
        (:source_key, :student_id, :person_id, :device_name, :card_number, :occurred_at, :door_number, :reader_number, :verification_number, :event_type, :raw_event)'
);
foreach ($eventFiles as $deviceName => $eventFile) {
    if (is_file($eventFile)) {
        $lines = file($eventFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_slice($lines ?: [], -50) as $line) {
            $event = json_decode($line, true);
            if (is_array($event)) {
                $event['device_name'] = $deviceName;
                $personId = trim((string) ($event['employee_number'] ?? ''));
                $student = null;
                if ($database !== null && $personId !== '') {
                    if (!array_key_exists($personId, $studentCache)) {
                        $studentCache[$personId] = sacbaeFindStudent($database, $personId);
                    }
                    $student = $studentCache[$personId];
                }

                if (is_array($student)) {
                    $event['student_name'] = $student['full_name'];
                    $event['institutional_email'] = $student['institutional_email'];
                    $event['employee_name'] = $student['full_name'];
                }

                if ($insertEvent !== null) {
                    $occurredAt = sprintf(
                        '%04d-%02d-%02d %02d:%02d:%02d',
                        $event['year'] ?? 0, $event['month'] ?? 0, $event['day'] ?? 0,
                        $event['hour'] ?? 0, $event['minute'] ?? 0, $event['second'] ?? 0
                    );
                    $insertEvent->execute([
                        'source_key' => hash('sha256', $deviceName . '|' . $line),
                        'student_id' => $student['id'] ?? null,
                        'person_id' => $personId !== '' ? $personId : null,
                        'device_name' => $deviceName,
                        'card_number' => $event['card_number'] ?? null,
                        'occurred_at' => str_starts_with($occurredAt, '0000-') ? null : $occurredAt,
                        'door_number' => $event['door'] ?? null,
                        'reader_number' => $event['reader'] ?? null,
                        'verification_number' => $event['verify'] ?? null,
                        'event_type' => $event['event_type'] ?? null,
                        'raw_event' => $line,
                    ]);
                }
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
