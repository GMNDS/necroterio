<?php
session_start();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
session_destroy();

// Manda para a página de login
header("Location: login.php");
exit;
?>