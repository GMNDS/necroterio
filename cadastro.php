<?php
require_once 'verificar_sessao.php';
require_once 'conexao.php';

$mensagem = '';

$sql_camaras = "SELECT * FROM tb_camara";
$resultado_camaras = mysqli_query($conexao, $sql_camaras);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $origem = mysqli_real_escape_string($conexao, $_POST['origem']);
    $data_entrada = $_POST['data_entrada'];
    $status = mysqli_real_escape_string($conexao, $_POST['status']);
    $identificacao = mysqli_real_escape_string($conexao, $_POST['identificacao']);
    $id_camara = $_POST['id_camara'];
    $observacao = mysqli_real_escape_string($conexao, $_POST['observacao']);
    $sexo = isset($_POST['sexo']) ? mysqli_real_escape_string($conexao, $_POST['sexo']) : null;
    $idade_aproximada = isset($_POST['idade_aproximada']) ? $_POST['idade_aproximada'] : null;
    $causa_presumida = isset($_POST['causa_presumida']) ? mysqli_real_escape_string($conexao, $_POST['causa_presumida']) : null;
    $responsavel = mysqli_real_escape_string($conexao, $_POST['responsavel_recepcao']);

    // Verificar se o CPF já existe no banco de dados (se foi fornecido)
    $cpf_duplicado = false;
    if (!empty($identificacao)) {
        $sql_verificar_cpf = "SELECT COUNT(*) as total FROM tb_recepcao WHERE identificacao = ?";
        $stmt_verificar = mysqli_prepare($conexao, $sql_verificar_cpf);
        mysqli_stmt_bind_param($stmt_verificar, 's', $identificacao);
        mysqli_stmt_execute($stmt_verificar);
        $resultado_verificacao = mysqli_stmt_get_result($stmt_verificar);
        $dados_verificacao = mysqli_fetch_assoc($resultado_verificacao);
        
        if ($dados_verificacao['total'] > 0) {
            $cpf_duplicado = true;
            $mensagem = '<div class="alerta erro">Este CPF já está cadastrado no sistema. Não é possível cadastrar o mesmo CPF duas vezes.</div>';
        }
    }

    // Só prossegue com a inserção se não for um CPF duplicado
    if (!$cpf_duplicado) {
        // Inserir na tabela tb_recepcao (tabela unificada conforme novo esquema)
        $sql = "INSERT INTO tb_recepcao (nome, origem, data_entrada, status, identificacao, id_camara, 
                observacao, sexo, idade_aproximada, causa_presumida, responsavel_recepcao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, 'sssssississ', 
        $nome, $origem, $data_entrada, $status, $identificacao, 
        $id_camara, $observacao, $sexo, $idade_aproximada, $causa_presumida, $responsavel);
        
        if (mysqli_stmt_execute($stmt)) {
            $id_morto = mysqli_insert_id($conexao);
            
            // Registrar no histórico
            $descricao = "Entrada registrada";
            $usuario = $_SESSION['nome_usuario']; 
            
            $sql_historico = "INSERT INTO tb_historico (id_morto, descricao, usuario) VALUES (?, ?, ?)";
            $stmt_hist = mysqli_prepare($conexao, $sql_historico);
            mysqli_stmt_bind_param($stmt_hist, 'iss', $id_morto, $descricao, $usuario);
            mysqli_stmt_execute($stmt_hist);
            
            $mensagem = '<div class="alerta sucesso">Corpo cadastrado com sucesso!</div>';
        } else {
            $mensagem = '<div class="alerta erro">Erro ao cadastrar: ' . mysqli_error($conexao) . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Cadastro de Entrada - Último Suspiro</title>
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
      <h1>Cadastro de Entrada</h1>
      <p>Preencha os dados para registrar a entrada de um novo corpo</p>
      
      <?php echo $mensagem; ?>
      
      <div class="form-container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <div class="form-row">
            <div class="form-group">
              <label for="nome">Nome do Falecido:</label>
              <input type="text" id="nome" name="nome" placeholder="Desconhecido, se não for identificado">
            </div>
            
            <div class="form-group">
              <label for="origem">Origem:</label>
              <input type="text" id="origem" name="origem" placeholder="IML, Hospital, Via pública, etc." required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="data_entrada">Data de Entrada:</label>
              <input type="datetime-local" id="data_entrada" name="data_entrada" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
            </div>
            
            <div class="form-group">
              <label for="status">Status:</label>
              <select id="status" name="status" required>
                <option value="Identificado">Identificado</option>
                <option value="Não Identificado">Não Identificado</option>
                <option value="Em Processo de Identificação">Em Processo de Identificação</option>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="identificacao">Identificação (CPF):</label>
              <input type="text" id="identificacao" name="identificacao" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" placeholder="Formato: 123.456.789-00">
            </div>
            
            <div class="form-group">
              <label for="id_camara">Câmara Frigorífica:</label>
              <select id="id_camara" name="id_camara" required>
                <?php
                while ($camara = mysqli_fetch_assoc($resultado_camaras)) {
                  echo "<option value='" . $camara['id_camara'] . "'>Câmara " . $camara['id_camara'] . " - " . $camara['status_camara'] . " (Temperatura: " . ($camara['temperatura'] ?? 'N/A') . "°C)</option>";
                }
                ?>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="sexo">Sexo:</label>
              <select id="sexo" name="sexo">
                <option value="">Selecione</option>
                <option value="M">Masculino</option>
                <option value="F">Feminino</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="idade_aproximada">Idade Aproximada:</label>
              <input type="number" min="0" max="120" id="idade_aproximada" name="idade_aproximada">
            </div>
          </div>
          
          <div class="form-group">
            <label for="causa_presumida">Causa Presumida da Morte:</label>
            <input type="text" id="causa_presumida" name="causa_presumida">
          </div>
          
          <div class="form-group">
            <label for="responsavel_recepcao">Responsável pela Recepção:</label>
            <input type="text" id="responsavel_recepcao" name="responsavel_recepcao" value="<?php echo htmlspecialchars($_SESSION['nome_usuario']); ?>" required>
          </div>
          
          <div class="form-group">
            <label for="observacao">Observações:</label>
            <textarea id="observacao" name="observacao"></textarea>
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn-submit">Cadastrar Entrada</button>
          </div>
        </form>
      </div>
    </section>
  </main>
</body>
</html>