<?php

require_once __DIR__ . '/../../config/database.php';

class TiposAtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    // GET ?controller=tipos&action=listar
    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $stmt = $this->pdo->query(
            'SELECT id, nome, descricao, status FROM tipos_atendimentos ORDER BY id DESC'
        );
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // GET ?controller=tipos&action=buscarPorId&id=1
    // GET ?controller=tipos&action=buscar&id=1
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
            'SELECT id, nome, descricao, status FROM tipos_atendimentos WHERE id = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $tipo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tipo) {
            http_response_code(404);
            echo json_encode(['erro' => 'Tipo de atendimento nao encontrado.']);
            return;
        }

        echo json_encode($tipo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // POST ?controller=tipos&action=criar
    // Body x-www-form-urlencoded: nome, descricao, status
    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome     = trim($_POST['nome']     ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status   = $_POST['status']        ?? 'ativo';

        if ($nome === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'O campo nome e obrigatorio.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status invalido.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO tipos_atendimentos (nome, descricao, status)
                 VALUES (:nome, :descricao, :status)'
            );
            $stmt->bindValue(':nome',      $nome);
            $stmt->bindValue(':descricao', $descricao ?: null);
            $stmt->bindValue(':status',    $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Tipo de atendimento cadastrado com sucesso.',
                'id'       => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar tipo de atendimento.']);
        }
    }

    // POST ?controller=tipos&action=atualizar
    // Body x-www-form-urlencoded: id, nome, descricao, status
    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id       = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome     = trim($_POST['nome']      ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status   = $_POST['status']         ?? 'ativo';

        if (!$id || $nome === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID e nome sao obrigatorios.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status invalido.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE tipos_atendimentos
                 SET nome=:nome, descricao=:descricao, status=:status
                 WHERE id = :id'
            );
            $stmt->bindValue(':nome',      $nome);
            $stmt->bindValue(':descricao', $descricao ?: null);
            $stmt->bindValue(':status',    $status);
            $stmt->bindValue(':id',        $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Tipo de atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar tipo de atendimento.']);
        }
    }

    // POST ?controller=tipos&action=inativar
    // Body x-www-form-urlencoded: id
    public function inativar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID invalido.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                "UPDATE tipos_atendimentos SET status = 'inativo' WHERE id = :id"
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Tipo de atendimento inativado com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao inativar tipo de atendimento.']);
        }
    }
}
