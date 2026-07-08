<?php

class DashboardController
{
    private PDO $pdo;

    public function __construct()
    {
        require_once __DIR__ . '/../../config/database.php';
        global $pdo;
        $this->pdo = $pdo;
    }

    public function resumo(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $totalPessoas = (int) $this->pdo->query('SELECT COUNT(*) FROM pessoas')->fetchColumn();
        $totalTipos = (int) $this->pdo->query('SELECT COUNT(*) FROM tipos_atendimentos')->fetchColumn();
        $totalAtendimentos = (int) $this->pdo->query('SELECT COUNT(*) FROM atendimentos')->fetchColumn();

        $stmt = $this->pdo->query(
            'SELECT a.id, p.nome AS pessoa, t.nome AS tipo, u.nome AS responsavel,
                    a.data_atendimento, a.status
             FROM atendimentos a
             LEFT JOIN pessoas p ON p.id = a.pessoa_id
             LEFT JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
             LEFT JOIN usuarios u ON u.id = a.usuario_id
             ORDER BY a.id DESC
             LIMIT 5'
        );
        $atendimentosRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'indicadores' => [
                'total_pessoas'      => $totalPessoas,
                'total_tipos'        => $totalTipos,
                'total_atendimentos' => $totalAtendimentos,
            ],
            'atendimentos_recentes' => $atendimentosRecentes,
        ], JSON_UNESCAPED_UNICODE);
    }
}
