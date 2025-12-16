<?php
define('SAW_APP', true);
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/session.php';
require_once '../../includes/funcoes.php';
require_once '../../includes/sanitizacao.php';
require_once '../../includes/validacao.php';
require_once '../../includes/auth.php';

require_user();

$user = get_current_user_data();

// Processar formulário
if (is_post()) {
      $nome = sanitize_string(post('nome'));
      $email = sanitize_email(post('email'));
      $password = post('password');

      $errors = [];

      if (!validate_name($nome)) {
            $errors[] = "Nome inválido.";
      }

      if (!validate_email($email)) {
            $errors[] = "Email inválido.";
      }

      if (email_exists($email, $user['id'])) {
            $errors[] = "Este email já está em uso.";
      }

      // Upload de foto
      $foto_perfil = $user['foto_perfil'];
      if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $uploaded = upload_image($_FILES['foto_perfil'], IMAGES_PATH . '/perfis', 'perfil_');
            if ($uploaded) {
                  // Eliminar foto antiga
                  if ($foto_perfil) {
                        delete_image(IMAGES_PATH . '/perfis/' . $foto_perfil);
                  }
                  $foto_perfil = $uploaded;
            }
      }

      if (empty($errors)) {
            $query = "UPDATE utilizadores SET nome = ?, email = ?, foto_perfil = ? WHERE id = ?";
            $params = [$nome, $email, $foto_perfil, $user['id']];

            // Se alterar password
            if (!empty($password)) {
                  if (!validate_password($password, $errors)) {
                        foreach ($errors as $error) {
                              set_flash('error', $error);
                        }
                  } else {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $query = "UPDATE utilizadores SET nome = ?, email = ?, foto_perfil = ?, password = ? WHERE id = ?";
                        $params = [$nome, $email, $foto_perfil, $password_hash, $user['id']];
                  }
            }

            if (empty($errors)) {
                  if (db_execute($query, str_repeat('s', count($params) - 1) . 'i', $params)) {
                        $_SESSION['user_name'] = $nome;
                        $_SESSION['user_email'] = $email;
                        set_flash('success', 'Perfil atualizado com sucesso!');
                        redirect(BASE_URL . '/user/perfil.php');
                  } else {
                        set_flash('error', 'Erro ao atualizar perfil.');
                  }
            }
      } else {
            foreach ($errors as $error) {
                  set_flash('error', $error);
            }
      }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Meu Perfil - Stand Automóvel SAW</title>
      <link rel="stylesheet" href="../css/style.css">
</head>

<body>
      <header>
            <div class="container">
                  <a href="../index.php" class="logo">🚗 Stand Automóvel SAW</a>
                  <nav>
                        <ul>
                              <li><a href="index.php">Dashboard</a></li>
                              <li><a href="veiculos.php">Veículos</a></li>
                              <li><a href="minhas_reservas.php">Minhas Reservas</a></li>
                              <li><a href="perfil.php">Perfil</a></li>
                              <li><a href="../logout.php">Sair</a></li>
                        </ul>
                  </nav>
            </div>
      </header>

      <main class="container py-4">
            <h1>Meu Perfil</h1>

            <?php echo display_flash(); ?>

            <div class="card mt-4">
                  <form method="POST" enctype="multipart/form-data">
                        <div class="profile-header">
                              <?php if ($user['foto_perfil']): ?>
                                    <img src="<?php echo IMAGES_URL . '/perfis/' . escape_output($user['foto_perfil']); ?>"
                                          alt="Foto de perfil" class="profile-avatar">
                              <?php else: ?>
                                    <div class="profile-avatar"
                                          style="background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white;">
                                          👤
                                    </div>
                              <?php endif; ?>

                              <div class="profile-info">
                                    <h2><?php echo escape_output($user['nome']); ?></h2>
                                    <p><?php echo escape_output($user['email']); ?></p>
                              </div>
                        </div>

                        <div class="form-group">
                              <label for="nome">Nome Completo</label>
                              <input type="text" id="nome" name="nome" class="form-control"
                                    value="<?php echo escape_output($user['nome']); ?>" required>
                        </div>

                        <div class="form-group">
                              <label for="email">Email</label>
                              <input type="email" id="email" name="email" class="form-control"
                                    value="<?php echo escape_output($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                              <label for="foto_perfil">Foto de Perfil</label>
                              <input type="file" id="foto_perfil" name="foto_perfil" class="form-control"
                                    accept="image/*">
                              <small style="color: var(--gray); font-size: 0.875rem;">Deixe em branco para manter a foto
                                    atual.</small>
                        </div>

                        <div class="form-group">
                              <label for="password">Nova Password (opcional)</label>
                              <input type="password" id="password" name="password" class="form-control">
                              <small style="color: var(--gray); font-size: 0.875rem;">Deixe em branco para manter a
                                    password atual.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Atualizar Perfil</button>
                  </form>
            </div>
      </main>

      <footer>
            <div class="container">
                  <p>&copy; <?php echo date('Y'); ?> Stand Automóvel SAW. Todos os direitos reservados.</p>
            </div>
      </footer>
</body>

</html>