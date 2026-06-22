<?php

require_once __DIR__ . '/../../config/database.php';

class UsuariosController
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    // GET ?controller=usuarios&action=listar
    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql  = 'SELECT id, nome, email, perfil, status, criado_em
                 FROM usuarios
                 ORDER BY id DESC';
        $stmt = $this->pdo->query($sql);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // GET ?controller=usuarios&action=buscarPorId&id=1
    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID invalido.']);
            return;
        }

        $sql  = 'SELECT id, nome, email, perfil, status, criado_em
                 FROM usuarios
                 WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['erro' => 'Usuario nao encontrado.']);
            return;
        }

        echo json_encode($usuario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // POST ?controller=usuarios&action=criar
    // Body: form-encode   nome, email, senha, perfil, status
    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome   = trim($_POST['nome']   ?? '');
        $email  = trim($_POST['email']  ?? '');
        $senha  = $_POST['senha']       ?? '';
        $perfil = $_POST['perfil']      ?? 'atendente';
        $status = $_POST['status']      ?? 'ativo';

        if ($nome === '' || $email === '' || $senha === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome, e-mail e senha sao obrigatorios.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail invalido.']);
            return;
        }

        if (!in_array($perfil, ['admin', 'atendente', 'aluno'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Perfil invalido.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status invalido.']);
            return;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $sql = 'INSERT INTO usuarios (nome, email, senha, perfil, status)
                    VALUES (:nome, :email, :senha, :perfil, :status)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome',   $nome);
            $stmt->bindValue(':email',  $email);
            $stmt->bindValue(':senha',  $senhaHash);
            $stmt->bindValue(':perfil', $perfil);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Usuario cadastrado com sucesso.',
                'id'       => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar usuario.']);
        }
    }

    // POST ?controller=usuarios&action=atualizar
    // Body: form-encode   id, nome, email, perfil, status
    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome   = trim($_POST['nome']   ?? '');
        $email  = trim($_POST['email']  ?? '');
        $perfil = $_POST['perfil']      ?? 'atendente';
        $status = $_POST['status']      ?? 'ativo';

        if (!$id || $nome === '' || $email === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID, nome e e-mail sao obrigatorios.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail invalido.']);
            return;
        }

        if (!in_array($perfil, ['admin', 'atendente', 'aluno'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Perfil invalido.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status invalido.']);
            return;
        }

        try {
            $sql = 'UPDATE usuarios
                    SET nome   = :nome,
                        email  = :email,
                        perfil = :perfil,
                        status = :status
                    WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome',   $nome);
            $stmt->bindValue(':email',  $email);
            $stmt->bindValue(':perfil', $perfil);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id',     $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(
                ['mensagem' => 'Usuario atualizado com sucesso.'],
                JSON_UNESCAPED_UNICODE
            );

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar usuario.']);
        }
    }

    // POST ?controller=usuarios&action=excluir
    // Body: form-encode   id
    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID invalido.']);
            return;
        }

        try {
            $sql  = 'DELETE FROM usuarios WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(
                ['mensagem' => 'Usuario excluido com sucesso.'],
                JSON_UNESCAPED_UNICODE
            );

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir usuario.']);
        }
    }
}
