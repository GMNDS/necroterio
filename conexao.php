<?php
// Configurações do banco de dados
$host = "db"; // Alterado para o nome do serviço no docker-compose
$usuario = "root";
$senha = "root"; // Senha atualizada conforme devcontainer
$banco = "necroterio";

// Criar conexão
$conexao = mysqli_connect($host, $usuario, $senha, $banco);

// Verificar conexão
if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());
}

// Definir charset para UTF-8
mysqli_set_charset($conexao, "utf8mb4"); // Atualizado para utf8mb4 conforme o schema
?>