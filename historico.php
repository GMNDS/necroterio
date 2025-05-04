<?php
require_once 'verificar_sessao.php';
require_once 'conexao.php';

// Buscar histórico de saídas
$sql = "SELECT r.id_morto, r.nome, r.origem, r.data_entrada, r.status, r.identificacao,
               s.id_saida, s.data_saida, s.destino, s.responsavel_liberacao, s.documento_autorizacao
        FROM tb_recepcao r
        INNER JOIN tb_saida s ON r.id_morto = s.id_morto
        ORDER BY s.data_saida DESC";
$resultado = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Histórico de Saídas - Último Suspiro</title>
  <style>
    .table-container {
      overflow-x: auto;
      margin-top: 20px;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      background: var(--card-bg);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    
    th {
      background-color: var(--primary-color);
      color: var(--light-color);
      font-weight: 600;
    }
    
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    
    tr:hover {
      background-color: var(--hover-color);
    }
    
    .status {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 50px;
      font-size: 0.85rem;
    }
    
    .identificado {
      background-color: #d4edda;
      color: #155724;
    }
    
    .nao-identificado {
      background-color: #f8d7da;
      color: #721c24;
    }
    
    .em-processo {
      background-color: #fff3cd;
      color: #856404;
    }
    
    .empty-message {
      padding: 20px;
      text-align: center;
      background: var(--card-bg);
      border-radius: 16px;
      margin-top: 20px;
      color: var(--primary-color);
      font-weight: 600;
    }
    
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
      <h1>Histórico de Saídas</h1>
      <p>Registro de todos os corpos que já saíram do necrotério</p>
      
      <?php if (mysqli_num_rows($resultado) > 0): ?>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Data Entrada</th>
                <th>Data Saída</th>
                <th>Permanência</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Status</th>
                <th>Responsável</th>
                <th>Documento</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($corpo = mysqli_fetch_assoc($resultado)): 
                // Calcular dias de permanência
                $data_entrada = new DateTime($corpo['data_entrada']);
                $data_saida = new DateTime($corpo['data_saida']);
                $permanencia = $data_entrada->diff($data_saida)->days;
                
                // Se a permanência for menos de um dia, mostrar em horas
                if ($permanencia == 0) {
                  $horas = $data_entrada->diff($data_saida)->h;
                  $permanencia_texto = $horas . " horas";
                } else {
                  $permanencia_texto = $permanencia . " dias";
                }
              ?>
                <tr>
                  <td><?php echo $corpo['id_morto']; ?></td>
                  <td><?php echo $corpo['nome'] ?: 'Não identificado'; ?></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($corpo['data_entrada'])); ?></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($corpo['data_saida'])); ?></td>
                  <td><?php echo $permanencia_texto; ?></td>
                  <td><?php echo $corpo['origem']; ?></td>
                  <td><?php echo $corpo['destino']; ?></td>
                  <td>
                    <?php 
                    $status_class = '';
                    switch ($corpo['status']) {
                      case 'Identificado':
                        $status_class = 'identificado';
                        break;
                      case 'Não Identificado':
                        $status_class = 'nao-identificado';
                        break;
                      case 'Em Processo de Identificação':
                        $status_class = 'em-processo';
                        break;
                    }
                    ?>
                    <span class="status <?php echo $status_class; ?>"><?php echo $corpo['status']; ?></span>
                  </td>
                  <td><?php echo $corpo['responsavel_liberacao']; ?></td>
                  <td><?php echo $corpo['documento_autorizacao'] ?: 'N/A'; ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-message">
          Não há registros de saídas no sistema.
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>