-- Add address + postcode columns to customers.
-- Run once on production databases that were created from an older schema.sql.
-- Safe to re-run: ADD COLUMN IF NOT EXISTS is idempotent on MariaDB / MySQL 8.0+.
--
-- phpMyAdmin → select database → Import → upload this file.

ALTER TABLE customers
  ADD COLUMN IF NOT EXISTS address VARCHAR(500) NULL AFTER city,
  ADD COLUMN IF NOT EXISTS postcode VARCHAR(20) NULL AFTER address;
