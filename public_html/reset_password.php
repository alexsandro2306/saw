<?php
/**
 * Reset Password
 */

define('SAW_APP', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/funcoes.php';
require_once '../includes/sanitizacao.php';
require_once '../includes/validacao.php';
require_once '../includes/auth.php';
require_once '../includes/email.php';

// Obter token
$token = sanitize_string(get('token'));

if (empty($token)) {
      set_flash('error', 'Token inválido.');
      redirect(BASE_URL . '/login.php');
}

// Verificar token
$token_data = verify_reset_token($token);

if (!$token_data) {
      set_flash('error', 'Token inválido ou expirado.');
      redirect(BASE_URL . '/esqueci_password.php');
}

// Processar formulário
if (is_post()) {
      $password = post('password');
      $password_confirm = post('password_confirm');

      if (empty($password) || empty($password_confirm)) {
            set_flash('error', 'Por favor, preencha todos os campos.');
      } elseif ($password !== $password_confirm) {
            set_flash('error', 'As passwords não coincidem.');
      } else {
            if (reset_password_with_token($token, $password)) {
                  redirect(BASE_URL . '/login.php');
            }
      }
}

$page_title = 'Redefinir Password';
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
                              <li><a href="login.php">Login</a></li>
                        </ul>
                  </nav>
            </div>
      </header>

      <main class="container py-5">
            <div class="container-small">
                  <div class="card">
                        <div class="card-header">
                              <h2>Redefinir Password</h2>
                        </div>

                        <div class="card-body">
                              <?php echo display_flash(); ?>

                              <p class="mb-3">Olá <strong><?php echo escape_output($token_data['nome']); ?></strong>,
                                    defina a sua nova password.</p>

                              <form method="POST" action="">
                                    <div class="form-group">
                                          <label for="password">Nova Password</label>
                                          <input type="password" id="password" name="password" class="form-control"
                                                required autofocus>
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

                                    <button type="submit" class="btn btn-primary btn-block">Redefinir Password</button>
                              </form>
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