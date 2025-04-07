<?php
require_once 'conexao.php';

$mensagem = '';
$corpo = [];

// Verificar se há um ID na URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_morto = intval($_GET['id']);
    
    // Buscar dados do corpo
    $sql = "SELECT * FROM tb_recepcao WHERE id_morto = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_morto);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($resultado) > 0) {
        $corpo = mysqli_fetch_assoc($resultado);
    } else {
        $mensagem = '<div class="alerta erro">Corpo não encontrado no sistema.</div>';
    }
} else {
    // Redirecionar para lista se não houver ID
    header("Location: lista_corpos.php");
    exit;
}

// Processar o formulário de identificação
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_morto = $_POST['id_morto'];
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $identificacao = mysqli_real_escape_string($conexao, $_POST['identificacao']);
    $sexo = mysqli_real_escape_string($conexao, $_POST['sexo']);
    $idade_aproximada = !empty($_POST['idade_aproximada']) ? intval($_POST['idade_aproximada']) : null;
    $status = "Identificado"; // Sempre será identificado
    $observacao = mysqli_real_escape_string($conexao, $_POST['observacao']);
    $responsavel = mysqli_real_escape_string($conexao, $_POST['responsavel']);

    // Atualizar dados na tabela tb_recepcao
    $sql = "UPDATE tb_recepcao SET 
            nome = ?, 
            identificacao = ?, 
            sexo = ?, 
            idade_aproximada = ?, 
            status = ?, 
            observacao = CONCAT(observacao, '\n[Atualização de Identificação]: ', ?)
            WHERE id_morto = ?";
    
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 'sssissi', 
        $nome, $identificacao, $sexo, $idade_aproximada, $status, $observacao, $id_morto);
    
    if (mysqli_stmt_execute($stmt)) {
        // Registrar no histórico
        $descricao = "Identificação atualizada por $responsavel. Nome: $nome, Documento: $identificacao";
        $usuario = $responsavel;
        
        $sql_historico = "INSERT INTO tb_historico (id_morto, descricao, usuario) VALUES (?, ?, ?)";
        $stmt_hist = mysqli_prepare($conexao, $sql_historico);
        mysqli_stmt_bind_param($stmt_hist, 'iss', $id_morto, $descricao, $usuario);
        mysqli_stmt_execute($stmt_hist);
        
        $mensagem = '<div class="alerta sucesso">Identificação atualizada com sucesso!</div>';
        
        // Atualizar dados na variável para refletir na página
        $corpo['nome'] = $nome;
        $corpo['identificacao'] = $identificacao;
        $corpo['sexo'] = $sexo;
        $corpo['idade_aproximada'] = $idade_aproximada;
        $corpo['status'] = $status;
    } else {
        $mensagem = '<div class="alerta erro">Erro ao atualizar identificação: ' . mysqli_error($conexao) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Identificar Corpo - Último Suspiro</title>
  <style>
    .form-container {
      background: var(--card-bg);
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: var(--primary-color);
    }
    
    input, select, textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-family: "Cinzel", serif;
    }
    
    textarea {
      min-height: 100px;
    }
    
    .btn-submit {
      background: var(--btn-color);
      color: #fff;
      border: none;
      cursor: pointer;
      padding: 12px 20px;
      font-weight: 600;
      border-radius: 10px;
      font-family: "Cinzel", serif;
    }
    
    .btn-submit:hover {
      background: var(--btn-hover);
    }
    
    .alerta {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
    }
    
    .sucesso {
      background-color: #d4edda;
      color: #155724;
    }
    
    .erro {
      background-color: #f8d7da;
      color: #721c24;
    }
    
    .form-row {
      display: flex;
      gap: 15px;
    }

    .form-row .form-group {
      flex: 1;
    }

    .detalhes-corpo {
      background: var(--card-bg);
      padding: 15px;
      border-radius: 16px;
      margin-bottom: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .detalhes-corpo h3 {
      color: var(--primary-color);
      margin-bottom: 10px;
    }
    
    .detalhes-grupo {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 10px;
    }
    
    .detalhe-item {
      flex: 1;
      min-width: 200px;
    }
    
    .detalhe-label {
      font-weight: 600;
      color: var(--secondary-color);
      font-size: 0.9rem;
    }
    
    .detalhe-valor {
      margin-top: 5px;
    }

    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
      }
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
      <h1>Atualizar Identificação</h1>
      <p>Atualize as informações de identificação deste corpo</p>
      
      <?php echo $mensagem; ?>
      
      <?php if (!empty($corpo)): ?>
        <div class="detalhes-corpo">
          <h3>Detalhes do Registro</h3>
          <div class="detalhes-grupo">
            <div class="detalhe-item">
              <div class="detalhe-label">ID:</div>
              <div class="detalhe-valor"><?php echo $corpo['id_morto']; ?></div>
            </div>
            
            <div class="detalhe-item">
              <div class="detalhe-label">Data de Entrada:</div>
              <div class="detalhe-valor"><?php echo date('d/m/Y H:i', strtotime($corpo['data_entrada'])); ?></div>
            </div>
            
            <div class="detalhe-item">
              <div class="detalhe-label">Origem:</div>
              <div class="detalhe-valor"><?php echo $corpo['origem']; ?></div>
            </div>
            
            <div class="detalhe-item">
              <div class="detalhe-label">Status Atual:</div>
              <div class="detalhe-valor"><?php echo $corpo['status']; ?></div>
            </div>
          </div>
        </div>
        
        <div class="form-container">
          <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $corpo['id_morto']); ?>">
            <input type="hidden" name="id_morto" value="<?php echo $corpo['id_morto']; ?>">
            
            <div class="form-row">
              <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($corpo['nome'] ?? ''); ?>" required>
              </div>
              
              <div class="form-group">
                <label for="identificacao">Identificação (RG/CPF/Outro):</label>
                <input type="text" id="identificacao" name="identificacao" value="<?php echo htmlspecialchars($corpo['identificacao'] ?? ''); ?>" required>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="sexo">Sexo:</label>
                <select id="sexo" name="sexo" required>
                  <option value="">Selecione</option>
                  <option value="M" <?php echo ($corpo['sexo'] == 'M') ? 'selected' : ''; ?>>Masculino</option>
                  <option value="F" <?php echo ($corpo['sexo'] == 'F') ? 'selected' : ''; ?>>Feminino</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="idade_aproximada">Idade:</label>
                <input type="number" min="0" max="120" id="idade_aproximada" name="idade_aproximada" value="<?php echo $corpo['idade_aproximada'] ?? ''; ?>">
              </div>
            </div>
            
            <div class="form-group">
              <label for="responsavel">Responsável pela Identificação:</label>
              <input type="text" id="responsavel" name="responsavel" required>
            </div>
            
            <div class="form-group">
              <label for="observacao">Observações sobre a Identificação:</label>
              <textarea id="observacao" name="observacao" placeholder="Detalhes sobre como o corpo foi identificado, documentos apresentados, familiares que o reconheceram, etc."></textarea>
            </div>
            
            <div class="form-group">
              <button type="submit" class="btn-submit">Atualizar Identificação</button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>