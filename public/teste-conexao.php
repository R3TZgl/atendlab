<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host    = 'localhost';
$porta   = '3306';
$banco   = 'atendelab';
$usuario = 'root';
$senha   = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$porta};dbname={$banco};charset=utf8mb4",
        $usuario,
        $senha
    );
    echo "Conexão realizada com sucesso!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
