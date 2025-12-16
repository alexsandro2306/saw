<?php
define('SAW_APP', true);
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/session.php';
require_once '../../includes/funcoes.php';
require_once '../../includes/sanitizacao.php';
require_once '../../includes/auth.php';

require_admin();

$veiculo_id = sanitize_int(get('id', 0));

if ($veiculo_id > 0) {
      // Buscar veículo
      $query = "SELECT * FROM veiculos WHERE id = ?";
      $result = db_query($query, 'i', [$veiculo_id]);

      if ($result && $result->num_rows > 0) {
            $veiculo = $result->fetch_assoc();

            // Eliminar foto
            if ($veiculo['foto_principal']) {
                  delete_image(IMAGES_PATH . '/veiculos/' . $veiculo['foto_principal']);
            }

            // Eliminar veículo
            $query = "DELETE FROM veiculos WHERE id = ?";
            if (db_execute($query, 'i', [$veiculo_id])) {
                  set_flash('success', 'Veículo eliminado com sucesso!');
            } else {
                  set_flash('error', 'Erro ao eliminar veículo.');
            }
      } else {
            set_flash('error', 'Veículo não encontrado.');
      }
} else {
      set_flash('error', 'ID inválido.');
}

redirect(BASE_URL . '/admin/veiculos.php');
