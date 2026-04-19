-- ============================================================
-- VoltGrid — Full Database Schema
-- Database: voltgrid
-- Tables: stations, users, bookings, feedback
-- ============================================================

CREATE DATABASE IF NOT EXISTS voltgrid
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE voltgrid;

-- ── 1. STATIONS ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS stations (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    station_code    VARCHAR(8)      NOT NULL UNIQUE,   -- VG-01, VG-02, VG-03
    name            VARCHAR(100)    NOT NULL,
    location        VARCHAR(150)    NOT NULL,
    power_kw        SMALLINT        NOT NULL,
    connector_type  VARCHAR(30)     NOT NULL,
    total_slots     TINYINT         NOT NULL DEFAULT 2,
    status          ENUM('live','inuse','offline') NOT NULL DEFAULT 'live',
    price_per_kwh   DECIMAL(6,2)    NOT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_stations_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. USERS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    first_name      VARCHAR(60)     NOT NULL,
    last_name       VARCHAR(60)     NOT NULL,
    email           VARCHAR(180)    NOT NULL UNIQUE,
    phone           VARCHAR(20)     NOT NULL,
    city            VARCHAR(80)     NOT NULL,
    vehicle_model   VARCHAR(100)    DEFAULT NULL,
    vehicle_number  VARCHAR(20)     DEFAULT NULL,
    connector_type  VARCHAR(30)     DEFAULT NULL,
    password        VARCHAR(255)    NOT NULL,
    remember_token  VARCHAR(64)     DEFAULT NULL,
    token_expiry    DATETIME        DEFAULT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE  INDEX idx_users_email          (email),
    INDEX         idx_users_remember_token (remember_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. BOOKINGS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bookings (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED    DEFAULT NULL,          -- NULL = guest booking
    station         VARCHAR(100)    NOT NULL,
    date            DATE            NOT NULL,
    time_slot       VARCHAR(20)     NOT NULL,
    duration        VARCHAR(20)     NOT NULL,
    vehicle_number  VARCHAR(20)     DEFAULT NULL,
    total_amount    DECIMAL(8,2)    NOT NULL DEFAULT 0,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_bookings_user_id (user_id),
    INDEX idx_bookings_date    (date),
    CONSTRAINT fk_bookings_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. FEEDBACK ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS feedback (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(120)    DEFAULT NULL,
    email       VARCHAR(180)    DEFAULT NULL,
    type        VARCHAR(40)     NOT NULL DEFAULT 'Feedback',
    message     TEXT            NOT NULL,
    rating      TINYINT         DEFAULT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_feedback_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── SEED: stations ───────────────────────────────────────────
INSERT INTO stations (station_code, name, location, power_kw, connector_type, total_slots, status, price_per_kwh)
VALUES
    ('VG-01', 'Malabar Hill',  'Malabar Hill, South Mumbai',    11,  'Type 2', 2, 'offline', 10.00),
    ('VG-02', 'Vidyavihar',    'Vidyavihar East, Central Mumbai', 60,  'CCS2',   4, 'inuse',   15.00),
    ('VG-03', 'Chembur',       'Chembur, Eastern Mumbai',       150, 'CCS2',   6, 'live',    18.00)
ON DUPLICATE KEY UPDATE
    name           = VALUES(name),
    location       = VALUES(location),
    power_kw       = VALUES(power_kw),
    connector_type = VALUES(connector_type),
    total_slots    = VALUES(total_slots),
    status         = VALUES(status),
    price_per_kwh  = VALUES(price_per_kwh);
