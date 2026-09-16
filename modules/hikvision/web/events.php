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
$databaseError = $database === null ? 'No se pudo conectar con MySQL.' : null;
if ($database !== null) {
    $configuredDevices = $database->query(
        'SELECT label, host, event_file FROM biometric_devices WHERE active = 1 ORDER BY id'
    )->fetchAll();
    if ($configuredDevices !== []) {
        $eventFiles = [];
        foreach ($configuredDevices as $device) {
            $eventFiles[$device['label'] . ' (' . $device['host'] . ')'] = $runtimeDirectory . DIRECTORY_SEPARATOR . $device['event_file'];
        }
    }
}
$studentCache = [];
$insertEvent = $database === null ? null : $database->prepare(
    'INSERT IGNORE INTO attendance_events
        (source_key, student_id, person_id, device_name, card_number, occurred_at, door_number, reader_number, verification_number, event_type, raw_event)
      VALUES
          (:source_key, :student_id, :person_id, :device_name, :card_number, :occurred_at, :door_number, :reader_number, :verification_number, :event_type, :raw_event)
      ON DUPLICATE KEY UPDATE student_id = COALESCE(VALUES(student_id), student_id)'
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

if ($database !== null) {
    $database->exec(
        'UPDATE attendance_events e
         INNER JOIN students s ON s.person_id = e.person_id AND s.active = 1
         SET e.student_id = s.id
         WHERE e.student_id IS NULL'
    );
    $storedEvents = $database->query(
        'SELECT e.person_id, e.device_name, e.card_number, e.occurred_at, e.door_number,
                e.reader_number, e.verification_number, e.event_type, e.raw_event,
                s.full_name, s.institutional_email
         FROM attendance_events e
         LEFT JOIN students s ON s.id = e.student_id
         ORDER BY e.occurred_at DESC, e.id DESC
         LIMIT 100'
    )->fetchAll();
    $events = [];
    foreach ($storedEvents as $storedEvent) {
        $event = json_decode((string) $storedEvent['raw_event'], true);
        $event = is_array($event) ? $event : [];
        $occurredAt = (string) ($storedEvent['occurred_at'] ?? '');
        if ($occurredAt !== '' && preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $occurredAt, $dateParts)) {
            $event['year'] = (int) $dateParts[1];
            $event['month'] = (int) $dateParts[2];
            $event['day'] = (int) $dateParts[3];
            $event['hour'] = (int) $dateParts[4];
            $event['minute'] = (int) $dateParts[5];
            $event['second'] = (int) $dateParts[6];
        }
        $event['device_name'] = $storedEvent['device_name'];
        $event['employee_number'] = $storedEvent['person_id'] ?? $event['employee_number'] ?? null;
        $event['card_number'] = $storedEvent['card_number'] ?? $event['card_number'] ?? '';
        $event['door'] = $storedEvent['door_number'] ?? $event['door'] ?? null;
        $event['reader'] = $storedEvent['reader_number'] ?? $event['reader'] ?? null;
        $event['verify'] = $storedEvent['verification_number'] ?? $event['verify'] ?? null;
        $event['event_type'] = $storedEvent['event_type'] ?? $event['event_type'] ?? null;
        if ($storedEvent['full_name'] !== null) {
            $event['employee_name'] = $storedEvent['full_name'];
            $event['institutional_email'] = $storedEvent['institutional_email'];
        }
        $events[] = $event;
    }
}

usort($events, static function (array $first, array $second): int {
    $firstTime = sprintf('%04d%02d%02d%02d%02d%02d', $first['year'] ?? 0, $first['month'] ?? 0, $first['day'] ?? 0, $first['hour'] ?? 0, $first['minute'] ?? 0, $first['second'] ?? 0);
    $secondTime = sprintf('%04d%02d%02d%02d%02d%02d', $second['year'] ?? 0, $second['month'] ?? 0, $second['day'] ?? 0, $second['hour'] ?? 0, $second['minute'] ?? 0, $second['second'] ?? 0);
    return strcmp($secondTime, $firstTime);
});

echo json_encode([
    'ok' => true,
    'database_connected' => $database !== null,
    'database_error' => $databaseError,
    'events' => array_slice($events, 0, 100),
], JSON_UNESCAPED_SLASHES);
