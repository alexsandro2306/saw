<?php
/**
 * Esqueci Password
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

// Processar formulário
if (is_post()) {
      $email = sanitize_email(post('email'));

      if (empty($email)) {
            set_flash('error', 'Por favor, introduza o seu email.');
      } elseif (!validate_email($email)) {
            set_flash('error', 'Email inválido.');
      } else {
            // Enviar email (sempre retorna true para não revelar se o email existe)
            send_password_reset_email($email);
            set_flash('success', 'Se o email existir na nossa base de dados, receberá instruções para recuperar a password.');
            redirect(BASE_URL . '/login.php');
      }
}

$page_title = 'Recuperar Password';
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
                              <h2>Recuperar Password</h2>
                        </div>

                        <div class="card-body">
                              <?php echo display_flash(); ?>

                              <p class="mb-3">Introduza o seu email para receber instruções de recuperação de password.
                              </p>

                              <form method="POST" action="">
                                    <div class="form-group">
                                          <label for="email">Email</label>
                                          <input type="email" id="email" name="email" class="form-control"
                                                value="<?php echo escape_output(post('email')); ?>" required autofocus>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block">Enviar Instruções</button>
                              </form>
                        </div>

                        <div class="card-footer text-center">
                              <p><a href="login.php" style="color: var(--primary);">Voltar ao Login</a></p>
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