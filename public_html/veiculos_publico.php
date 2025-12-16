<?php
/**
 * Listagem Pública de Veículos (sem informação de estado)
 */

define('SAW_APP', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/funcoes.php';
require_once '../includes/sanitizacao.php';
require_once '../includes/auth.php';

// Buscar todos os veículos (sem mostrar estado)
$query = "SELECT id, marca, modelo, cor, combustivel, ano_fabrico, quilometros, num_portas, num_lugares, preco, descricao, foto_principal 
          FROM veiculos 
          ORDER BY data_criacao DESC";
$result = db_query($query);

$page_title = 'Veículos';
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
            <a href="index.php" class="logo">🚗 Stand Automóvel SAW</a>
            <nav>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="veiculos_publico.php">Veículos</a></li>
                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin()): ?>
                            <li><a href="admin/index.php">Administração</a></li>
                        <?php else: ?>
                            <li><a href="user/index.php">Minha Área</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Sair</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="registo.php">Registar</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container py-4">
        <h1 class="mb-4">Nossos Veículos</h1>
        
        <?php echo display_flash(); ?>

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
                                <span>🚪 <?php echo $veiculo['num_portas']; ?> portas</span>
                                <span>👥 <?php echo $veiculo['num_lugares']; ?> lugares</span>
                            </div>
                            
                            <?php if ($veiculo['descricao']): ?>
                                <p style="color: var(--gray); font-size: 0.875rem; margin: 1rem 0;">
                                    <?php echo escape_output(truncate($veiculo['descricao'], 100)); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="vehicle-price"><?php echo format_price($veiculo['preco']); ?></div>
                            
                            <?php if (is_logged_in() && is_user()): ?>
                                <a href="user/reservar.php?veiculo=<?php echo $veiculo['id']; ?>" class="btn btn-primary btn-block">Agendar Test Drive</a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-secondary btn-block">Login para Agendar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Não há veículos disponíveis no momento.</div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Stand Automóvel SAW. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
