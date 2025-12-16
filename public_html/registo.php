<?php
/**
 * Página de Registo
 */

define('SAW_APP', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/funcoes.php';
require_once '../includes/sanitizacao.php';
require_once '../includes/validacao.php';
require_once '../includes/auth.php';

// Se já está autenticado, redirecionar
if (is_logged_in()) {
      redirect(BASE_URL . '/index.php');
}

// Processar formulário
if (is_post()) {
      $nome = post('nome');
      $email = post('email');
      $password = post('password');
      $password_confirm = post('password_confirm');

      // Validar
      $errors = [];

      if (empty($nome) || empty($email) || empty($password) || empty($password_confirm)) {
            $errors[] = "Todos os campos são obrigatórios.";
      }

      if ($password !== $password_confirm) {
            $errors[] = "As passwords não coincidem.";
      }

      if (empty($errors)) {
            $user_id = register_user($nome, $email, $password);

            if ($user_id) {
                  redirect(BASE_URL . '/login.php');
            }
      } else {
            foreach ($errors as $error) {
                  set_flash('error', $error);
            }
      }
}

$page_title = 'Registo';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?php echo $page_title; ?> - Stand Automóvel SAW</title>
      <link rel="stylesheet" href="css/style.css">
</head>

<body>
      <header>
            <div class="container">
                  <a href="index.php" class="logo">
                        🚗 Stand Automóvel SAW
                  </a>
                  <nav>
                        <ul>
                              <li><a href="index.php">Início</a></li>
                              <li><a href="veiculos_publico.php">Veículos</a></li>
                              <li><a href="login.php">Login</a></li>
                        </ul>
                  </nav>
            </div>
      </header>

      <main class="container py-5">
            <div class="container-small">
                  <div class="card">
                        <div class="card-header">
                              <h2>Criar Conta</h2>
                        </div>

                        <div class="card-body">
                              <?php echo display_flash(); ?>

                              <form method="POST" action="">
                                    <div class="form-group">
                                          <label for="nome">Nome Completo</label>
                                          <input type="text" id="nome" name="nome" class="form-control"
                                                value="<?php echo escape_output(post('nome')); ?>" required>
                                    </div>

                                    <div class="form-group">
                                          <label for="email">Email</label>
                                          <input type="email" id="email" name="email" class="form-control"
                                                value="<?php echo escape_output(post('email')); ?>" required>
                                    </div>

                                    <div class="form-group">
                                          <label for="password">Password</label>
                                          <input type="password" id="password" name="password" class="form-control"
                                                required>
                                          <small style="color: var(--gray); font-size: 0.875rem;">
                                                Mínimo 8 caracteres, incluindo maiúsculas, minúsculas, números e
                                                caracteres especiais.
                                          </small>
                                    </div>

                                    <div class="form-group">
                                          <label for="password_confirm">Confirmar Password</label>
                                          <input type="password" id="password_confirm" name="password_confirm"
                                                class="form-control" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block">Registar</button>
                              </form>
                        </div>

                        <div class="card-footer text-center">
                              <p>Já tem conta? <a href="login.php" style="color: var(--primary); font-weight: 600;">Faça
                                          login</a></p>
                        </div>
                  </div>
            </div>
      </main>

      <footer>
            <div class="container">
                  <p>&copy; <?php echo date('Y'); ?> Stand Automóvel SAW. Todos os direitos reservados.</p>
            </div>
      </footer>
</body>

</html>