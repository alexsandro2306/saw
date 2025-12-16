<?php
define('SAW_APP', true);
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/session.php';
require_once '../../includes/funcoes.php';
require_once '../../includes/sanitizacao.php';
require_once '../../includes/auth.php';

require_admin();

// Processar alteração de estado enviada pelo admin
if (is_post() && post('action') === 'change_status') {
      $reserva_id = sanitize_int(post('reserva_id', 0));
      $novo_estado = sanitize_string(post('novo_estado', ''));

      $estados_permitidos = ['pendente', 'confirmada', 'cancelada', 'concluida'];

      if ($reserva_id > 0 && in_array($novo_estado, $estados_permitidos, true)) {
            $update_q = "UPDATE reservas SET estado = ? WHERE id = ?";
            $ok = db_execute($update_q, 'si', [$novo_estado, $reserva_id]);
            if ($ok) {
                  set_flash('success', 'Estado da reserva atualizado com sucesso.');
            } else {
                  set_flash('error', 'Erro ao atualizar estado da reserva.');
            }
      } else {
            set_flash('error', 'Dados inválidos.');
      }

      // Redirecionar para a mesma página (preservar filtros)
      $qs = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
      redirect(BASE_URL . '/admin/reservas.php' . $qs);
}

// Filtros
$data_filtro = sanitize_string(get('data', ''));
$veiculo_filtro = sanitize_int(get('veiculo', 0));
$estado_filtro = sanitize_string(get('estado', '')); // ← NOVO FILTRO
$page = sanitize_int(get('page', 1));

// Buscar veículos para filtro
$query_veiculos = "SELECT id, marca, modelo FROM veiculos ORDER BY marca, modelo";
$result_veiculos = db_query($query_veiculos);

// Construir WHERE
$where_conditions = [];
$params = [];
$types = '';

if (!empty($data_filtro)) {
      $where_conditions[] = "r.data_reserva = ?";
      $params[] = $data_filtro;
      $types .= 's';
}

if ($veiculo_filtro > 0) {
      $where_conditions[] = "r.veiculo_id = ?";
      $params[] = $veiculo_filtro;
      $types .= 'i';
}

