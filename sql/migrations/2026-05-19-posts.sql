-- 2026-05-19 · Create the `posts` table
--
-- blog.php and blog-post.php have been querying a `posts` table that
-- was never in schema.sql, so the live blog page 500s. This migration
-- creates the table the front-end already expects. Run it once in
-- phpMyAdmin (or any MySQL client) to enable the blog.
--
-- The blog will render an empty "Bientôt nos premiers articles" state
-- until rows are inserted (admin UI to come later — for now, INSERT
-- rows via phpMyAdmin).
--
-- Safe to re-run: CREATE TABLE IF NOT EXISTS is idempotent.

CREATE TABLE IF NOT EXISTS posts (
  id                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  slug              VARCHAR(255)   NOT NULL,
  title             VARCHAR(255)   NOT NULL,
  excerpt           TEXT           NULL,
  body              MEDIUMTEXT     NULL,
  cover_url         VARCHAR(512)   NULL,
  status            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  meta_title        VARCHAR(255)   NULL,
  meta_description  VARCHAR(512)   NULL,
  published_at      DATETIME       NULL,
  created_at        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_slug (slug),
  KEY idx_status_published (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
