<?php

class AtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require_once __DIR__ . '/../../config/database.php';
        global $pdo;
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $stmt = $this->pdo->query(
            'SELECT a.id, p.nome AS pessoa, t.nome AS tipo, u.nome AS responsavel,
                    a.data_atendimento, a.horario_atendimento, a.descricao, a.status, a.observacao_final
             FROM atendimentos a
             LEFT JOIN pessoas p ON p.id = a.pessoa_id
             LEFT JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
             LEFT JOIN usuarios u ON u.id = a.usuario_id
             ORDER BY a.id DESC'
        );
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['atendimentos' => $atendimentos], JSON_UNESCAPED_UNICODE);
    }

    public function visualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT a.id, a.pessoa_id, a.tipo_atendimento_id, a.usuario_id,
                    p.nome AS pessoa, t.nome AS tipo, u.nome AS responsavel,
                    a.data_atendimento, a.horario_atendimento, a.descricao, a.status, a.observacao_final
             FROM atendimentos a
             LEFT JOIN pessoas p ON p.id = a.pessoa_id
             LEFT JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
             LEFT JOIN usuarios u ON u.id = a.usuario_id
             WHERE a.id = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento não encontrado.']);
            return;
        }

        echo json_encode(['atendimento' => $atendimento], JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoaId   = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoId     = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $descricao  = trim($_POST['descricao'] ?? '');
        $data       = trim($_POST['data_atendimento'] ?? '');
        $horario    = trim($_POST['horario_atendimento'] ?? '');

        if (!$pessoaId || !$tipoId || $descricao === '' || $data === '' || $horario === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Pessoa, tipo, descrição, data e horário são obrigatórios.']);
            return;
        }

        $usuarioId = $this->usuarioResponsavel();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO atendimentos (pessoa_id, tipo_atendimento_id, usuario_id, descricao, status, data_atendimento, horario_atendimento)
                 VALUES (:pessoa_id, :tipo_atendimento_id, :usuario_id, :descricao, "aberto", :data_atendimento, :horario_atendimento)'
            );
            $stmt->bindValue(':pessoa_id',            $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id',  $tipoId,   PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id',           $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':descricao',            $descricao);
            $stmt->bindValue(':data_atendimento',     $data);
            $stmt->bindValue(':horario_atendimento',  $horario);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Atendimento registrado com sucesso.',
                'id'       => $this->pdo->lastInsertId(),
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao registrar atendimento.']);
        }
    }

    public function atualizarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id              = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status          = $_POST['status'] ?? '';
        $observacaoFinal = trim($_POST['observacao_final'] ?? '');

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        if (!in_array($status, ['aberto', 'em_andamento', 'concluido'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        if ($status === 'concluido' && $observacaoFinal === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Observação final é obrigatória ao concluir.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE atendimentos SET status = :status, observacao_final = :observacao_final WHERE id = :id'
            );
            $stmt->bindValue(':status',           $status);
            $stmt->bindValue(':observacao_final', $observacaoFinal ?: null);
            $stmt->bindValue(':id',               $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Status atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar status.']);
        }
    }

    public function alterarStatus(): void
    {
        $this->atualizarStatus();
    }

    public function opcoesFormulario(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoas = $this->pdo->query(
            'SELECT id, nome FROM pessoas WHERE status = "ativo" ORDER BY nome'
        )->fetchAll(PDO::FETCH_ASSOC);

        $tipos = $this->pdo->query(
            'SELECT id, nome FROM tipos_atendimentos WHERE status = "ativo" ORDER BY nome'
        )->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['pessoas' => $pessoas, 'tipos' => $tipos], JSON_UNESCAPED_UNICODE);
    }

    private function usuarioResponsavel(): int
    {
        if (isset($_SESSION['usuario']['id'])) {
            return (int) $_SESSION['usuario']['id'];
        }

        $id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'Usuário não autenticado.']);
            exit;
        }

        return $id;
    }
}