// ← NOVO: Filtro de Estado
if (!empty($estado_filtro)) {
      $where_conditions[] = "r.estado = ?";
      $params[] = $estado_filtro;
      $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Contar total
$count_query = "SELECT COUNT(*) as total FROM reservas r $where_clause";
$count_result = empty($params) ? db_query($count_query) : db_query($count_query, $types, $params);
$total_items = $count_result->fetch_assoc()['total'];

// Paginação
$pagination = get_pagination_info($total_items, 20, $page);

// Query principal
$query = "SELECT r.*, u.nome as utilizador_nome, u.email as utilizador_email, v.marca, v.modelo 
          FROM reservas r 
          JOIN utilizadores u ON r.utilizador_id = u.id 
          JOIN veiculos v ON r.veiculo_id = v.id 
          $where_clause
          ORDER BY r.data_reserva DESC, r.hora_reserva DESC
          LIMIT ? OFFSET ?";
$final_params = array_merge($params, [$pagination['items_per_page'], $pagination['offset']]);
$final_types = $types . 'ii';
$result = db_query($query, $final_types, $final_params);

$base_url = build_url_with_params(['data' => $data_filtro, 'veiculo' => $veiculo_filtro, 'estado' => $estado_filtro]);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Reservas - Admin - Stand Automóvel SAW</title>
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
            <h1>Gestão de Reservas</h1>

            <?php echo display_flash(); ?>

            <!-- Filtros -->
            <div class="filter-bar">
                  <form method="GET">
                        <div class="grid grid-3">
                              <div class="form-group">
                                    <label for="data">Filtrar por Data</label>
                                    <input type="date" id="data" name="data" class="form-control"
                                          value="<?php echo escape_output($data_filtro); ?>">
                              </div>
                              <div class="form-group">
                                    <label for="veiculo">Filtrar por Veículo</label>
                                    <select id="veiculo" name="veiculo" class="form-control">
                                          <option value="">Todos os Veículos</option>
                                          <?php
                                          // Reset pointer
                                          $result_veiculos->data_seek(0);
                                          while ($veiculo = $result_veiculos->fetch_assoc()): 
                                          ?>
                                                <option value="<?php echo $veiculo['id']; ?>" <?php echo $veiculo_filtro == $veiculo['id'] ? 'selected' : ''; ?>>
                                                      <?php echo escape_output($veiculo['marca'] . ' ' . $veiculo['modelo']); ?>
                                                </option>
                                          <?php endwhile; ?>
                                    </select>
                              </div>
                              <!-- NOVO: Filtro de Estado -->
                              <div class="form-group">
                                    <label for="estado">Filtrar por Estado</label>
                                    <select id="estado" name="estado" class="form-control">
                                          <option value="">Todos os Estados</option>
                                          <option value="pendente" <?php echo $estado_filtro === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                          <option value="confirmada" <?php echo $estado_filtro === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                                          <option value="cancelada" <?php echo $estado_filtro === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                          <option value="concluida" <?php echo $estado_filtro === 'concluida' ? 'selected' : ''; ?>>Concluída</option>
                                    </select>
                              </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
                        <a href="reservas.php" class="btn btn-secondary mt-2">Limpar Filtros</a>
                  </form>
            </div>

            <p><strong><?php echo $total_items; ?></strong> reservas encontradas</p>

            <?php if ($result && $result->num_rows > 0): ?>
                  <div class="table-container mt-4">
                        <table>
                              <thead>
                                    <tr>
                                          <th>ID</th>
                                          <th>Utilizador</th>
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
                                                <td><?php echo $reserva['id']; ?></td>
                                                <td>
                                                      <strong><?php echo escape_output($reserva['utilizador_nome']); ?></strong><br>
                                                      <small style="color: var(--gray);"><?php echo escape_output($reserva['utilizador_email']); ?></small>
                                                </td>
                                                <td><?php echo escape_output($reserva['marca'] . ' ' . $reserva['modelo']); ?></td>
                                                <td><?php echo format_date($reserva['data_reserva']); ?></td>
                                                <td><?php echo format_time($reserva['hora_reserva']); ?></td>
                                                <td>
                                                      <?php
                                                      $badge_class = 'badge-warning';
                                                      if ($reserva['estado'] === 'confirmada')
                                                            $badge_class = 'badge-success';
                                                      elseif ($reserva['estado'] === 'cancelada')
                                                            $badge_class = 'badge-danger';
                                                      elseif ($reserva['estado'] === 'concluida')
                                                            $badge_class = 'badge-success';
                                                      ?>
                                                      <div style="display:flex;align-items:center;gap:0.5rem;">
                                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($reserva['estado']); ?></span>

                                                            <form method="POST" style="display:inline-block;">
                                                                  <input type="hidden" name="action" value="change_status">
                                                                  <input type="hidden" name="reserva_id" value="<?php echo (int)$reserva['id']; ?>">
                                                                  <select name="novo_estado" class="form-control" style="display:inline-block;width:auto;">
                                                                        <?php
                                                                        $estados = ['pendente' => 'Pendente', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada', 'concluida' => 'Concluída'];
                                                                        foreach ($estados as $key => $label):
                                                                        ?>
                                                                              <option value="<?php echo $key; ?>" <?php echo $reserva['estado'] === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                                                        <?php endforeach; ?>
                                                                  </select>
                                                                  <button type="submit" class="btn btn-sm btn-primary">Alterar</button>
                                                            </form>
                                                      </div>
                                                </td>
                                                <td><?php echo escape_output($reserva['observacoes'] ?: '-'); ?></td>
                                          </tr>
                                    <?php endwhile; ?>
                              </tbody>
                        </table>
                  </div>

                  <?php echo display_pagination($pagination, $base_url); ?>
            <?php else: ?>
                  <div class="alert alert-info mt-4">Nenhuma reserva encontrada com os filtros selecionados.</div>
            <?php endif; ?>
      </main>

      <footer>
            <div class="container">
                  <p>&copy; <?php echo date('Y'); ?> Stand Automóvel SAW. Todos os direitos reservados.</p>
            </div>
      </footer>
</body>

</html>
