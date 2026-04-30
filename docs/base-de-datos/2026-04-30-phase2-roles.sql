-- =============================================================
-- Migración: Fase 2 — Roles y Team Manager
-- Fecha: 2026-04-30
-- =============================================================

ALTER TABLE users
  ADD COLUMN team_id INT(10) UNSIGNED NULL AFTER league_id,
  ADD CONSTRAINT fk_users_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL;

ALTER TABLE users
  MODIFY COLUMN role ENUM('admin','registrador','team_manager') NOT NULL DEFAULT 'registrador';
