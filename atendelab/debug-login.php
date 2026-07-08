<?php
require_once __DIR__ . '/config/database.php';

$email = 'adm@gmail.com';
$senha = 'password';

// Testa a query
$sql = 'SELECT id, nome, email, senha, perfil, status
        FROM usuarios
        WHERE email = :email
        LIMIT 1';

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':email', $email);
$stmt->execute();

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<pre>";
echo "Usuário encontrado:\n";
var_dump($usuario);

if ($usuario) {
    echo "\nSenha digitada: " . $senha;
    echo "\nHash no banco: " . $usuario['senha'];
    echo "\npassword_verify: ";
    var_dump(password_verify($senha, $usuario['senha']));
    echo "\nStatus: " . $usuario['status'];
}
echo "</pre>";