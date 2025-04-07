<?php
require_once 'conexao.php';

// Buscar corpos ativos (sem registro de saída)
$sql = "SELECT r.id_morto, r.nome, r.origem, r.data_entrada, r.status, r.identificacao, r.observacao, 
               r.sexo, r.idade_aproximada, c.id_camara, c.status_camara, c.temperatura
        FROM tb_recepcao r
        LEFT JOIN tb_saida s ON r.id_morto = s.id_morto
        LEFT JOIN tb_camara c ON r.id_camara = c.id_camara
        WHERE s.id_morto IS NULL
        ORDER BY r.data_entrada DESC";
$resultado = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Lista de Corpos - Último Suspiro</title>
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
    
    .btn-acao {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 5px;
      text-decoration: none;
      margin-right: 5px;
      font-size: 0.85rem;
    }
    
    .btn-info {
      background-color: var(--accent-color);
      color: var(--light-color);
    }
    
    .btn-liberar {
      background-color: var(--btn-color);
      color: var(--light-color);
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
      </ul>
    </nav>
  </header>
  <main class="main">
    <section>
      <h1>Lista de Corpos Ativos</h1>
      <p>Corpos atualmente armazenados no necrotério</p>
      
      <?php if (mysqli_num_rows($resultado) > 0): ?>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Data Entrada</th>
                <th>Origem</th>
                <th>Status</th>
                <th>Identificação</th>
                <th>Câmara</th>
                <th>Info</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($corpo = mysqli_fetch_assoc($resultado)): ?>
                <tr>
                  <td><?php echo $corpo['id_morto']; ?></td>
                  <td><?php echo $corpo['nome'] ?: 'Não identificado'; ?></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($corpo['data_entrada'])); ?></td>
                  <td><?php echo $corpo['origem']; ?></td>
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
                  <td><?php echo $corpo['identificacao'] ?: 'N/A'; ?></td>
                  <td>
                    Câmara <?php echo $corpo['id_camara']; ?>
                    <?php if ($corpo['temperatura']): ?>
                      (<?php echo $corpo['temperatura']; ?>°C)
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($corpo['sexo'] || $corpo['idade_aproximada']): ?>
                      <?php echo $corpo['sexo'] == 'M' ? 'Masculino' : ($corpo['sexo'] == 'F' ? 'Feminino' : ''); ?>
                      <?php echo $corpo['idade_aproximada'] ? ', ' . $corpo['idade_aproximada'] . ' anos' : ''; ?>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="saida.php?id=<?php echo $corpo['id_morto']; ?>" class="btn-acao btn-liberar">Liberar</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-message">
          Não há corpos cadastrados no sistema.
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>