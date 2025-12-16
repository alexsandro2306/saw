<?php
/**
 * Página de Login
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
      if (is_admin()) {
            redirect(BASE_URL . '/admin/index.php');
      } else {
            redirect(BASE_URL . '/user/index.php');
      }
}

// Processar formulário
if (is_post()) {
      $email = post('email');
      $password = post('password');
      $remember_me = isset($_POST['remember_me']);

      if (empty($email) || empty($password)) {
            set_flash('error', 'Por favor, preencha todos os campos.');
      } else {
            if (login_user($email, $password, $remember_me)) {
                  // Redirecionar conforme o tipo de utilizador
                  if (is_admin()) {
                        redirect(BASE_URL . '/admin/index.php');
                  } else {
                        redirect(BASE_URL . '/user/index.php');
                  }
            }
      }
}

$page_title = 'Login';
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
                              <li><a href="registo.php">Registar</a></li>
                        </ul>
                  </nav>
            </div>
      </header>

      <main class="container py-5">
            <div class="container-small">
                  <div class="card">
                        <div class="card-header">
                              <h2>Login</h2>
                        </div>

                        <div class="card-body">
                              <?php echo display_flash(); ?>

                              <form method="POST" action="">
                                    <div class="form-group">
                                          <label for="email">Email</label>
                                          <input type="email" id="email" name="email" class="form-control"
                                                value="<?php echo escape_output(post('email')); ?>" required autofocus>
                                    </div>

                                    <div class="form-group">
                                          <label for="password">Password</label>
                                          <input type="password" id="password" name="password" class="form-control"
                                                required>
                                    </div>

                                    <div class="form-group">
                                          <div class="form-check">
                                                <input type="checkbox" id="remember_me" name="remember_me">
                                                <label for="remember_me">Lembrar-me</label>
                                          </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block">Entrar</button>
                              </form>

                              <div class="text-center mt-3">
                                    <a href="esqueci_password.php" style="color: var(--primary);">Esqueci a minha
                                          password</a>
                              </div>
                        </div>

                        <div class="card-footer text-center">
                              <p>Não tem conta? <a href="registo.php"
                                          style="color: var(--primary); font-weight: 600;">Registe-se aqui</a></p>
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