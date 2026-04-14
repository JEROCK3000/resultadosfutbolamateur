<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

/**
 * TeamModel.php — Modelo de Equipos
 */
class TeamModel extends Model
{
    protected string $table = 'teams';

    /** Obtiene todos los equipos con nombre de liga */
    public function getAll(string $orderBy = 't.name', string $direction = 'ASC'): array
    {
        $stmt = $this->db->query("
            SELECT t.*, l.name AS league_name
            FROM teams t
            LEFT JOIN leagues l ON t.league_id = l.id
            ORDER BY {$orderBy} {$direction}
        ");
        return $stmt->fetchAll();
    }

    /** Equipos por liga */
    public function getByLeague(int $leagueId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, l.name AS league_name
            FROM teams t
            LEFT JOIN leagues l ON t.league_id = l.id
            WHERE t.league_id = :league_id 
            ORDER BY t.name ASC
        ");
        $stmt->execute([':league_id' => $leagueId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT t.*, l.name AS league_name
            FROM teams t
            LEFT JOIN leagues l ON t.league_id = l.id
            WHERE t.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO teams (league_id, name, short_name, logo, founded_year, created_at, updated_at)
            VALUES (:league_id, :name, :short_name, :logo, :founded_year, NOW(), NOW())
        ");
        return $stmt->execute([
            ':league_id'    => $data['league_id'],
            ':name'         => $data['name'],
            ':short_name'   => $data['short_name'] ?? null,
            ':logo'         => $data['logo'] ?? null,
            ':founded_year' => $data['founded_year'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE teams
            SET league_id = :league_id, name = :name, short_name = :short_name,
                logo = COALESCE(:logo, logo),
                founded_year = :founded_year, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':league_id'    => $data['league_id'],
            ':name'         => $data['name'],
            ':short_name'   => $data['short_name'] ?? null,
            ':logo'         => $data['logo'] ?? null,
            ':founded_year' => $data['founded_year'] ?? null,
            ':id'           => $id,
        ]);
    }

    /** Para el select encadenado Liga→Equipos (respuesta JSON) */
    public function getByLeagueForJson(int $leagueId): array
    {
        return $this->getByLeague($leagueId);
    }
}
