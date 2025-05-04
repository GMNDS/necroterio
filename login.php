<?php
session_start();
require_once 'conexao.php';

// Verificar se o usuário já está logado
if(isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

$mensagem = '';

// Processar o formulário de login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = mysqli_real_escape_string($conexao, $_POST['login']);
    $senha = $_POST['senha'];
    
    // Buscar usuário pelo login
    $sql = "SELECT * FROM tb_usuarios WHERE login = ? AND ativo = 1";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 's', $login);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($resultado) == 1) {
        $usuario = mysqli_fetch_assoc($resultado);
        
        // Verificar senha
        if (password_verify($senha, $usuario['senha'])) {
            // Armazenar dados na sessão
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nome_usuario'] = $usuario['nome'];
            $_SESSION['login_usuario'] = $usuario['login'];
            $_SESSION['nivel_usuario'] = $usuario['nivel'];
            
            // Registrar login no histórico do sistema
            $ip = $_SERVER['REMOTE_ADDR'];
            
            // Redirecionar para o dashboard
            header("Location: index.php");
            exit;
        } else {
            $mensagem = '<div class="alerta erro">Senha incorreta.</div>';
        }
    } else {
        $mensagem = '<div class="alerta erro">Usuário não encontrado ou inativo.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Login - Último Suspiro</title>
  <style>
    body, html {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      width: 100vw;
      background-color: #f4f6f8;
    }
    
    .login-container {
      width: 400px;
      background: var(--light-color);
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .logo {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .logo h1 {
      color: var(--primary-color);
      font-size: 2.5rem;
      margin-bottom: 5px;
    }
    
    .logo p {
      color: var(--accent-color);
      font-size: 1rem;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: var(--primary-color);
    }
    
    input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-family: "Cinzel", serif;
      font-size: 1rem;
    }
    
    .btn-submit {
      width: 100%;
      background: var(--btn-color);
      color: #fff;
      border: none;
      cursor: pointer;
      padding: 14px;
      font-weight: 600;
      border-radius: 10px;
      font-family: "Cinzel", serif;
      font-size: 1.1rem;
      margin-top: 10px;
      transition: background 0.2s ease;
    }
    
    .btn-submit:hover {
      background: var(--btn-hover);
    }
    
    .alerta {
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 8px;
      text-align: center;
    }
    
    .sucesso {
      background-color: #d4edda;
      color: #155724;
    }
    
    .erro {
      background-color: #f8d7da;
      color: #721c24;
    }
    
    .footer {
      margin-top: 25px;
      text-align: center;
      color: var(--accent-color);
      font-size: 0.85rem;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="logo">
      <h1>Último Suspiro</h1>
      <p>Sistema de Gestão de Necrotério</p>
    </div>
    
    <?php echo $mensagem; ?>
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <div class="form-group">
        <label for="login">Usuário:</label>
        <input type="text" id="login" name="login" required autofocus>
      </div>
      
      <div class="form-group">
        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>
      </div>
      
      <button type="submit" class="btn-submit">Entrar</button>
    </form>
    
    <div class="footer">
      &copy; <?php echo date('Y'); ?> Último Suspiro - Todos os direitos reservados
    </div>
  </div>
</body>
</html>