-- =============================================================
-- Migración: Fase 5 — Sanciones y Estadísticas
-- Fecha: 2026-04-30
-- =============================================================

CREATE TABLE IF NOT EXISTS player_sanctions (
  id              INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id       INT(10) UNSIGNED NOT NULL,
  league_id       INT(10) UNSIGNED NOT NULL,
  match_id        INT(10) UNSIGNED NULL,
  type            ENUM('auto','disciplinary') DEFAULT 'auto',
  reason          VARCHAR(255) NOT NULL,
  matches_qty     INT UNSIGNED DEFAULT 0,
  matches_served  INT UNSIGNED DEFAULT 0,
  fine_usd        DECIMAL(8,2) DEFAULT 0.00,
  fine_paid       TINYINT(1)   DEFAULT 0,
  active          TINYINT(1)   DEFAULT 1,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_player_league (player_id, league_id),
  FOREIGN KEY (player_id) REFERENCES players(id)  ON DELETE CASCADE,
  FOREIGN KEY (league_id) REFERENCES leagues(id)  ON DELETE CASCADE,
  FOREIGN KEY (match_id)  REFERENCES matches(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- player_id en match_events ya se agregó en la Fase 1
-- Verificar que tenga la columna (si la migración anterior no se aplicó)
-- ALTER TABLE match_events ADD COLUMN IF NOT EXISTS player_id INT(10) UNSIGNED NULL AFTER team_id;
