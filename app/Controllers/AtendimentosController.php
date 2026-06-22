<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Middleware/auth.php';

class AtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    // GET ?controller=atendimentos&action=listar
    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $stmt = $this->pdo->query(
            'SELECT a.id,
                    p.nome  AS pessoa,
                    t.nome  AS tipo,
                    u.nome  AS responsavel,
                    a.data_atendimento,
                    a.hora_atendimento,
                    a.descricao,
                    a.observacao,
                    a.status,
                    a.criado_em
             FROM atendimentos a
             JOIN pessoas           p ON p.id = a.pessoa_id
             JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
             JOIN usuarios          u ON u.id = a.usuario_id
             ORDER BY a.id DESC'
        );
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // GET ?controller=atendimentos&action=buscarPorId&id=1
    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID invalido.']);
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT a.id,
                    a.pessoa_id, p.nome AS pessoa,
                    a.tipo_atendimento_id, t.nome AS tipo,
                    a.usuario_id, u.nome AS responsavel,
                    a.data_atendimento, a.hora_atendimento,
                    a.descricao, a.observacao, a.status, a.criado_em
             FROM atendimentos a
             JOIN pessoas           p ON p.id = a.pessoa_id
             JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
             JOIN usuarios          u ON u.id = a.usuario_id
             WHERE a.id = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento nao encontrado.']);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // POST ?controller=atendimentos&action=criar
    // Body x-www-form-urlencoded:
    //   pessoa_id, tipo_atendimento_id, data_atendimento, hora_atendimento, descricao
    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoaId         = filter_input(INPUT_POST, 'pessoa_id',         FILTER_VALIDATE_INT);
        $tipoId           = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $dataAtendimento  = trim($_POST['data_atendimento']  ?? '');
        $horaAtendimento  = trim($_POST['hora_atendimento']  ?? '');
        $descricao        = trim($_POST['descricao']         ?? '');

        // Pega o usuario_id da sessao
        $usuarioId = $_SESSION['usuario']['id'] ?? null;

        if (!$pessoaId || !$tipoId || !$usuarioId) {
            http_response_code(400);
            echo json_encode(['erro' => 'Pessoa, tipo e usuario sao obrigatorios.']);
            return;
        }

        if ($dataAtendimento === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'A data do atendimento e obrigatoria.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO atendimentos
                    (pessoa_id, tipo_atendimento_id, usuario_id, data_atendimento, hora_atendimento, descricao, status)
                 VALUES
                    (:pessoa_id, :tipo_id, :usuario_id, :data, :hora, :descricao, "aberto")'
            );
            $stmt->bindValue(':pessoa_id',  $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_id',    $tipoId,   PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':data',       $dataAtendimento);
            $stmt->bindValue(':hora',       $horaAtendimento ?: null);
            $stmt->bindValue(':descricao',  $descricao       ?: null);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Atendimento cadastrado com sucesso.',
                'id'       => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar atendimento.']);
        }
    }

    // POST ?controller=atendimentos&action=atualizarStatus
    // Body x-www-form-urlencoded: id, status, observacao (obrigatorio quando concluido)
    public function atualizarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status    = $_POST['status']    ?? '';
        $observacao = trim($_POST['observacao'] ?? $_POST['observacao_final'] ?? '');

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID invalido.']);
            return;
        }

        if (!in_array($status, ['aberto', 'em_andamento', 'concluido'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status invalido. Use: aberto, em_andamento ou concluido.']);
            return;
        }

        if ($status === 'concluido' && $observacao === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'A observacao final e obrigatoria ao concluir.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE atendimentos
                 SET status     = :status,
                     observacao = :observacao
                 WHERE id = :id'
            );
            $stmt->bindValue(':status',     $status);
            $stmt->bindValue(':observacao', $observacao ?: null);
            $stmt->bindValue(':id',         $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Status atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar status.']);
        }
    }
}
