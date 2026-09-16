<?php

declare(strict_types=1);

function sacbaeDatabase(): ?PDO
{
    static $connection = false;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('SACBAE_DB_HOST') ?: '127.0.0.1';
    $port = getenv('SACBAE_DB_PORT') ?: '3306';
    $database = getenv('SACBAE_DB_NAME') ?: 'sacbae';
    $username = getenv('SACBAE_DB_USER') ?: 'root';
    $password = getenv('SACBAE_DB_PASSWORD') ?: '';

    try {
        $connection = new PDO(
            "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } catch (PDOException) {
        $connection = null;
    }

    return $connection;
}

function sacbaeFindStudent(PDO $database, string $personId, string $cardNumber = ''): ?array
{
    $statement = $database->prepare(
        'SELECT id, person_id, dni, full_name, institutional_email
         FROM students
         WHERE active = 1
           AND ((:card_number <> \'\' AND card_number = :card_number) OR (:person_id <> \'\' AND person_id = :person_id))
         ORDER BY CASE WHEN :card_number_order <> \'\' AND card_number = :card_number_order THEN 0 ELSE 1 END
         LIMIT 1'
    );
    $statement->execute([
        'card_number' => $cardNumber,
        'person_id' => $personId,
        'card_number_order' => $cardNumber,
    ]);
    return $statement->fetch() ?: null;
}
