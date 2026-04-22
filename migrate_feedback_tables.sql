-- ============================================================
-- VoltGrid — Feedback Tables Migration
-- Run this ONCE on existing installations that already have
-- the old single `feedback` table (with a `type` column).
-- Safe to skip on fresh installs (voltgrid.sql already
-- creates the three new tables directly).
-- ============================================================

USE voltgrid;

-- ── 1. Create the three new tables if they don't exist ───────

CREATE TABLE IF NOT EXISTS report_issues (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name                VARCHAR(120)    DEFAULT NULL,
    email               VARCHAR(180)    DEFAULT NULL,
    booking_reference   VARCHAR(40)     DEFAULT NULL,
    message             TEXT            NOT NULL,
    attachment_path     VARCHAR(300)    DEFAULT NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_report_issues_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contact_us (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(120)    DEFAULT NULL,
    email       VARCHAR(180)    DEFAULT NULL,
    phone       VARCHAR(30)     DEFAULT NULL,
    message     TEXT            NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_contact_us_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rename old `feedback` to preserve its data, then recreate clean.
-- Skip this block if you prefer to keep the old table as-is.
ALTER TABLE feedback RENAME TO feedback_legacy;

CREATE TABLE IF NOT EXISTS feedback (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(120)    DEFAULT NULL,
    email       VARCHAR(180)    DEFAULT NULL,
    message     TEXT            NOT NULL,
    rating      TINYINT         DEFAULT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. Migrate existing rows into the correct new tables ──────

-- Feedback rows
INSERT INTO feedback (name, email, message, rating, created_at)
SELECT name, email, message, rating, created_at
FROM   feedback_legacy
WHERE  LOWER(type) IN ('feedback', '');

-- Report Issue rows
INSERT INTO report_issues (name, email, message, created_at)
SELECT name, email, message, created_at
FROM   feedback_legacy
WHERE  LOWER(type) IN ('issue', 'report issue');

-- Contact Us rows
INSERT INTO contact_us (name, email, message, created_at)
SELECT name, email, message, created_at
FROM   feedback_legacy
WHERE  LOWER(type) IN ('contact', 'contact us');

-- ── 3. (Optional) Drop the legacy table after verifying data ──
-- Uncomment the line below only after confirming the migration.
-- DROP TABLE feedback_legacy;
