<?php

$host = getenv('DB_HOST') ?: 'db';          // serviço do banco no docker-compose
$user = getenv('DB_USER') ?: 'admin';
$pass = getenv('DB_PASSWORD') ?: 'root';
$dbname = getenv('DB_NAME') ?: 'BaterPonto';
$charset = 'utf8mb4';

// Monta o DSN para PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Opções para o PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // lança exceções em erro
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // busca como array associativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // usa prepared statements reais
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("❌ Erro na conexão: " . $e->getMessage());
}

?>
