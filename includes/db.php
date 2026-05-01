<?php
function srm_install_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_students = $wpdb->prefix . 'srm_students';
    $wpdb->query("
        CREATE TABLE IF NOT EXISTS $table_students (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            roll VARCHAR(50),
            reg_no VARCHAR(50),
            class VARCHAR(10),
            section VARCHAR(10),
            `group` VARCHAR(20),
            session VARCHAR(20),
            photo_url VARCHAR(255),
            father_name VARCHAR(255),
            mother_name VARCHAR(255),
            address TEXT,
            birth_reg_no VARCHAR(50);
            PRIMARY KEY (id)
        ) $charset_collate;
    ");

    $table_subjects = $wpdb->prefix . 'srm_subjects';
    $wpdb->query("
        CREATE TABLE IF NOT EXISTS $table_subjects (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            class VARCHAR(10),
            `group` VARCHAR(20),
            full_marks INT,
            pass_marks INT,
            PRIMARY KEY (id)
        ) $charset_collate;
    ");

    $table_exams = $wpdb->prefix . 'srm_exams';
    $wpdb->query("
        CREATE TABLE IF NOT EXISTS $table_exams (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            class VARCHAR(10),
            year VARCHAR(10),
            PRIMARY KEY (id)
        ) $charset_collate;
    ");

    $table_marks = $wpdb->prefix . 'srm_marks';
    $wpdb->query("
        CREATE TABLE IF NOT EXISTS $table_marks (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            student_id BIGINT UNSIGNED,
            subject_id BIGINT UNSIGNED,
            exam_id BIGINT UNSIGNED,
            marks INT,
            PRIMARY KEY (id)
        ) $charset_collate;
    ");
}
