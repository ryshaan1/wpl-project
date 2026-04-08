-- ──────────────────────────────────────────────────────────────────────────
-- VoltGrid — session & cookie migration
-- Run this once against your `voltgrid` database.
-- ──────────────────────────────────────────────────────────────────────────

-- 1. Add remember-me token columns to users table
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS remember_token VARCHAR(64)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS token_expiry   DATETIME     DEFAULT NULL;

-- Optional index to speed up token look-ups on login
CREATE INDEX IF NOT EXISTS idx_users_remember_token ON users (remember_token);

-- 2. Add user_id FK to bookings so each booking links to an account
--    (nullable — guests can still book without signing in)
ALTER TABLE bookings
    ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS created_at DATETIME DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE bookings
    ADD CONSTRAINT fk_bookings_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL;
