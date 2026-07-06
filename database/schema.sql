-- ---------------------------------------------------------------------------
-- SAMS — Student Attendance Management System
-- Database schema (plain PHP / MySQL · MariaDB). Replaces Laravel migrations.
-- ---------------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(255) NOT NULL,
    `email`          VARCHAR(255) NOT NULL,
    `password`       VARCHAR(255) NOT NULL,
    `role`           ENUM('admin', 'officer', 'supervisor') NOT NULL DEFAULT 'officer',
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `remember_token` VARCHAR(100) NULL,
    `created_at`     TIMESTAMP NULL,
    `updated_at`     TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `departments` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `departments_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `programs` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(255) NOT NULL,
    `department_id` BIGINT UNSIGNED NOT NULL,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `programs_name_unique` (`name`),
    KEY `programs_department_id_index` (`department_id`),
    CONSTRAINT `programs_department_id_foreign` FOREIGN KEY (`department_id`)
        REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `students` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id`    VARCHAR(50) NOT NULL,
    `first_name`    VARCHAR(100) NOT NULL,
    `last_name`     VARCHAR(100) NOT NULL,
    `photo`         VARCHAR(500) NULL,
    `program_id`    BIGINT UNSIGNED NULL,
    `level`         INT NOT NULL,
    `department_id` BIGINT UNSIGNED NULL,
    `faculty`       VARCHAR(200) NULL,
    `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `students_student_id_unique` (`student_id`),
    KEY `students_last_name_index` (`last_name`),
    KEY `students_is_active_index` (`is_active`),
    KEY `students_program_id_index` (`program_id`),
    KEY `students_department_id_index` (`department_id`),
    CONSTRAINT `students_program_id_foreign` FOREIGN KEY (`program_id`)
        REFERENCES `programs` (`id`) ON DELETE SET NULL,
    CONSTRAINT `students_department_id_foreign` FOREIGN KEY (`department_id`)
        REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `semesters` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(255) NOT NULL,
    `start_date`  DATE NOT NULL,
    `end_date`    DATE NOT NULL,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 0,
    `description` TEXT NULL,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `semesters_is_active_index` (`is_active`),
    KEY `semesters_start_date_index` (`start_date`),
    KEY `semesters_end_date_index` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `events` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(255) NOT NULL,
    `type`        ENUM('church_service', 'special_program', 'week_of_emphasis', 'idf') NOT NULL,
    `start_time`  DATETIME NOT NULL,
    `end_time`    DATETIME NOT NULL,
    `description` TEXT NULL,
    `created_by`  BIGINT UNSIGNED NOT NULL,
    `semester_id` BIGINT UNSIGNED NULL,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `events_type_index` (`type`),
    KEY `events_start_time_index` (`start_time`),
    KEY `events_created_by_index` (`created_by`),
    KEY `events_semester_id_index` (`semester_id`),
    CONSTRAINT `events_created_by_foreign` FOREIGN KEY (`created_by`)
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `events_semester_id_foreign` FOREIGN KEY (`semester_id`)
        REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_officers` (
    `id`       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` BIGINT UNSIGNED NOT NULL,
    `user_id`  BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `event_officers_event_id_user_id_unique` (`event_id`, `user_id`),
    KEY `event_officers_user_id_index` (`user_id`),
    CONSTRAINT `event_officers_event_id_foreign` FOREIGN KEY (`event_id`)
        REFERENCES `events` (`id`) ON DELETE CASCADE,
    CONSTRAINT `event_officers_user_id_foreign` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attendance` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id`    BIGINT UNSIGNED NOT NULL,
    `student_id`  BIGINT UNSIGNED NOT NULL,
    `marked_by`   BIGINT UNSIGNED NOT NULL,
    `method`      ENUM('scan', 'manual', 'ocr_scan') NOT NULL DEFAULT 'scan',
    `is_verified` TINYINT(1) NOT NULL DEFAULT 1,
    `timestamp`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attendance_event_id_student_id_unique` (`event_id`, `student_id`),
    KEY `attendance_student_id_index` (`student_id`),
    KEY `attendance_marked_by_index` (`marked_by`),
    KEY `attendance_timestamp_index` (`timestamp`),
    CONSTRAINT `attendance_event_id_foreign` FOREIGN KEY (`event_id`)
        REFERENCES `events` (`id`) ON DELETE CASCADE,
    CONSTRAINT `attendance_student_id_foreign` FOREIGN KEY (`student_id`)
        REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `attendance_marked_by_foreign` FOREIGN KEY (`marked_by`)
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `action`       VARCHAR(255) NOT NULL,
    `performed_by` BIGINT UNSIGNED NOT NULL,
    `target_type`  VARCHAR(100) NOT NULL,
    `target_id`    BIGINT UNSIGNED NULL,
    `metadata`     JSON NULL,
    `timestamp`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `audit_logs_performed_by_index` (`performed_by`),
    KEY `audit_logs_target_type_index` (`target_type`),
    KEY `audit_logs_timestamp_index` (`timestamp`),
    CONSTRAINT `audit_logs_performed_by_foreign` FOREIGN KEY (`performed_by`)
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `system_settings` (
    `key`        VARCHAR(100) NOT NULL,
    `value`      TEXT NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Binary assets stored in the database (e.g. the branding logo) so they do not
-- depend on the filesystem being writable or served on the host.
CREATE TABLE IF NOT EXISTS `app_files` (
    `name`       VARCHAR(100) NOT NULL,
    `mime`       VARCHAR(100) NOT NULL,
    `data`       LONGBLOB NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
