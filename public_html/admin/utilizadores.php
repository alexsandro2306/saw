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
$where_clause = "WHERE tipo = 'user'";
$params = [];
$types = '';

if (!empty($search)) {
      $where_clause .= " AND (nome LIKE ? OR email LIKE ?)";
      $search_param = '%' . $search . '%';
      $params = [$search_param, $search_param];
      $types = 'ss';
}

// Contar total
$count_query = "SELECT COUNT(*) as total FROM utilizadores $where_clause";
$count_result = empty($params) ? db_query($count_query) : db_query($count_query, $types, $params);
$total_items = $count_result->fetch_assoc()['total'];

// Paginação
$pagination = get_pagination_info($total_items, 15, $page);

// Query principal
$query = "SELECT * FROM utilizadores $where_clause ORDER BY data_criacao DESC LIMIT ? OFFSET ?";
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
      <title>Utilizadores - Admin - Stand Automóvel SAW</title>
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
            <h1>Utilizadores Registados</h1>

            <?php echo display_flash(); ?>

            <!-- Pesquisa -->
            <div class="filter-bar">
                  <form method="GET">
                        <div class="form-group search-box">
                              <label for="search">Pesquisar Utilizador</label>
                              <span class="search-icon">🔍</span>
                              <input type="text" id="search" name="search" class="form-control"
                                    placeholder="Nome ou email..." value="<?php echo escape_output($search); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Pesquisar</button>
                        <a href="utilizadores.php" class="btn btn-secondary mt-2">Limpar</a>
                  </form>
            </div>

            <p><strong><?php echo $total_items; ?></strong> utilizadores registados</p>

            <?php if ($result && $result->num_rows > 0): ?>
                  <div class="table-container mt-4">
                        <table>
                              <thead>
                                    <tr>
                                          <th>ID</th>
                                          <th>Nome</th>
                                          <th>Email</th>
                                          <th>Data de Registo</th>
                                          <th>Reservas</th>
                                    </tr>
                              </thead>
                              <tbody>
                                    <?php while ($user = $result->fetch_assoc()): ?>
                                          <?php
                                          $query_reservas = "SELECT COUNT(*) as total FROM reservas WHERE utilizador_id = ?";
                                          $result_reservas = db_query($query_reservas, 'i', [$user['id']]);
                                          $total_reservas = $result_reservas ? $result_reservas->fetch_assoc()['total'] : 0;
                                          ?>
                                          <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td>
                                                      <?php if ($user['foto_perfil']): ?>
                                                            <img src="<?php echo IMAGES_URL . '/perfis/' . escape_output($user['foto_perfil']); ?>"
                                                                  alt="Foto"
                                                                  style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 8px;">
                                                      <?php endif; ?>
                                                      <strong><?php echo escape_output($user['nome']); ?></strong>
                                                </td>
                                                <td><?php echo escape_output($user['email']); ?></td>
                                                <td><?php echo format_datetime($user['data_criacao']); ?></td>
                                                <td><?php echo $total_reservas; ?></td>
                                          </tr>
                                    <?php endwhile; ?>
                              </tbody>
                        </table>
                  </div>

                  <?php echo display_pagination($pagination, $base_url); ?>
            <?php else: ?>
                  <div class="alert alert-info mt-4">Nenhum utilizador encontrado.</div>
            <?php endif; ?>
      </main>

      <footer>
            <div class="container">
                  <p>&copy; <?php echo date('Y'); ?> Stand Automóvel SAW. Todos os direitos reservados.</p>
            </div>
      </footer>
</body>

</html>