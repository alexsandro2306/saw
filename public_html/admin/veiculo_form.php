<?php
define('SAW_APP', true);
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/session.php';
require_once '../../includes/funcoes.php';
require_once '../../includes/sanitizacao.php';
require_once '../../includes/validacao.php';
require_once '../../includes/auth.php';

require_admin();

$veiculo_id = sanitize_int(get('id', 0));
$veiculo = null;
$is_edit = false;

if ($veiculo_id > 0) {
      $query = "SELECT * FROM veiculos WHERE id = ?";
      $result = db_query($query, 'i', [$veiculo_id]);
      if ($result && $result->num_rows > 0) {
            $veiculo = $result->fetch_assoc();
            $is_edit = true;
      }
}

if (is_post()) {
      $marca = sanitize_string(post('marca'));
      $modelo = sanitize_string(post('modelo'));
      $cor = sanitize_string(post('cor'));
      $combustivel = sanitize_string(post('combustivel'));
      $ano_fabrico = sanitize_int(post('ano_fabrico'));
      $quilometros = sanitize_int(post('quilometros'));
      $num_portas = sanitize_int(post('num_portas'));
      $num_lugares = sanitize_int(post('num_lugares'));
      $preco = sanitize_float(post('preco'));
      $estado = sanitize_string(post('estado'));
      $descricao = sanitize_string(post('descricao'));

      $errors = [];

      if (empty($marca) || empty($modelo))
            $errors[] = "Marca e modelo são obrigatórios.";
      if (!validate_year($ano_fabrico, 1900))
            $errors[] = "Ano inválido.";
      if (!validate_positive_int($quilometros))
            $errors[] = "Quilómetros inválidos.";
      if (!validate_positive_float($preco))
            $errors[] = "Preço inválido.";
      if (!validate_enum($combustivel, ['Gasolina', 'Diesel', 'Elétrico', 'Híbrido', 'GPL']))
            $errors[] = "Combustível inválido.";
      if (!validate_enum($estado, ['disponivel', 'indisponivel', 'brevemente']))
            $errors[] = "Estado inválido.";

      $foto_principal = $is_edit ? $veiculo['foto_principal'] : null;
      if (isset($_FILES['foto_principal']) && $_FILES['foto_principal']['error'] === UPLOAD_ERR_OK) {
            $uploaded = upload_image($_FILES['foto_principal'], IMAGES_PATH . '/veiculos', 'veiculo_');
            if ($uploaded) {
                  if ($foto_principal)
                        delete_image(IMAGES_PATH . '/veiculos/' . $foto_principal);
                  $foto_principal = $uploaded;
            }
      }

      if (empty($errors)) {
            if ($is_edit) {
                  $query = "UPDATE veiculos SET marca=?, modelo=?, cor=?, combustivel=?, ano_fabrico=?, quilometros=?, num_portas=?, num_lugares=?, preco=?, estado=?, descricao=?, foto_principal=? WHERE id=?";
                  $result = db_execute($query, 'ssssiiiidsssi', [$marca, $modelo, $cor, $combustivel, $ano_fabrico, $quilometros, $num_portas, $num_lugares, $preco, $estado, $descricao, $foto_principal, $veiculo_id]);
            } else {
                  $query = "INSERT INTO veiculos (marca, modelo, cor, combustivel, ano_fabrico, quilometros, num_portas, num_lugares, preco, estado, descricao, foto_principal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                  $result = db_execute($query, 'ssssiiiidsss', [$marca, $modelo, $cor, $combustivel, $ano_fabrico, $quilometros, $num_portas, $num_lugares, $preco, $estado, $descricao, $foto_principal]);
            }

            if ($result) {
                  set_flash('success', $is_edit ? 'Veículo atualizado!' : 'Veículo adicionado!');
                  redirect(BASE_URL . '/admin/veiculos.php');
            } else {
                  set_flash('error', 'Erro ao guardar veículo.');
            }
      } else {
            foreach ($errors as $error)
                  set_flash('error', $error);
      }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?php echo $is_edit ? 'Editar' : 'Adicionar'; ?> Veículo - Admin</title>
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
            <h1><?php echo $is_edit ? 'Editar' : 'Adicionar'; ?> Veículo</h1>

            <?php echo display_flash(); ?>

            <div class="card mt-4">
                  <form method="POST" enctype="multipart/form-data">
                        <div class="grid grid-2">
                              <div class="form-group">
                                    <label for="marca">Marca *</label>
                                    <input type="text" id="marca" name="marca" class="form-control"
                                          value="<?php echo $veiculo ? escape_output($veiculo['marca']) : ''; ?>"
                                          required>
                              </div>
                              <div class="form-group">
                                    <label for="modelo">Modelo *</label>
                                    <input type="text" id="modelo" name="modelo" class="form-control"
                                          value="<?php echo $veiculo ? escape_output($veiculo['modelo']) : ''; ?>"
                                          required>
                              </div>
                        </div>

                        <div class="grid grid-3">
                              <div class="form-group">
                                    <label for="cor">Cor *</label>
                                    <input type="text" id="cor" name="cor" class="form-control"
                                          value="<?php echo $veiculo ? escape_output($veiculo['cor']) : ''; ?>"
                                          required>
                              </div>
                              <div class="form-group">
                                    <label for="combustivel">Combustível *</label>
                                    <select id="combustivel" name="combustivel" class="form-control" required>
                                          <option value="">Selecione</option>
                                          <?php foreach (['Gasolina', 'Diesel', 'Elétrico', 'Híbrido', 'GPL'] as $comb): ?>
                                                <option value="<?php echo $comb; ?>" <?php echo ($veiculo && $veiculo['combustivel'] === $comb) ? 'selected' : ''; ?>>
                                                      <?php echo $comb; ?></option>
                                          <?php endforeach; ?>
                                    </select>
                              </div>
                              <div class="form-group">
                                    <label for="ano_fabrico">Ano de Fabrico *</label>
                                    <input type="number" id="ano_fabrico" name="ano_fabrico" class="form-control"
                                          min="1900" max="<?php echo date('Y'); ?>"
                                          value="<?php echo $veiculo ? $veiculo['ano_fabrico'] : ''; ?>" required>
                              </div>
                        </div>

                        <div class="grid grid-3">
                              <div class="form-group">
                                    <label for="quilometros">Quilómetros *</label>
                                    <input type="number" id="quilometros" name="quilometros" class="form-control"
                                          min="0" value="<?php echo $veiculo ? $veiculo['quilometros'] : ''; ?>"
                                          required>
                              </div>
                              <div class="form-group">
                                    <label for="num_portas">Nº Portas *</label>
                                    <input type="number" id="num_portas" name="num_portas" class="form-control" min="2"
                                          max="5" value="<?php echo $veiculo ? $veiculo['num_portas'] : ''; ?>"
                                          required>
                              </div>
                              <div class="form-group">
                                    <label for="num_lugares">Nº Lugares *</label>
                                    <input type="number" id="num_lugares" name="num_lugares" class="form-control"
                                          min="2" max="9" value="<?php echo $veiculo ? $veiculo['num_lugares'] : ''; ?>"
                                          required>
                              </div>
                        </div>

                        <div class="grid grid-2">
                              <div class="form-group">
                                    <label for="preco">Preço (€) *</label>
                                    <input type="number" id="preco" name="preco" class="form-control" step="0.01"
                                          min="0" value="<?php echo $veiculo ? $veiculo['preco'] : ''; ?>" required>
                              </div>
                              <div class="form-group">
                                    <label for="estado">Estado *</label>
                                    <select id="estado" name="estado" class="form-control" required>
                                          <?php foreach (['disponivel' => 'Disponível', 'indisponivel' => 'Indisponível', 'brevemente' => 'Brevemente'] as $key => $label): ?>
                                                <option value="<?php echo $key; ?>" <?php echo ($veiculo && $veiculo['estado'] === $key) ? 'selected' : ''; ?>><?php echo $label; ?>
                                                </option>
                                          <?php endforeach; ?>
                                    </select>
                              </div>
                        </div>

                        <div class="form-group">
                              <label for="descricao">Descrição</label>
                              <textarea id="descricao" name="descricao"
                                    class="form-control"><?php echo $veiculo ? escape_output($veiculo['descricao']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                              <label for="foto_principal">Foto Principal</label>
                              <input type="file" id="foto_principal" name="foto_principal" class="form-control"
                                    accept="image/*">
                              <?php if ($veiculo && $veiculo['foto_principal']): ?>
                                    <img src="<?php echo IMAGES_URL . '/veiculos/' . escape_output($veiculo['foto_principal']); ?>"
                                          alt="Foto atual" style="width: 200px; margin-top: 10px; border-radius: 8px;">
                              <?php endif; ?>
                        </div>

                        <button type="submit"
                              class="btn btn-success"><?php echo $is_edit ? 'Atualizar' : 'Adicionar'; ?>
                              Veículo</button>
                        <a href="veiculos.php" class="btn btn-secondary">Cancelar</a>
                  </form>
            </div>
      </main>

      <footer>
            <div class="container">
                  <p>&copy; <?php echo date('Y'); ?> Stand Automóvel SAW. Todos os direitos reservados.</p>
            </div>
      </footer>
</body>

</html>