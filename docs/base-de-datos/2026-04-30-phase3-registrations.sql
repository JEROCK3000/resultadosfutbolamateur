-- =============================================================
-- Migración: Fase 3 — Inscripciones de Equipos en Campeonatos
-- Fecha: 2026-04-30
-- =============================================================

CREATE TABLE IF NOT EXISTS championship_registrations (
  id           INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  team_id      INT(10) UNSIGNED NOT NULL,
  league_id    INT(10) UNSIGNED NOT NULL,
  status       ENUM('pending','approved','rejected') DEFAULT 'pending',
  notes        TEXT NULL,
  submitted_by INT(10) UNSIGNED NOT NULL,
  reviewed_by  INT(10) UNSIGNED NULL,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_team_league_reg (team_id, league_id),
  FOREIGN KEY (team_id)      REFERENCES teams(id)   ON DELETE CASCADE,
  FOREIGN KEY (league_id)    REFERENCES leagues(id) ON DELETE CASCADE,
  FOREIGN KEY (submitted_by) REFERENCES users(id)   ON DELETE CASCADE,
  FOREIGN KEY (reviewed_by)  REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
