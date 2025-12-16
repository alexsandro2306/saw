<?php
define('SAW_APP', true);
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/session.php';
require_once '../../includes/funcoes.php';
require_once '../../includes/sanitizacao.php';
require_once '../../includes/auth.php';

require_user();

// Estatísticas do utilizador
$user_id = get_current_user_id();
$query = "SELECT COUNT(*) as total FROM reservas WHERE utilizador_id = ?";
$result = db_query($query, 'i', [$user_id]);
$total_reservas = $result ? $result->fetch_assoc()['total'] : 0;

$query = "SELECT COUNT(*) as total FROM reservas WHERE utilizador_id = ? AND data_reserva >= CURDATE()";
$result = db_query($query, 'i', [$user_id]);
$reservas_futuras = $result ? $result->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="pt">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Dashboard - Stand Automóvel SAW</title>
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
            <h1>Bem-vindo, <?php echo escape_output($_SESSION['user_name']); ?>!</h1>

            <?php echo display_flash(); ?>

            <div class="stats-grid mt-4">
                  <div class="stat-card">
                        <div class="stat-number"><?php echo $total_reservas; ?></div>
                        <div class="stat-label">Total de Reservas</div>
                  </div>
                  <div class="stat-card">
                        <div class="stat-number"><?php echo $reservas_futuras; ?></div>
                        <div class="stat-label">Reservas Futuras</div>
                  </div>
            </div>

            <div class="grid grid-2 mt-4">
                  <div class="card">
                        <h3>🚗 Ver Veículos</h3>
                        <p>Explore nossa coleção de veículos e filtre por marca e ano.</p>
                        <a href="veiculos.php" class="btn btn-primary mt-2">Ver Veículos</a>
                  </div>
                  <div class="card">
                        <h3>📅 Minhas Reservas</h3>
                        <p>Consulte todas as suas reservas de test drive.</p>
                        <a href="minhas_reservas.php" class="btn btn-primary mt-2">Ver Reservas</a>
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