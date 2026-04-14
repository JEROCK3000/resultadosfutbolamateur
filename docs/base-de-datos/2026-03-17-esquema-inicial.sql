-- ============================================================
-- Esquema de Base de Datos — Sistema Multiligas de Fútbol
-- Motor: MariaDB 10.6 LTS | Charset: utf8mb4
-- Fecha: 2026-03-17
-- ============================================================


-- ── Estadios ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS stadiums (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    city       VARCHAR(100) NOT NULL,
    country    VARCHAR(100) NOT NULL,
    capacity   INT UNSIGNED NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Ligas ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS leagues (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    season      VARCHAR(20)  NOT NULL,
    country     VARCHAR(100) NOT NULL,
    description TEXT         NULL,
    status      ENUM('active','inactive','finished') NOT NULL DEFAULT 'active',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Equipos ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS teams (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    league_id    INT UNSIGNED NOT NULL,
    name         VARCHAR(150) NOT NULL,
    short_name   VARCHAR(10)  NULL,
    founded_year YEAR         NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_teams_league FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE CASCADE,
    INDEX idx_league (league_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Partidos / Encuentros ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS matches (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    league_id    INT UNSIGNED NOT NULL,
    home_team_id INT UNSIGNED NOT NULL,
    away_team_id INT UNSIGNED NOT NULL,
    stadium_id   INT UNSIGNED NOT NULL,
    match_date   DATE         NOT NULL,
    match_time   TIME         NOT NULL,
    status       ENUM('scheduled','live','finished','postponed') NOT NULL DEFAULT 'scheduled',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Evitar duplicado estadio+fecha+hora
    UNIQUE KEY uq_stadium_slot (stadium_id, match_date, match_time),
    CONSTRAINT fk_matches_league    FOREIGN KEY (league_id)    REFERENCES leagues(id)  ON DELETE CASCADE,
    CONSTRAINT fk_matches_home_team FOREIGN KEY (home_team_id) REFERENCES teams(id)    ON DELETE CASCADE,
    CONSTRAINT fk_matches_away_team FOREIGN KEY (away_team_id) REFERENCES teams(id)    ON DELETE CASCADE,
    CONSTRAINT fk_matches_stadium   FOREIGN KEY (stadium_id)   REFERENCES stadiums(id) ON DELETE CASCADE,
    INDEX idx_match_date  (match_date),
    INDEX idx_match_status (status),
    INDEX idx_match_league (league_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Resultados de Partidos ────────────────────────────────────
CREATE TABLE IF NOT EXISTS match_results (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_id   INT UNSIGNED NOT NULL,
    home_goals TINYINT UNSIGNED NOT NULL DEFAULT 0,
    away_goals TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status     ENUM('pending','official') NOT NULL DEFAULT 'official',
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_match_result (match_id),
    CONSTRAINT fk_results_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Eventos de Partido (goles, tarjetas) ──────────────────────
CREATE TABLE IF NOT EXISTS match_events (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_id    INT UNSIGNED     NOT NULL,
    team_id     INT UNSIGNED     NOT NULL,
    player_name VARCHAR(150)     NULL,
    event_type  ENUM('goal','yellow_card','red_card') NOT NULL,
    minute      TINYINT UNSIGNED NULL,
    created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_match FOREIGN KEY (match_id) REFERENCES matches(id)   ON DELETE CASCADE,
    CONSTRAINT fk_events_team  FOREIGN KEY (team_id)  REFERENCES teams(id)     ON DELETE CASCADE,
    INDEX idx_event_match (match_id),
    INDEX idx_event_type  (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Torneos / Fases Finales ───────────────────────────────────
CREATE TABLE IF NOT EXISTS tournaments (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    league_id  INT UNSIGNED NOT NULL,
    name       VARCHAR(200) NOT NULL,
    type       ENUM('knockout','seeded') NOT NULL DEFAULT 'knockout',
    status     ENUM('active','finished') NOT NULL DEFAULT 'active',
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tournaments_league FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Rondas del Torneo ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tournament_rounds (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT UNSIGNED  NOT NULL,
    round_name    VARCHAR(100)  NOT NULL,
    round_order   TINYINT       NOT NULL DEFAULT 1,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rounds_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    INDEX idx_round_order (tournament_id, round_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Cruces dentro de cada Ronda ───────────────────────────────
CREATE TABLE IF NOT EXISTS tournament_matches (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    round_id      INT UNSIGNED    NOT NULL,
    match_id      INT UNSIGNED    NULL,
    home_team_id  INT UNSIGNED    NOT NULL,
    away_team_id  INT UNSIGNED    NOT NULL,
    position_home TINYINT UNSIGNED NULL COMMENT 'Posición en tabla (ej: 1)',
    position_away TINYINT UNSIGNED NULL COMMENT 'Posición en tabla (ej: 8)',
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tm_round     FOREIGN KEY (round_id)     REFERENCES tournament_rounds(id) ON DELETE CASCADE,
    CONSTRAINT fk_tm_match     FOREIGN KEY (match_id)     REFERENCES matches(id)           ON DELETE SET NULL,
    CONSTRAINT fk_tm_home_team FOREIGN KEY (home_team_id) REFERENCES teams(id)             ON DELETE CASCADE,
    CONSTRAINT fk_tm_away_team FOREIGN KEY (away_team_id) REFERENCES teams(id)             ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
