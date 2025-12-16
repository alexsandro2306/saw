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

$veiculo_id = sanitize_int(get('veiculo', 0));
$user_id = get_current_user_id();

// Buscar veículo
$query = "SELECT * FROM veiculos WHERE id = ? AND estado = 'disponivel'";
$result = db_query($query, 'i', [$veiculo_id]);

if (!$result || $result->num_rows === 0) {
      set_flash('error', 'Veículo não encontrado ou indisponível.');
      redirect(BASE_URL . '/user/veiculos.php');
}

$veiculo = $result->fetch_assoc();

// Processar formulário
if (is_post()) {
      $data_reserva = sanitize_date(post('data_reserva'));
      $hora_reserva = sanitize_time(post('hora_reserva'));
      $observacoes = sanitize_string(post('observacoes'));

      $errors = [];

      if (!$data_reserva || !validate_future_date($data_reserva)) {
            $errors[] = "Data inválida ou no passado.";
      }

      if (!$hora_reserva || !validate_time($hora_reserva)) {
            $errors[] = "Hora inválida.";
      }

      if (!is_time_slot_available($data_reserva, $hora_reserva)) {
            $errors[] = "Este horário já está reservado. Por favor, escolha outro.";
      }

      if (empty($errors)) {
            $query = "INSERT INTO reservas (utilizador_id, veiculo_id, data_reserva, hora_reserva, observacoes, estado) 
                  VALUES (?, ?, ?, ?, ?, 'pendente')";
            $result = db_execute($query, 'iisss', [$user_id, $veiculo_id, $data_reserva, $hora_reserva, $observacoes]);

            if ($result) {
                  set_flash('success', 'Reserva efetuada com sucesso!');
                  redirect(BASE_URL . '/user/minhas_reservas.php');
            } else {
                  set_flash('error', 'Erro ao efetuar reserva.');
            }
      } else {
            foreach ($errors as $error) {
                  set_flash('error', $error);
            }
      }
}

$horas_disponiveis = get_available_hours();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Reservar Test Drive - Stand Automóvel SAW</title>
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
            <h1>Reservar Test Drive</h1>

            <?php echo display_flash(); ?>

            <div class="grid grid-2 mt-4">
                  <div class="card">
                        <h3>Veículo Selecionado</h3>
                        <?php if ($veiculo['foto_principal']): ?>
                              <img src="<?php echo IMAGES_URL . '/veiculos/' . escape_output($veiculo['foto_principal']); ?>"
                                    alt="<?php echo escape_output($veiculo['marca'] . ' ' . $veiculo['modelo']); ?>"
                                    style="width: 100%; border-radius: 8px; margin: 1rem 0;">
                        <?php endif; ?>
                        <h2><?php echo escape_output($veiculo['marca'] . ' ' . $veiculo['modelo']); ?></h2>
                        <div class="vehicle-specs mt-2">
                              <span>📅 <?php echo $veiculo['ano_fabrico']; ?></span>
                              <span>⛽ <?php echo $veiculo['combustivel']; ?></span>
                              <span>🛣️ <?php echo number_format($veiculo['quilometros'], 0, ',', '.'); ?> km</span>
                        </div>
                        <div class="vehicle-price mt-2"><?php echo format_price($veiculo['preco']); ?></div>
                  </div>

                  <div class="card">
                        <h3>Dados da Reserva</h3>
                        <form method="POST" action="">
                              <div class="form-group">
                                    <label for="data_reserva">Data</label>
                                    <input type="date" id="data_reserva" name="data_reserva" class="form-control"
                                          min="<?php echo date('Y-m-d'); ?>" required>
                              </div>

                              <div class="form-group">
                                    <label for="hora_reserva">Hora</label>
                                    <select id="hora_reserva" name="hora_reserva" class="form-control" required>
                                          <option value="">Selecione uma hora</option>
                                          <?php foreach ($horas_disponiveis as $hora): ?>
                                                <option value="<?php echo $hora; ?>"><?php echo $hora; ?></option>
                                          <?php endforeach; ?>
                                    </select>
                                    <small style="color: var(--gray); font-size: 0.875rem;">
                                          Apenas 1 test drive por horário disponível.
                                    </small>
                              </div>

                              <div class="form-group">
                                    <label for="observacoes">Observações (opcional)</label>
                                    <textarea id="observacoes" name="observacoes" class="form-control"></textarea>
                              </div>

                              <button type="submit" class="btn btn-primary btn-block">Confirmar Reserva</button>
                              <a href="veiculos.php" class="btn btn-secondary btn-block mt-2">Cancelar</a>
                        </form>
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