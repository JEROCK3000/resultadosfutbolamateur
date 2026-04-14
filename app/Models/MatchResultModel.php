<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

/**
 * MatchResultModel.php — Modelo de Resultados de Partidos
 */
class MatchResultModel extends Model
{
    protected string $table = 'match_results';

    public function getByMatch(int $matchId): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM match_results WHERE match_id = :match_id LIMIT 1");
        $stmt->execute([':match_id' => $matchId]);
        return $stmt->fetch();
    }

    /** Crea o actualiza el resultado de un partido (UPSERT) */
    public function upsert(int $matchId, int $homeGoals, int $awayGoals, int $homeYc = 0, int $awayYc = 0, int $homeRc = 0, int $awayRc = 0): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO match_results (match_id, home_goals, away_goals, home_yellow_cards, away_yellow_cards, home_red_cards, away_red_cards, status, created_at, updated_at)
            VALUES (:match_id, :home_goals, :away_goals, :hyc, :ayc, :hrc, :arc, 'official', NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                home_goals = :home_goals2,
                away_goals = :away_goals2,
                home_yellow_cards = :hyc2,
                away_yellow_cards = :ayc2,
                home_red_cards = :hrc2,
                away_red_cards = :arc2,
                status     = 'official',
                updated_at = NOW()
        ");
        return $stmt->execute([
            ':match_id'    => $matchId,
            ':home_goals'  => $homeGoals,
            ':away_goals'  => $awayGoals,
            ':hyc'         => $homeYc,
            ':ayc'         => $awayYc,
            ':hrc'         => $homeRc,
            ':arc'         => $awayRc,
            ':home_goals2' => $homeGoals,
            ':away_goals2' => $awayGoals,
            ':hyc2'        => $homeYc,
            ':ayc2'        => $awayYc,
            ':hrc2'        => $homeRc,
            ':arc2'        => $awayRc,
        ]);
    }

    public function create(array $data): bool
    {
        return $this->upsert(
            (int)$data['match_id'], 
            (int)$data['home_goals'], 
            (int)$data['away_goals'],
            (int)($data['home_yellow_cards'] ?? 0),
            (int)($data['away_yellow_cards'] ?? 0),
            (int)($data['home_red_cards'] ?? 0),
            (int)($data['away_red_cards'] ?? 0)
        );
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE match_results
            SET home_goals = :home_goals, 
                away_goals = :away_goals,
                home_yellow_cards = :hyc,
                away_yellow_cards = :ayc,
                home_red_cards = :hrc,
                away_red_cards = :arc,
                updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':home_goals' => $data['home_goals'],
            ':away_goals' => $data['away_goals'],
            ':hyc'        => $data['home_yellow_cards'] ?? 0,
            ':ayc'        => $data['away_yellow_cards'] ?? 0,
            ':hrc'        => $data['home_red_cards'] ?? 0,
            ':arc'        => $data['away_red_cards'] ?? 0,
            ':id'         => $id,
        ]);
    }
}
