-- Add password-reset token columns to customers.
-- Run once on production databases that were created from an older schema.sql.
-- Safe to re-run: uses IF NOT EXISTS via the SHOW COLUMNS guard.
--
-- phpMyAdmin → select database → Import → upload this file.

ALTER TABLE customers
  ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) NULL AFTER newsletter_subscribed,
  ADD COLUMN IF NOT EXISTS reset_token_expires_at TIMESTAMP NULL AFTER reset_token;
