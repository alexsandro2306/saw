<?php
define('SAW_APP', true);
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/session.php';
require_once '../../includes/funcoes.php';
require_once '../../includes/sanitizacao.php';
require_once '../../includes/auth.php';

require_user();

$user_id = get_current_user_id();

// Buscar reservas do utilizador
$query = "SELECT r.*, v.marca, v.modelo, v.foto_principal 
          FROM reservas r 
          JOIN veiculos v ON r.veiculo_id = v.id 
          WHERE r.utilizador_id = ? 
          ORDER BY r.data_reserva DESC, r.hora_reserva DESC";
$result = db_query($query, 'i', [$user_id]);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Minhas Reservas - Stand Automóvel SAW</title>
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
            <h1>Minhas Reservas</h1>

            <?php echo display_flash(); ?>

            <?php if ($result && $result->num_rows > 0): ?>
                  <div class="table-container mt-4">
                        <table>
                              <thead>
                                    <tr>
                                          <th>Veículo</th>
                                          <th>Data</th>
                                          <th>Hora</th>
                                          <th>Estado</th>
                                          <th>Observações</th>
                                    </tr>
                              </thead>
                              <tbody>
                                    <?php while ($reserva = $result->fetch_assoc()): ?>
                                          <tr>
                                                <td>
                                                      <strong><?php echo escape_output($reserva['marca'] . ' ' . $reserva['modelo']); ?></strong>
                                                </td>
                                                <td><?php echo format_date($reserva['data_reserva']); ?></td>
                                                <td><?php echo format_time($reserva['hora_reserva']); ?></td>
                                                <td>
                                                      <?php
                                                      $badge_class = 'badge-warning';
                                                      $estado_text = 'Pendente';
                                                      if ($reserva['estado'] === 'confirmada') {
                                                            $badge_class = 'badge-success';
                                                            $estado_text = 'Confirmada';
                                                      } elseif ($reserva['estado'] === 'cancelada') {
                                                            $badge_class = 'badge-danger';
                                                            $estado_text = 'Cancelada';
                                                      } elseif ($reserva['estado'] === 'concluida') {
                                                            $badge_class = 'badge-success';
                                                            $estado_text = 'Concluída';
                                                      }
                                                      ?>
                                                      <span
                                                            class="badge <?php echo $badge_class; ?>"><?php echo $estado_text; ?></span>
                                                </td>
                                                <td><?php echo escape_output($reserva['observacoes'] ?: '-'); ?></td>
                                          </tr>
                                    <?php endwhile; ?>
                              </tbody>
                        </table>
                  </div>
            <?php else: ?>
                  <div class="alert alert-info mt-4">
                        Ainda não tem reservas. <a href="veiculos.php" style="color: var(--primary); font-weight: 600;">Ver
                              veículos disponíveis</a>
                  </div>
            <?php endif; ?>
      </main>

      <footer>
            <div class="container">
                  <p>&copy; <?php echo date('Y'); ?> Stand Automóvel SAW. Todos os direitos reservados.</p>
            </div>
      </footer>
</body>

</html>