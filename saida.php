<?php
require_once 'conexao.php';

$mensagem = '';

// Buscar corpos ativos (sem registro de saída)
$sql_corpos = "SELECT r.id_morto, r.nome, r.identificacao 
               FROM tb_recepcao r 
               LEFT JOIN tb_saida s ON r.id_morto = s.id_morto
               WHERE s.id_morto IS NULL";
$resultado_corpos = mysqli_query($conexao, $sql_corpos);

// Processar o formulário de saída
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_morto = $_POST['id_morto'];
    $data_saida = $_POST['data_saida'];
    $destino = mysqli_real_escape_string($conexao, $_POST['destino']);
    $responsavel_liberacao = mysqli_real_escape_string($conexao, $_POST['responsavel_liberacao']);
    $documento_autorizacao = mysqli_real_escape_string($conexao, $_POST['documento_autorizacao'] ?? '');
    $observacao = mysqli_real_escape_string($conexao, $_POST['observacao'] ?? '');

    // Inserir na tabela tb_saida usando prepared statement
    $sql = "INSERT INTO tb_saida (id_morto, data_saida, destino, responsavel_liberacao, documento_autorizacao, observacao) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 'isssss', $id_morto, $data_saida, $destino, $responsavel_liberacao, 
                          $documento_autorizacao, $observacao);
    
    if (mysqli_stmt_execute($stmt)) {
        // Registrar no histórico
        $descricao = "Saída registrada para: " . $destino;
        $usuario = "Sistema"; 
        
        $sql_historico = "INSERT INTO tb_historico (id_morto, descricao, usuario) VALUES (?, ?, ?)";
        $stmt_hist = mysqli_prepare($conexao, $sql_historico);
        mysqli_stmt_bind_param($stmt_hist, 'iss', $id_morto, $descricao, $usuario);
        mysqli_stmt_execute($stmt_hist);
        
        $mensagem = '<div class="alerta sucesso">Saída registrada com sucesso!</div>';
    } else {
        $mensagem = '<div class="alerta erro">Erro ao registrar saída: ' . mysqli_error($conexao) . '</div>';
    }
}

// Verificar se há um ID na URL para pré-selecionar
$id_pre_selecionado = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Registro de Saída - Último Suspiro</title>
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
      <h1>Registro de Saída</h1>
      <p>Preencha os dados para registrar a saída de um corpo</p>
      
      <?php echo $mensagem; ?>
      
      <div class="form-container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <div class="form-group">
            <label for="id_morto">Selecionar Corpo:</label>
            <select id="id_morto" name="id_morto" required>
              <option value="">Selecione um corpo</option>
              <?php
              if (mysqli_num_rows($resultado_corpos) > 0) {
                while ($corpo = mysqli_fetch_assoc($resultado_corpos)) {
                  $identificacao = !empty($corpo['identificacao']) ? " - " . $corpo['identificacao'] : "";
                  $selected = ($corpo['id_morto'] == $id_pre_selecionado) ? 'selected' : '';
                  echo "<option value='" . $corpo['id_morto'] . "' $selected>" . $corpo['id_morto'] . " - " . $corpo['nome'] . $identificacao . "</option>";
                }
              } else {
                echo "<option disabled>Nenhum corpo disponível para saída</option>";
              }
              ?>
            </select>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="data_saida">Data de Saída:</label>
              <input type="datetime-local" id="data_saida" name="data_saida" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
            </div>
            
            <div class="form-group">
              <label for="destino">Destino:</label>
              <input type="text" id="destino" name="destino" placeholder="Família, Cemitério, Instituto Médico, etc." required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="responsavel_liberacao">Responsável pela Liberação:</label>
              <input type="text" id="responsavel_liberacao" name="responsavel_liberacao" required>
            </div>
            
            <div class="form-group">
              <label for="documento_autorizacao">Documento de Autorização:</label>
              <input type="text" id="documento_autorizacao" name="documento_autorizacao" placeholder="N° do documento, autoridade emissor, etc.">
            </div>
          </div>
          
          <div class="form-group">
            <label for="observacao">Observações:</label>
            <textarea id="observacao" name="observacao"></textarea>
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn-submit">Registrar Saída</button>
          </div>
        </form>
      </div>
    </section>
  </main>
</body>
</html>