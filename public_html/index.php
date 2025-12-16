<?php
/**
 * Página Inicial - Área Pública
 */

define('SAW_APP', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/funcoes.php';
require_once '../includes/sanitizacao.php';
require_once '../includes/validacao.php';
require_once '../includes/auth.php';

$page_title = 'Bem-vindo';
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
                  <a href="index.php" class="logo">
                        🚗 Stand Automóvel SAW
                  </a>
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

      <section class="hero">
            <div class="container">
                  <h1>Encontre o Seu Carro de Sonho</h1>
                  <p>Os melhores veículos com as melhores condições</p>
                  <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                        <a href="veiculos_publico.php" class="btn btn-primary">Ver Veículos</a>
                        <?php if (!is_logged_in()): ?>
                              <a href="registo.php" class="btn btn-secondary">Criar Conta</a>
                        <?php endif; ?>
                  </div>
            </div>
      </section>

      <main class="container py-5">
            <?php echo display_flash(); ?>

            <div class="grid grid-3">
                  <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🚗</div>
                        <h3>Vasta Seleção</h3>
                        <p>Centenas de veículos de todas as marcas e modelos para escolher.</p>
                  </div>

                  <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                        <h3>Qualidade Garantida</h3>
                        <p>Todos os nossos veículos são inspecionados e certificados.</p>
                  </div>

                  <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🔧</div>
                        <h3>Test Drive</h3>
                        <p>Agende um test drive e experimente o veículo antes de comprar.</p>
                  </div>
            </div>

            <?php
            // Mostrar alguns veículos em destaque
            $query = "SELECT * FROM veiculos WHERE estado = 'disponivel' ORDER BY data_criacao DESC LIMIT 6";
            $result = db_query($query);
            ?>

            <?php if ($result && $result->num_rows > 0): ?>
                  <h2 class="mt-4 mb-3">Veículos em Destaque</h2>
                  <div class="grid grid-3">
                        <?php while ($veiculo = $result->fetch_assoc()): ?>
                              <div class="vehicle-card">
                                    <?php if ($veiculo['foto_principal']): ?>
                                          <img src="<?php echo IMAGES_URL . '/veiculos/' . escape_output($veiculo['foto_principal']); ?>"
                                                alt="<?php echo escape_output($veiculo['marca'] . ' ' . $veiculo['modelo']); ?>"
                                                class="vehicle-image">
                                    <?php else: ?>
                                          <div class="vehicle-image"
                                                style="display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                                🚗
                                          </div>
                                    <?php endif; ?>

                                    <div class="vehicle-info">
                                          <h3 class="vehicle-title">
                                                <?php echo escape_output($veiculo['marca'] . ' ' . $veiculo['modelo']); ?>
                                          </h3>

                                          <div class="vehicle-specs">
                                                <span class="vehicle-spec">📅
                                                      <?php echo escape_output($veiculo['ano_fabrico']); ?></span>
                                                <span class="vehicle-spec">⛽
                                                      <?php echo escape_output($veiculo['combustivel']); ?></span>
                                                <span class="vehicle-spec">🛣️
                                                      <?php echo number_format($veiculo['quilometros'], 0, ',', '.'); ?> km</span>
                                          </div>

                                          <div class="vehicle-price">
                                                <?php echo format_price($veiculo['preco']); ?>
                                          </div>

                                          <?php if (is_logged_in() && is_user()): ?>
                                                <a href="user/reservar.php?veiculo=<?php echo $veiculo['id']; ?>"
                                                      class="btn btn-primary btn-block">
                                                      Agendar Test Drive
                                                </a>
                                          <?php else: ?>
                                                <a href="login.php" class="btn btn-secondary btn-block">
                                                      Login para Agendar
                                                </a>
                                          <?php endif; ?>
                                    </div>
                              </div>
                        <?php endwhile; ?>
                  </div>

                  <div class="text-center mt-4">
                        <a href="veiculos_publico.php" class="btn btn-primary">Ver Todos os Veículos</a>
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