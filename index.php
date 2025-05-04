<?php
require_once 'verificar_sessao.php';
require_once 'conexao.php';

// Consulta para contar corpos ativos
$sql_ativos = "SELECT COUNT(*) as total FROM tb_recepcao WHERE id_morto NOT IN (SELECT id_morto FROM tb_saida)";
$resultado_ativos = mysqli_query($conexao, $sql_ativos);
$total_ativos = mysqli_fetch_assoc($resultado_ativos)['total'];

// Consulta para contar corpos que já saíram
$sql_saidas = "SELECT COUNT(*) as total FROM tb_saida";
$resultado_saidas = mysqli_query($conexao, $sql_saidas);
$total_saidas = mysqli_fetch_assoc($resultado_saidas)['total'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Último Suspiro</title>
  <style>
    .user-info {
      padding: 10px;
      margin-top: 20px;
      border-top: 1px solid #eee;
      color: var(--accent-color);
      font-size: 0.9rem;
    }
    
    .user-info span {
      font-weight: 600;
      color: var(--primary-color);
    }
  </style>
</head>
<body>
  <header class="sidebar">
    <h2>Último Suspiro</h2>
    <nav>
      <ul>
        <li> <a href="index.php">Dashboard</a></li>
        <li> <a href="cadastro.php">Entrada de corpos</a></li>
        <li> <a href="saida.php">Saída de corpos</a></li>
        <li> <a href="lista_corpos.php">Lista de corpos</a></li>
        <li> <a href="historico.php">Histórico de saídas</a></li>
        <?php if ($_SESSION['nivel_usuario'] == 'admin'): ?>
        <li> <a href="usuarios.php">Gerenciar Usuários</a></li>
        <?php endif; ?>
        <li> <a href="logout.php">Sair do Sistema</a></li>
      </ul>
    </nav>
    <div class="user-info">
      Usuário: <span><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span>
      <br>
      Nível: <span><?php echo $_SESSION['nivel_usuario'] == 'admin' ? 'Administrador' : 'Funcionário'; ?></span>
    </div>
  </header>
  <main class="main">
    <section>
      <h1>Dashboard</h1>
      <p>Visão geral do sistema do necrotério</p>
    </section>
    <section class="section">
    <h2>Ações</h2>
    <div class="card-container">
        <div class="card">
      <h2>Nova Recepção</h2>
      <p>Cadastrar entrada de novo corpo</p>
      <a href="cadastro.php" class="btn">Cadastrar</a>
    </div>

    <div class="card">
      <h2>Registrar Saída</h2>
      <p>Registrar a saída de um corpo</p>
      <a href="saida.php" class="btn">Liberar</a>
    </div> 
    </div>
    </section>

    <section class="section">
    <h2>Visão Geral</h2>
    <div class="card-container">
      <div class="card">
        <h2>Corpos ativos</h2>
        <p>Total de corpos ativos no necrotério</p>
        <h3><?php echo $total_ativos; ?></h3>
        <a href="lista_corpos.php">ver detalhes &gt;</a>
      </div>

      <div class="card">
        <h2>Corpos que já saíram</h2>
        <p>Total de corpos que já saíram</p>
        <h3><?php echo $total_saidas; ?></h3>
        <a href="historico.php">ver detalhes &gt;</a>
      </div> 
    </div>
    </section>
  </main>
</body>
</html>