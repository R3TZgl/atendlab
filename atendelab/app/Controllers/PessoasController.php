<?php

class PessoasController
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
            'SELECT id, nome, documento, telefone, email, curso, periodo, observacoes, status
             FROM pessoas
             ORDER BY nome'
        );
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['pessoas' => $pessoas], JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT id, nome, documento, telefone, email, curso, periodo, observacoes, status
             FROM pessoas
             WHERE id = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada.']);
            return;
        }

        echo json_encode(['pessoa' => $pessoa], JSON_UNESCAPED_UNICODE);
    }

    public function buscar(): void
    {
        $this->buscarPorId();
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome      = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telefone  = trim($_POST['telefone'] ?? '');
        $curso     = trim($_POST['curso'] ?? '');
        $periodo   = trim($_POST['periodo'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status    = $_POST['status'] ?? 'ativo';

        if ($nome === '' || $documento === '' || $email === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome, documento e e-mail são obrigatórios.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            $status = 'ativo';
        }

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO pessoas (nome, documento, telefone, email, curso, periodo, observacoes, status)
                 VALUES (:nome, :documento, :telefone, :email, :curso, :periodo, :observacoes, :status)'
            );
            $stmt->bindValue(':nome',        $nome);
            $stmt->bindValue(':documento',   $documento);
            $stmt->bindValue(':telefone',    $telefone);
            $stmt->bindValue(':email',       $email);
            $stmt->bindValue(':curso',       $curso);
            $stmt->bindValue(':periodo',     $periodo);
            $stmt->bindValue(':observacoes', $observacoes);
            $stmt->bindValue(':status',      $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id'       => $this->pdo->lastInsertId(),
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar pessoa.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome      = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telefone  = trim($_POST['telefone'] ?? '');
        $curso     = trim($_POST['curso'] ?? '');
        $periodo   = trim($_POST['periodo'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status    = $_POST['status'] ?? 'ativo';

        if (!$id || $nome === '' || $documento === '' || $email === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID, nome, documento e e-mail são obrigatórios.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            $status = 'ativo';
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE pessoas
                    SET nome = :nome, documento = :documento, telefone = :telefone,
                        email = :email, curso = :curso, periodo = :periodo,
                        observacoes = :observacoes, status = :status
                  WHERE id = :id'
            );
            $stmt->bindValue(':nome',        $nome);
            $stmt->bindValue(':documento',   $documento);
            $stmt->bindValue(':telefone',    $telefone);
            $stmt->bindValue(':email',       $email);
            $stmt->bindValue(':curso',       $curso);
            $stmt->bindValue(':periodo',     $periodo);
            $stmt->bindValue(':observacoes', $observacoes);
            $stmt->bindValue(':status',      $status);
            $stmt->bindValue(':id',          $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa.']);
        }
    }

    public function inativar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare('UPDATE pessoas SET status = "inativo" WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa inativada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao inativar pessoa.']);
        }
    }
}
