<?php
define('SAW_APP', true);
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/session.php';
require_once '../../includes/funcoes.php';
require_once '../../includes/auth.php';

require_admin();

// Estatísticas
$query = "SELECT COUNT(*) as total FROM utilizadores WHERE tipo = 'user'";
$total_users = db_query($query)->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM veiculos";
$total_veiculos = db_query($query)->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM reservas";
$total_reservas = db_query($query)->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM veiculos WHERE estado = 'disponivel'";
$veiculos_disponiveis = db_query($query)->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="pt">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Administração - Stand Automóvel SAW</title>
      <link rel="stylesheet" href="../css/style.css">
</head>

<body>
      <header>
            <div class="container">
                  <a href="../index.php" class="logo">🚗 Stand Automóvel SAW - Admin</a>
                  <nav>
                        <ul>
                              <li><a href="index.php">Dashboard</a></li>
                              <li><a href="utilizadores.php">Utilizadores</a></li>
                              <li><a href="veiculos.php">Veículos</a></li>
                              <li><a href="reservas.php">Reservas</a></li>
                              <li><a href="perfil.php">Perfil</a></li>
                              <li><a href="../logout.php">Sair</a></li>
                        </ul>
                  </nav>
            </div>
      </header>

      <main class="container py-4">
            <h1>Painel de Administração</h1>

            <?php echo display_flash(); ?>

            <div class="stats-grid mt-4">
                  <div class="stat-card">
                        <div class="stat-number"><?php echo $total_users; ?></div>
                        <div class="stat-label">Utilizadores</div>
                  </div>
                  <div class="stat-card">
                        <div class="stat-number"><?php echo $total_veiculos; ?></div>
                        <div class="stat-label">Veículos</div>
                  </div>
                  <div class="stat-card">
                        <div class="stat-number"><?php echo $veiculos_disponiveis; ?></div>
                        <div class="stat-label">Disponíveis</div>
                  </div>
                  <div class="stat-card">
                        <div class="stat-number"><?php echo $total_reservas; ?></div>
                        <div class="stat-label">Reservas</div>
                  </div>
            </div>

            <div class="grid grid-3 mt-4">
                  <div class="card">
                        <h3>👥 Utilizadores</h3>
                        <p>Gerir utilizadores registados no sistema.</p>
                        <a href="utilizadores.php" class="btn btn-primary mt-2">Ver Utilizadores</a>
                  </div>
                  <div class="card">
                        <h3>🚗 Veículos</h3>
                        <p>Adicionar, editar e eliminar veículos.</p>
                        <a href="veiculos.php" class="btn btn-primary mt-2">Gerir Veículos</a>
                  </div>
                  <div class="card">
                        <h3>📅 Reservas</h3>
                        <p>Consultar todas as reservas de test drive.</p>
                        <a href="reservas.php" class="btn btn-primary mt-2">Ver Reservas</a>
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