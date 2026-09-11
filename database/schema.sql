CREATE DATABASE IF NOT EXISTS sacbae
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sacbae;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(64) NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin') NOT NULL DEFAULT 'admin',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

INSERT IGNORE INTO users (username, full_name, email, password_hash, role)
VALUES ('admin', 'Administrador SACBAE', 'admin@institucion.local', '$2y$10$vOnzgUM89M5ZjG5/4IFzYe.fX9mHH8DAFoUTNkM8h/d5alGwnK8Dy', 'admin');

CREATE TABLE IF NOT EXISTS biometric_devices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    label VARCHAR(64) NOT NULL,
    host VARCHAR(128) NOT NULL,
    sdk_port SMALLINT UNSIGNED NOT NULL DEFAULT 8000,
    event_file VARCHAR(64) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_biometric_devices_label (label),
    UNIQUE KEY uq_biometric_devices_event_file (event_file)
) ENGINE=InnoDB;

INSERT IGNORE INTO biometric_devices (label, host, sdk_port, event_file) VALUES
    ('Bio1', '192.168.1.40', 8000, 'events-1.jsonl'),
    ('Bio3', '192.168.1.91', 8000, 'events-2.jsonl'),
    ('Bio2', '192.168.1.46', 8000, 'events-3.jsonl');

CREATE TABLE IF NOT EXISTS students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    person_id VARCHAR(64) NOT NULL,
    dni VARCHAR(32) NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    institutional_email VARCHAR(190) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_students_person_id (person_id),
    UNIQUE KEY uq_students_dni (dni),
    UNIQUE KEY uq_students_institutional_email (institutional_email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    source_key CHAR(64) NOT NULL,
    student_id BIGINT UNSIGNED NULL,
    person_id VARCHAR(64) NULL,
    device_name VARCHAR(100) NOT NULL,
    card_number VARCHAR(64) NULL,
    occurred_at DATETIME NULL,
    door_number INT NULL,
    reader_number INT NULL,
    verification_number INT NULL,
    event_type INT NULL,
    raw_event LONGTEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_attendance_events_source_key (source_key),
    KEY idx_attendance_events_student_id (student_id),
    KEY idx_attendance_events_occurred_at (occurred_at),
    CONSTRAINT fk_attendance_events_student
        FOREIGN KEY (student_id) REFERENCES students (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;
