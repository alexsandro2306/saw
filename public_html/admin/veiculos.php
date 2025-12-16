<?php
define('SAW_APP', true);
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/session.php';
require_once '../../includes/funcoes.php';
require_once '../../includes/sanitizacao.php';
require_once '../../includes/auth.php';

require_admin();

// Parâmetros
$search = sanitize_string(get('search', ''));
$page = sanitize_int(get('page', 1));

// WHERE clause
$where_clause = '';
$params = [];
$types = '';

if (!empty($search)) {
      $where_clause = "WHERE marca LIKE ? OR modelo LIKE ?";
      $search_param = '%' . $search . '%';
      $params = [$search_param, $search_param];
      $types = 'ss';
}

// Contar total
$count_query = "SELECT COUNT(*) as total FROM veiculos $where_clause";
$count_result = empty($params) ? db_query($count_query) : db_query($count_query, $types, $params);
$total_items = $count_result->fetch_assoc()['total'];

// Paginação
$pagination = get_pagination_info($total_items, 15, $page);

// Query principal
$query = "SELECT * FROM veiculos $where_clause ORDER BY data_criacao DESC LIMIT ? OFFSET ?";
$final_params = array_merge($params, [$pagination['items_per_page'], $pagination['offset']]);
$final_types = $types . 'ii';
$result = db_query($query, $final_types, $final_params);

$base_url = build_url_with_params(['search' => $search]);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Veículos - Admin - Stand Automóvel SAW</title>
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                  <h1>Gestão de Veículos</h1>
                  <a href="veiculo_form.php" class="btn btn-success">+ Adicionar Veículo</a>
            </div>

            <?php echo display_flash(); ?>

            <!-- Pesquisa -->
            <div class="filter-bar">
                  <form method="GET">
                        <div class="form-group search-box">
                              <label for="search">Pesquisar Veículo</label>
                              <span class="search-icon">🔍</span>
                              <input type="text" id="search" name="search" class="form-control"
                                    placeholder="Marca ou modelo..." value="<?php echo escape_output($search); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Pesquisar</button>
                        <a href="veiculos.php" class="btn btn-secondary mt-2">Limpar</a>
                  </form>
            </div>

            <p><strong><?php echo $total_items; ?></strong> veículos no total</p>

            <?php if ($result && $result->num_rows > 0): ?>
                  <div class="table-container mt-4">
                        <table>
                              <thead>
                                    <tr>
                                          <th>ID</th>
                                          <th>Veículo</th>
                                          <th>Ano</th>
                                          <th>Km</th>
                                          <th>Preço</th>
                                          <th>Estado</th>
                                          <th>Ações</th>
                                    </tr>
                              </thead>
                              <tbody>
                                    <?php while ($veiculo = $result->fetch_assoc()): ?>
                                          <tr>
                                                <td><?php echo $veiculo['id']; ?></td>
                                                <td>
                                                      <?php if ($veiculo['foto_principal']): ?>
                                                            <img src="<?php echo IMAGES_URL . '/veiculos/' . escape_output($veiculo['foto_principal']); ?>"
                                                                  alt="Foto"
                                                                  style="width: 50px; height: 35px; border-radius: 4px; object-fit: cover; vertical-align: middle; margin-right: 8px;">
                                                      <?php endif; ?>
                                                      <strong><?php echo escape_output($veiculo['marca'] . ' ' . $veiculo['modelo']); ?></strong>
                                                </td>
                                                <td><?php echo $veiculo['ano_fabrico']; ?></td>
                                                <td><?php echo number_format($veiculo['quilometros'], 0, ',', '.'); ?> km</td>
                                                <td><?php echo format_price($veiculo['preco']); ?></td>
                                                <td>
                                                      <?php
                                                      $badge_class = 'badge-success';
                                                      if ($veiculo['estado'] === 'indisponivel')
                                                            $badge_class = 'badge-danger';
                                                      elseif ($veiculo['estado'] === 'brevemente')
                                                            $badge_class = 'badge-warning';
                                                      ?>
                                                      <span
                                                            class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($veiculo['estado']); ?></span>
                                                </td>
                                                <td>
                                                      <a href="veiculo_form.php?id=<?php echo $veiculo['id']; ?>"
                                                            class="btn btn-primary btn-small">Editar</a>
                                                      <a href="veiculo_delete.php?id=<?php echo $veiculo['id']; ?>"
                                                            class="btn btn-danger btn-small"
                                                            onclick="return confirm('Tem certeza que deseja eliminar este veículo?')">Eliminar</a>
                                                </td>
                                          </tr>
                                    <?php endwhile; ?>
                              </tbody>
                        </table>
                  </div>

                  <?php echo display_pagination($pagination, $base_url); ?>
            <?php else: ?>
                  <div class="alert alert-info mt-4">Nenhum veículo encontrado. <a href="veiculo_form.php"
                              style="color: var(--primary); font-weight: 600;">Adicionar primeiro veículo</a></div>
            <?php endif; ?>
      </main>

      <footer>
            <div class="container">
                  <p>&copy; <?php echo date('Y'); ?> Stand Automóvel SAW. Todos os direitos reservados.</p>
            </div>
      </footer>
</body>

</html>