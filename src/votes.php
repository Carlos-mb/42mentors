<?php
// «Me ayudó»: valoraciones de 1 a 3 de un estudiante a un mentor por un proyecto (tabla votes).
// Los votos son anónimos: fuera de «Mis valoraciones» solo se muestran totales, nunca quién votó ni cuándo.
declare(strict_types=1);

const VOTE_LABELS = [
    1 => 'Un poco',
    2 => 'Bastante',
    3 => 'Mucho',
];

/** Puntos y número de votos que ha recibido un mentor, por proyecto: [project_id => ['points' => n, 'votes' => n]]. */
function mentor_vote_totals(int $mentorId): array
{
    $stmt = db()->prepare(
        'SELECT project_id, SUM(value) AS points, COUNT(*) AS votes
           FROM votes
          WHERE mentor_id = ?
          GROUP BY project_id'
    );
    $stmt->execute([$mentorId]);
    $totals = [];
    foreach ($stmt->fetchAll() as $row) {
        $totals[(int) $row['project_id']] = ['points' => (int) $row['points'], 'votes' => (int) $row['votes']];
    }
    return $totals;
}

/** Mis votos a un mentor: [project_id => valor]. */
function my_votes_for(int $voterId, int $mentorId): array
{
    $stmt = db()->prepare('SELECT project_id, value FROM votes WHERE voter_id = ? AND mentor_id = ?');
    $stmt->execute([$voterId, $mentorId]);
    $votes = [];
    foreach ($stmt->fetchAll() as $row) {
        $votes[(int) $row['project_id']] = (int) $row['value'];
    }
    return $votes;
}

/** "7 puntos · 3 valoraciones" */
function points_text(int $points, int $votes): string
{
    return $points . ($points === 1 ? ' punto' : ' puntos') . ' · '
        . $votes . ($votes === 1 ? ' valoración' : ' valoraciones');
}

/** Botones «Un poco / Bastante / Mucho» (HTML). $back: adónde vuelve vote.php ('mentor' o 'ratings'). */
function vote_form(int $mentorId, int $projectId, ?int $current, string $back = 'mentor'): string
{
    $html = '<form method="post" action="vote.php" class="vote">'
        . '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'
        . '<input type="hidden" name="mentor_id" value="' . $mentorId . '">'
        . '<input type="hidden" name="project_id" value="' . $projectId . '">'
        . '<input type="hidden" name="back" value="' . e($back) . '">';
    foreach (VOTE_LABELS as $value => $label) {
        $pressed = $current === $value;
        $html .= '<button type="submit" name="value" value="' . $value . '"'
            . ' class="vote-btn' . ($pressed ? ' active' : '') . '"'
            . ' aria-pressed="' . ($pressed ? 'true' : 'false') . '"'
            . ' title="' . e('Me ayudó: ' . $label . ' (' . $value . ($value === 1 ? ' punto' : ' puntos') . ')') . '">'
            . e($label) . '</button>';
    }
    return $html . '</form>';
}
