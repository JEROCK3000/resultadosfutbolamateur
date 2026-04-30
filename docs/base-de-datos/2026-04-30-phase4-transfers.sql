-- =============================================================
-- Migración: Fase 4 — Ventanas de Pase y Transferencias
-- Fecha: 2026-04-30
-- =============================================================

CREATE TABLE IF NOT EXISTS transfer_windows (
  id          INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  league_id   INT(10) UNSIGNED NOT NULL,
  name        VARCHAR(100) NOT NULL,
  opens_at    DATE NOT NULL,
  closes_at   DATE NOT NULL,
  status      ENUM('active','closed') DEFAULT 'active',
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS player_transfers (
  id            INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id     INT(10) UNSIGNED NOT NULL,
  from_team_id  INT(10) UNSIGNED NOT NULL,
  to_team_id    INT(10) UNSIGNED NOT NULL,
  league_id     INT(10) UNSIGNED NOT NULL,
  window_id     INT(10) UNSIGNED NULL,
  status        ENUM('pending','approved','rejected') DEFAULT 'pending',
  notes         TEXT NULL,
  requested_by  INT(10) UNSIGNED NOT NULL,
  reviewed_by   INT(10) UNSIGNED NULL,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (player_id)    REFERENCES players(id)          ON DELETE CASCADE,
  FOREIGN KEY (from_team_id) REFERENCES teams(id)            ON DELETE CASCADE,
  FOREIGN KEY (to_team_id)   REFERENCES teams(id)            ON DELETE CASCADE,
  FOREIGN KEY (league_id)    REFERENCES leagues(id)          ON DELETE CASCADE,
  FOREIGN KEY (window_id)    REFERENCES transfer_windows(id) ON DELETE SET NULL,
  FOREIGN KEY (requested_by) REFERENCES users(id)            ON DELETE CASCADE,
  FOREIGN KEY (reviewed_by)  REFERENCES users(id)            ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
