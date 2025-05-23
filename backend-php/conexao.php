<?php

$host = getenv('DB_HOST') ?: 'db'; // Nome do serviço do banco no docker-compose
$user = getenv('DB_USER') ?: 'admin';
$pass = getenv('DB_PASSWORD') ?: '11';
$dbname = getenv('DB_NAME') ?: 'BaterPonto';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("❌ Erro na conexão: " . mysqli_connect_error());
}

?>
