-- =============================================================
-- Migración: Módulo de Jugadores (Fase 1)
-- Fecha: 2026-04-30
-- Aplicar en: VPS producción (local ya aplicado)
-- =============================================================

-- 1. Tabla maestra de jugadores (deduplicada por cédula)
CREATE TABLE IF NOT EXISTS players (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  cedula      VARCHAR(20)  NOT NULL UNIQUE,
  name        VARCHAR(150) NOT NULL,
  birth_date  DATE         NULL,
  position    ENUM('portero','defensa','mediocampista','delantero','otro') DEFAULT 'otro',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Membresías jugador ↔ equipo ↔ campeonato
CREATE TABLE IF NOT EXISTS team_players (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  team_id     INT NOT NULL,
  league_id   INT NOT NULL,
  player_id   INT NOT NULL,
  number      TINYINT UNSIGNED NULL,
  status      ENUM('active','suspended','inactive') DEFAULT 'active',
  joined_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_team_player_league (team_id, player_id, league_id),
  FOREIGN KEY (team_id)   REFERENCES teams(id)   ON DELETE CASCADE,
  FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE CASCADE,
  FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Vincular eventos de partido a jugadores
ALTER TABLE match_events
  ADD COLUMN IF NOT EXISTS player_id INT NULL AFTER team_id,
  ADD CONSTRAINT fk_match_events_player
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL;
