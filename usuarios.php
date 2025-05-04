<?php
session_start();
require_once 'conexao.php';

if(!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

if($_SESSION['nivel_usuario'] != 'admin') {
    header("Location: index.php");
    exit;
}

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'adicionar' || $_POST['acao'] == 'editar') {
        $id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
        $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
        $login = mysqli_real_escape_string($conexao, $_POST['login']);
        $email = mysqli_real_escape_string($conexao, $_POST['email']);
        $nivel = mysqli_real_escape_string($conexao, $_POST['nivel']);
        $senha = isset($_POST['senha']) && !empty($_POST['senha']) ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : '';
        
        // Verificar se o login já existe (exceto para o usuário atual, caso seja edição)
        $sql_verificar = "SELECT COUNT(*) as total FROM tb_usuarios WHERE login = ? AND id_usuario != ?";
        $stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
        mysqli_stmt_bind_param($stmt_verificar, 'si', $login, $id_usuario);
        mysqli_stmt_execute($stmt_verificar);
        $resultado_verificacao = mysqli_stmt_get_result($stmt_verificar);
        $dados_verificacao = mysqli_fetch_assoc($resultado_verificacao);
        
        if ($dados_verificacao['total'] > 0) {
            $mensagem = '<div class="alerta erro">Este nome de usuário já está sendo utilizado. Escolha outro.</div>';
        } else {
            // Adicionar novo usuário
            if ($_POST['acao'] == 'adicionar') {
                if (empty($senha)) {
                    $mensagem = '<div class="alerta erro">Senha é obrigatória para novos usuários.</div>';
                } else {
                    $sql = "INSERT INTO tb_usuarios (nome, login, senha, nivel, email) VALUES (?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conexao, $sql);
                    mysqli_stmt_bind_param($stmt, 'sssss', $nome, $login, $senha, $nivel, $email);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $mensagem = '<div class="alerta sucesso">Usuário cadastrado com sucesso!</div>';
                    } else {
                        $mensagem = '<div class="alerta erro">Erro ao cadastrar usuário: ' . mysqli_error($conexao) . '</div>';
                    }
                }
            } 
            // Editar usuário existente
            else {
                if (!empty($senha)) {
                    $sql = "UPDATE tb_usuarios SET nome = ?, login = ?, senha = ?, nivel = ?, email = ? WHERE id_usuario = ?";
                    $stmt = mysqli_prepare($conexao, $sql);
                    mysqli_stmt_bind_param($stmt, 'sssssi', $nome, $login, $senha, $nivel, $email, $id_usuario);
                } else {
                    $sql = "UPDATE tb_usuarios SET nome = ?, login = ?, nivel = ?, email = ? WHERE id_usuario = ?";
                    $stmt = mysqli_prepare($conexao, $sql);
                    mysqli_stmt_bind_param($stmt, 'ssssi', $nome, $login, $nivel, $email, $id_usuario);
                }
                
                if (mysqli_stmt_execute($stmt)) {
                    $mensagem = '<div class="alerta sucesso">Usuário atualizado com sucesso!</div>';
                } else {
                    $mensagem = '<div class="alerta erro">Erro ao atualizar usuário: ' . mysqli_error($conexao) . '</div>';
                }
            }
        }
    }
    else if ($_POST['acao'] == 'alternar_status') {
        $id_usuario = intval($_POST['id_usuario']);
        $status_atual = intval($_POST['status_atual']);
        $novo_status = $status_atual ? 0 : 1;
        
        if ($id_usuario == $_SESSION['id_usuario']) {
            $mensagem = '<div class="alerta erro">Você não pode desativar seu próprio usuário.</div>';
        } 
        else if ($id_usuario == 1) {
            $mensagem = '<div class="alerta erro">O usuário administrador principal não pode ser desativado.</div>';
        }
        else {
            $sql = "UPDATE tb_usuarios SET ativo = ? WHERE id_usuario = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, 'ii', $novo_status, $id_usuario);
            
            if (mysqli_stmt_execute($stmt)) {
                $status_texto = $novo_status ? "ativado" : "desativado";
                $mensagem = '<div class="alerta sucesso">Usuário ' . $status_texto . ' com sucesso!</div>';
            } else {
                $mensagem = '<div class="alerta erro">Erro ao alterar status do usuário: ' . mysqli_error($conexao) . '</div>';
            }
        }
    }
}

$usuario_edicao = null;
if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    
    $sql_editar = "SELECT * FROM tb_usuarios WHERE id_usuario = ?";
    $stmt_editar = mysqli_prepare($conexao, $sql_editar);
    mysqli_stmt_bind_param($stmt_editar, 'i', $id_editar);
    mysqli_stmt_execute($stmt_editar);
    $resultado_editar = mysqli_stmt_get_result($stmt_editar);
    
    if (mysqli_num_rows($resultado_editar) > 0) {
        $usuario_edicao = mysqli_fetch_assoc($resultado_editar);
    }
}

