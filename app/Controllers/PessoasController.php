<?php

require_once __DIR__ . '/../../config/database.php';

class PessoasController
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    // GET ?controller=pessoas&action=listar
    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $stmt = $this->pdo->query(
            'SELECT id, nome, documento, telefone, curso, periodo, status
             FROM pessoas ORDER BY id DESC'
        );
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // GET ?controller=pessoas&action=buscar&id=1
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
            'SELECT id, nome, documento, telefone, curso, periodo, status
             FROM pessoas WHERE id = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa nao encontrada.']);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // POST ?controller=pessoas&action=criar
    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome      = trim($_POST['nome']      ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone  = trim($_POST['telefone']  ?? '');
        $curso     = trim($_POST['curso']     ?? '');
        $periodo   = trim($_POST['periodo']   ?? '');
        $status    = $_POST['status']         ?? 'ativo';

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
                'INSERT INTO pessoas (nome, documento, telefone, curso, periodo, status)
                 VALUES (:nome, :documento, :telefone, :curso, :periodo, :status)'
            );
            $stmt->bindValue(':nome',      $nome);
            $stmt->bindValue(':documento', $documento ?: null);
            $stmt->bindValue(':telefone',  $telefone  ?: null);
            $stmt->bindValue(':curso',     $curso     ?: null);
            $stmt->bindValue(':periodo',   $periodo   ?: null);
            $stmt->bindValue(':status',    $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id'       => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar pessoa.']);
        }
    }

    // POST ?controller=pessoas&action=atualizar
    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome      = trim($_POST['nome']      ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone  = trim($_POST['telefone']  ?? '');
        $curso     = trim($_POST['curso']     ?? '');
        $periodo   = trim($_POST['periodo']   ?? '');
        $status    = $_POST['status']         ?? 'ativo';

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
                'UPDATE pessoas
                 SET nome=:nome, documento=:documento, telefone=:telefone,
                     curso=:curso, periodo=:periodo, status=:status
                 WHERE id = :id'
            );
            $stmt->bindValue(':nome',      $nome);
            $stmt->bindValue(':documento', $documento ?: null);
            $stmt->bindValue(':telefone',  $telefone  ?: null);
            $stmt->bindValue(':curso',     $curso     ?: null);
            $stmt->bindValue(':periodo',   $periodo   ?: null);
            $stmt->bindValue(':status',    $status);
            $stmt->bindValue(':id',        $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa.']);
        }
    }

    // POST ?controller=pessoas&action=inativar
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
            $stmt = $this->pdo->prepare("UPDATE pessoas SET status = 'inativo' WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa inativada com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao inativar pessoa.']);
        }
    }
}
