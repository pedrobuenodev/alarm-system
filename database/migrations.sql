-- ============================================================
-- Alarm System - Database Schema
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '-03:00';

-- ------------------------------------------------------------
-- Equipamentos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS equipments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)    NOT NULL,
    serial      VARCHAR(100)    NOT NULL UNIQUE,
    type        ENUM('voltage','current','oil') NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at  DATETIME        NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Alarmes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS alarms (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    description     VARCHAR(255)    NOT NULL,
    classification  ENUM('urgent','emergent','ordinary') NOT NULL,
    equipment_id    INT UNSIGNED    NOT NULL,
    status          ENUM('on','off') NOT NULL DEFAULT 'off',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME        NULL DEFAULT NULL,
    CONSTRAINT fk_alarms_equipment
        FOREIGN KEY (equipment_id) REFERENCES equipments(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Alarmes Atuados (eventos de ativação/desativação)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS alarm_events (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alarm_id    INT UNSIGNED    NOT NULL,
    entered_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    exited_at   DATETIME        NULL DEFAULT NULL,
    status      ENUM('on','off') NOT NULL,
    CONSTRAINT fk_events_alarm
        FOREIGN KEY (alarm_id) REFERENCES alarms(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Audit Log - registro de todas as ações
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity      VARCHAR(60)     NOT NULL,
    entity_id   INT UNSIGNED    NULL,
    action      VARCHAR(60)     NOT NULL,
    payload     JSON            NULL,
    ip          VARCHAR(45)     NOT NULL,
    user_agent  VARCHAR(255)    NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Índices de performance
-- ------------------------------------------------------------
CREATE INDEX idx_alarms_status       ON alarms(status);
CREATE INDEX idx_alarms_deleted      ON alarms(deleted_at);
CREATE INDEX idx_equipments_deleted  ON equipments(deleted_at);
CREATE INDEX idx_events_entered      ON alarm_events(entered_at);
CREATE INDEX idx_audit_entity        ON audit_logs(entity, entity_id);
CREATE INDEX idx_audit_created       ON audit_logs(created_at);