$sql_usuarios = "SELECT * FROM tb_usuarios ORDER BY nome";
$resultado_usuarios = mysqli_query($conexao, $sql_usuarios);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Gerenciar Usuários - Último Suspiro</title>
  <style>
    .form-container, .table-container {
      background: var(--card-bg);
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
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
    
    input, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-family: "Cinzel", serif;
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
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    
    th {
      background-color: var(--primary-color);
      color: var(--light-color);
    }
    
    tr:nth-child(even) {
      background-color: #f2f2f2;
    }
    
    tr:hover {
      background-color: var(--hover-color);
    }
    
    .btn-acao {
      display: inline-block;
      margin-right: 5px;
      padding: 5px 10px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 0.85rem;
      color: white;
      cursor: pointer;
    }
    
    .btn-editar {
      background-color: #4b77be;
    }
    
    .btn-ativar {
      background-color: #28a745;
    }
    
    .btn-desativar {
      background-color: #dc3545;
    }
    
    .usuario-inativo {
      opacity: 0.6;
    }
    
    .form-row {
      display: flex;
      gap: 15px;
    }

    .form-row .form-group {
      flex: 1;
    }
    
    .senha-info {
      font-size: 0.85rem;
      color: var(--accent-color);
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
        <li> <a href="usuarios.php">Gerenciar Usuários</a></li>
        <li> <a href="logout.php">Sair do Sistema</a></li>
      </ul>
    </nav>
  </header>
  <main class="main">
    <section>
      <h1><?php echo isset($usuario_edicao) ? 'Editar Usuário' : 'Adicionar Novo Usuário'; ?></h1>
      <p><?php echo isset($usuario_edicao) ? 'Altere os dados do usuário selecionado' : 'Preencha os dados para criar um novo usuário'; ?></p>
      
      <?php echo $mensagem; ?>
      
      <div class="form-container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <input type="hidden" name="acao" value="<?php echo isset($usuario_edicao) ? 'editar' : 'adicionar'; ?>">
          <?php if (isset($usuario_edicao)): ?>
            <input type="hidden" name="id_usuario" value="<?php echo $usuario_edicao['id_usuario']; ?>">
          <?php endif; ?>
          
          <div class="form-row">
            <div class="form-group">
              <label for="nome">Nome Completo:</label>
              <input type="text" id="nome" name="nome" value="<?php echo isset($usuario_edicao) ? htmlspecialchars($usuario_edicao['nome']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
              <label for="login">Login (Usuário):</label>
              <input type="text" id="login" name="login" value="<?php echo isset($usuario_edicao) ? htmlspecialchars($usuario_edicao['login']) : ''; ?>" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="email">Email:</label>
              <input type="email" id="email" name="email" value="<?php echo isset($usuario_edicao) ? htmlspecialchars($usuario_edicao['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
              <label for="nivel">Nível de Acesso:</label>
              <select id="nivel" name="nivel" required>
                <option value="funcionario" <?php echo (isset($usuario_edicao) && $usuario_edicao['nivel'] == 'funcionario') ? 'selected' : ''; ?>>Funcionário</option>
                <option value="admin" <?php echo (isset($usuario_edicao) && $usuario_edicao['nivel'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" <?php echo !isset($usuario_edicao) ? 'required' : ''; ?>>
            <?php if (isset($usuario_edicao)): ?>
              <p class="senha-info">Deixe em branco para manter a senha atual.</p>
            <?php endif; ?>
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn-submit">
              <?php echo isset($usuario_edicao) ? 'Atualizar Usuário' : 'Adicionar Usuário'; ?>
            </button>
            <?php if (isset($usuario_edicao)): ?>
              <a href="usuarios.php" class="btn-submit" style="text-decoration: none; display: inline-block; margin-left: 10px;">Cancelar</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </section>
    
    <section>
      <h2>Usuários Cadastrados</h2>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nome</th>
              <th>Login</th>
              <th>Email</th>
              <th>Nível</th>
              <th>Status</th>
              <th>Cadastro</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($resultado_usuarios) > 0): ?>
              <?php while ($usuario = mysqli_fetch_assoc($resultado_usuarios)): ?>
                <tr class="<?php echo $usuario['ativo'] ? '' : 'usuario-inativo'; ?>">
                  <td><?php echo $usuario['id_usuario']; ?></td>
                  <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                  <td><?php echo htmlspecialchars($usuario['login']); ?></td>
                  <td><?php echo htmlspecialchars($usuario['email'] ?: '-'); ?></td>
                  <td><?php echo $usuario['nivel'] == 'admin' ? 'Administrador' : 'Funcionário'; ?></td>
                  <td><?php echo $usuario['ativo'] ? 'Ativo' : 'Inativo'; ?></td>
                  <td><?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></td>
                  <td>
                    <a href="?editar=<?php echo $usuario['id_usuario']; ?>" class="btn-acao btn-editar">Editar</a>
                    
                    <?php if ($usuario['id_usuario'] != $_SESSION['id_usuario'] && $usuario['id_usuario'] != 1): ?>
                      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: inline;">
                        <input type="hidden" name="acao" value="alternar_status">
                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                        <input type="hidden" name="status_atual" value="<?php echo $usuario['ativo']; ?>">
                        <button type="submit" class="btn-acao <?php echo $usuario['ativo'] ? 'btn-desativar' : 'btn-ativar'; ?>">
                          <?php echo $usuario['ativo'] ? 'Desativar' : 'Ativar'; ?>
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8">Nenhum usuário cadastrado.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>