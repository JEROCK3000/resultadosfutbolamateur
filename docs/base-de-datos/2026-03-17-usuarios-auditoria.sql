-- ============================================================
-- Migración: Sistema de Usuarios y Auditoría
-- Fecha: 2026-03-17
-- ============================================================

USE resultadosfutbol;

-- ── Usuarios del sistema ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150)  NOT NULL,
    email         VARCHAR(200)  NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('admin','registrador') NOT NULL DEFAULT 'registrador',
    league_id     INT UNSIGNED  NULL COMMENT 'NULL = acceso global (admin), INT = liga asignada',
    status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    last_login    DATETIME      NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_email (email),
    CONSTRAINT fk_users_league FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Auditoría de acciones ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED  NOT NULL,
    action      VARCHAR(50)   NOT NULL COMMENT 'create | update | delete | login | logout',
    entity_type VARCHAR(60)   NULL     COMMENT 'Liga | Equipo | Partido | etc.',
    entity_id   INT UNSIGNED  NULL,
    description TEXT          NOT NULL,
    ip_address  VARCHAR(45)   NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_audit_user   (user_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_date   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Usuario administrador inicial ─────────────────────────────
-- Contraseña por defecto: Admin2025! (cambiar en primer login)
INSERT INTO users (name, email, password_hash, role, league_id, status)
VALUES (
    'Administrador',
    'admin@resultadosfutbol.ec',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- secret (bcrypt)
    'admin',
    NULL,
    'active'
) ON DUPLICATE KEY UPDATE id = id;
