<?php
define('SAW_APP', true);
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/session.php';
require_once '../../includes/funcoes.php';
require_once '../../includes/sanitizacao.php';
require_once '../../includes/auth.php';

require_user();

// Parâmetros de filtro, pesquisa e ordenação
$search = sanitize_string(get('search', ''));
$marca_filtro = sanitize_string(get('marca', ''));
$ano_filtro = sanitize_int(get('ano', 0));
$order_by = sanitize_string(get('order', 'data_criacao'));
$order_dir = sanitize_string(get('dir', 'DESC'));
$page = sanitize_int(get('page', 1));

// Validar ordenação
$allowed_orders = ['data_criacao', 'preco', 'ano_fabrico', 'marca'];
$allowed_dirs = ['ASC', 'DESC'];
if (!in_array($order_by, $allowed_orders)) $order_by = 'data_criacao';
if (!in_array($order_dir, $allowed_dirs)) $order_dir = 'DESC';

// Construir WHERE clause
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(marca LIKE ? OR modelo LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($marca_filtro)) {
    $where_conditions[] = "marca = ?";
    $params[] = $marca_filtro;
    $types .= 's';
}

if ($ano_filtro > 0) {
    $where_conditions[] = "ano_fabrico = ?";
    $params[] = $ano_filtro;
    $types .= 'i';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Contar total de veículos
$count_query = "SELECT COUNT(*) as total FROM veiculos $where_clause";
$count_result = empty($params) ? db_query($count_query) : db_query($count_query, $types, $params);
$total_items = $count_result->fetch_assoc()['total'];

// Calcular paginação
$pagination = get_pagination_info($total_items, 12, $page);

// Query principal com paginação
$query = "SELECT * FROM veiculos $where_clause ORDER BY $order_by $order_dir LIMIT ? OFFSET ?";
$final_params = array_merge($params, [$pagination['items_per_page'], $pagination['offset']]);
$final_types = $types . 'ii';
$result = db_query($query, $final_types, $final_params);

// Buscar marcas para filtro
$query_marcas = "SELECT DISTINCT marca FROM veiculos ORDER BY marca";
$result_marcas = db_query($query_marcas);

// Buscar anos para filtro
$query_anos = "SELECT DISTINCT ano_fabrico FROM veiculos ORDER BY ano_fabrico DESC";
$result_anos = db_query($query_anos);

// Construir URL base para paginação
$url_params = ['search' => $search, 'marca' => $marca_filtro, 'ano' => $ano_filtro, 'order' => $order_by, 'dir' => $order_dir];
$base_url = build_url_with_params($url_params);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veículos - Stand Automóvel SAW</title>
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
        <h1>Veículos Disponíveis</h1>
        
        <?php echo display_flash(); ?>

        <!-- Filtros e Pesquisa -->
        <div class="filter-bar">
            <form method="GET" action="">
                <div class="grid grid-3">
                    <div class="form-group search-box">
                        <label for="search">Pesquisar</label>
                        <span class="search-icon">🔍</span>
                        <input type="text" id="search" name="search" class="form-control" 
                               placeholder="Marca ou modelo..." value="<?php echo escape_output($search); ?>">
                    </div>
                    <div class="form-group">
                        <label for="marca">Marca</label>
                        <select id="marca" name="marca" class="form-control">
                            <option value="">Todas as Marcas</option>
                            <?php while ($marca = $result_marcas->fetch_assoc()): ?>
                                <option value="<?php echo escape_output($marca['marca']); ?>" 
                                        <?php echo $marca_filtro === $marca['marca'] ? 'selected' : ''; ?>>
                                    <?php echo escape_output($marca['marca']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ano">Ano de Fabrico</label>
                        <select id="ano" name="ano" class="form-control">
                            <option value="">Todos os Anos</option>
                            <?php while ($ano = $result_anos->fetch_assoc()): ?>
                                <option value="<?php echo $ano['ano_fabrico']; ?>" 
                                        <?php echo $ano_filtro == $ano['ano_fabrico'] ? 'selected' : ''; ?>>
                                    <?php echo $ano['ano_fabrico']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
                <a href="veiculos.php" class="btn btn-secondary mt-2">Limpar Filtros</a>
            </form>
        </div>

        <!-- Ordenação e Resultados -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 1.5rem 0;">
            <p><strong><?php echo $total_items; ?></strong> veículos encontrados</p>
            <div>
                <strong>Ordenar:</strong>
                <a href="?<?php echo http_build_query(array_merge($url_params, ['order' => 'preco', 'dir' => 'ASC', 'page' => 1])); ?>" 
                   class="btn btn-small btn-secondary">Preço ↑</a>
                <a href="?<?php echo http_build_query(array_merge($url_params, ['order' => 'preco', 'dir' => 'DESC', 'page' => 1])); ?>" 
                   class="btn btn-small btn-secondary">Preço ↓</a>
                <a href="?<?php echo http_build_query(array_merge($url_params, ['order' => 'ano_fabrico', 'dir' => 'DESC', 'page' => 1])); ?>" 
                   class="btn btn-small btn-secondary">Ano ↓</a>
                <a href="?<?php echo http_build_query(array_merge($url_params, ['order' => 'marca', 'dir' => 'ASC', 'page' => 1])); ?>" 
                   class="btn btn-small btn-secondary">A-Z</a>
            </div>
        </div>

        <!-- Listagem de Veículos -->
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="grid grid-3">
                <?php while ($veiculo = $result->fetch_assoc()): ?>
                    <div class="vehicle-card">
                        <?php if ($veiculo['foto_principal']): ?>
                            <img src="<?php echo IMAGES_URL . '/veiculos/' . escape_output($veiculo['foto_principal']); ?>" 
                                 alt="<?php echo escape_output($veiculo['marca'] . ' ' . $veiculo['modelo']); ?>" 
                                 class="vehicle-image">
                        <?php else: ?>
                            <div class="vehicle-image" style="display: flex; align-items: center; justify-content: center; font-size: 3rem;">🚗</div>
                        <?php endif; ?>
                        
                        <div class="vehicle-info">
                            <h3 class="vehicle-title"><?php echo escape_output($veiculo['marca'] . ' ' . $veiculo['modelo']); ?></h3>
                            
                            <div class="vehicle-specs">
                                <span>📅 <?php echo escape_output($veiculo['ano_fabrico']); ?></span>
                                <span>⛽ <?php echo escape_output($veiculo['combustivel']); ?></span>
                                <span>🛣️ <?php echo number_format($veiculo['quilometros'], 0, ',', '.'); ?> km</span>
                            </div>
                            
                            <div style="margin: 1rem 0;">
                                <?php
                                $badge_class = 'badge-success';
                                $estado_text = 'Disponível';
                                if ($veiculo['estado'] === 'indisponivel') {
                                    $badge_class = 'badge-danger';
                                    $estado_text = 'Indisponível';
                                } elseif ($veiculo['estado'] === 'brevemente') {
                                    $badge_class = 'badge-warning';
                                    $estado_text = 'Brevemente';
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $estado_text; ?></span>
                            </div>
                            
                            <div class="vehicle-price"><?php echo format_price($veiculo['preco']); ?></div>
                            
                            <?php if ($veiculo['estado'] === 'disponivel'): ?>
                                <a href="reservar.php?veiculo=<?php echo $veiculo['id']; ?>" class="btn btn-primary btn-block">Agendar Test Drive</a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-block" disabled>Indisponível</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Paginação -->
            <?php echo display_pagination($pagination, $base_url); ?>
        <?php else: ?>
            <div class="alert alert-info mt-4">Nenhum veículo encontrado com os filtros selecionados.</div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Stand Automóvel SAW. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
