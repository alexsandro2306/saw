<?php
/**
 * Funções Gerais
 * Funções auxiliares utilizadas em toda a aplicação
 */

// Prevenir acesso direto
if (!defined('SAW_APP')) {
      die('Acesso negado');
}

/**
 * Redireciona para um URL
 * @param string $url URL de destino
 */
function redirect($url)
{
      header("Location: " . $url);
      exit();
}

/**
 * Define uma mensagem flash na sessão
 * @param string $type Tipo da mensagem (success, error, warning, info)
 * @param string $message Mensagem a exibir
 */
function set_flash($type, $message)
{
      if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
      }
      $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Obtém e limpa as mensagens flash
 * @return array Array de mensagens flash
 */
function get_flash()
{
      if (isset($_SESSION['flash'])) {
            $messages = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $messages;
      }
      return [];
}

/**
 * Exibe mensagens flash em HTML
 * @return string HTML das mensagens
 */
function display_flash()
{
      $messages = get_flash();
      $html = '';

      foreach ($messages as $msg) {
            $html .= '<div class="alert alert-' . htmlspecialchars($msg['type']) . '">';
            $html .= htmlspecialchars($msg['message']);
            $html .= '</div>';
      }

      return $html;
}

/**
 * Faz upload de uma imagem
 * @param array $file Array $_FILES
 * @param string $destination Diretório de destino
 * @param string $prefix Prefixo do nome do ficheiro
 * @return string|false Nome do ficheiro ou false em erro
 */
function upload_image($file, $destination, $prefix = '')
{
      // Verificar se houve upload
      if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
      }

      // Verificar erros
      if ($file['error'] !== UPLOAD_ERR_OK) {
            set_flash('error', 'Erro ao fazer upload do ficheiro.');
            return false;
      }

      // Verificar tamanho
      if ($file['size'] > MAX_FILE_SIZE) {
            set_flash('error', 'Ficheiro demasiado grande. Máximo: 5MB.');
            return false;
      }

      // Verificar tipo MIME
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime_type = finfo_file($finfo, $file['tmp_name']);
      finfo_close($finfo);

      if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
            set_flash('error', 'Tipo de ficheiro não permitido. Use: JPG, PNG, GIF ou WEBP.');
            return false;
      }

      // Verificar extensão
      $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
            set_flash('error', 'Extensão de ficheiro não permitida.');
            return false;
      }

      // Gerar nome único
      $filename = $prefix . uniqid() . '_' . time() . '.' . $extension;
      $filepath = $destination . '/' . $filename;

      // Criar diretório se não existir
      if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
      }

      // Mover ficheiro
      if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filename;
      }

      set_flash('error', 'Erro ao guardar ficheiro.');
      return false;
}

/**
 * Elimina um ficheiro de imagem
 * @param string $filepath Caminho completo do ficheiro
 * @return bool
 */
function delete_image($filepath)
{
      if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
      }
      return false;
}

/**
 * Formata um preço em euros
 * @param float $price Preço
 * @return string Preço formatado
 */
function format_price($price)
{
      return number_format($price, 2, ',', '.') . ' €';
}

/**
 * Formata uma data para formato português
 * @param string $date Data em formato Y-m-d
 * @return string Data formatada
 */
function format_date($date)
{
      if (empty($date))
            return '';
      $timestamp = strtotime($date);
      return date('d/m/Y', $timestamp);
}

/**
 * Formata uma hora para formato português
 * @param string $time Hora em formato H:i:s
 * @return string Hora formatada
 */
function format_time($time)
{
      if (empty($time))
            return '';
      return substr($time, 0, 5); // HH:MM
}

/**
 * Formata data e hora
 * @param string $datetime DateTime em formato Y-m-d H:i:s
 * @return string DateTime formatado
 */
function format_datetime($datetime)
{
      if (empty($datetime))
            return '';
      $timestamp = strtotime($datetime);
      return date('d/m/Y H:i', $timestamp);
}

/**
 * Trunca um texto
 * @param string $text Texto
 * @param int $length Comprimento máximo
 * @param string $suffix Sufixo (...)
 * @return string Texto truncado
 */
function truncate($text, $length = 100, $suffix = '...')
{
      if (strlen($text) <= $length) {
            return $text;
      }
      return substr($text, 0, $length) . $suffix;
}

/**
 * Gera um token aleatório
 * @param int $length Comprimento do token
 * @return string Token
 */
function generate_token($length = 32)
{
      return bin2hex(random_bytes($length));
}

/**
 * Verifica se é um pedido POST
 * @return bool
 */
function is_post()
{
      return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Obtém valor de POST com fallback
 * @param string $key Chave
 * @param mixed $default Valor padrão
 * @return mixed
 */
function post($key, $default = '')
{
      return $_POST[$key] ?? $default;
}

/**
 * Obtém valor de GET com fallback
 * @param string $key Chave
 * @param mixed $default Valor padrão
 * @return mixed
 */
function get($key, $default = '')
{
      return $_GET[$key] ?? $default;
}

/**
 * Gera array de horas disponíveis para test drive
 * @return array
 */
function get_available_hours()
{
      $hours = [];
      $start = strtotime(TEST_DRIVE_HORA_INICIO);
      $end = strtotime(TEST_DRIVE_HORA_FIM);
      $interval = TEST_DRIVE_INTERVALO * 60; // converter para segundos

      for ($time = $start; $time < $end; $time += $interval) {
            $hours[] = date('H:i', $time);
      }

      return $hours;
}

/**
 * Calcula informações de paginação
 * @param int $total_items Total de itens
 * @param int $items_per_page Itens por página
 * @param int $current_page Página atual
 * @return array Informações de paginação
 */
function get_pagination_info($total_items, $items_per_page = 10, $current_page = 1)
{
      $total_pages = ceil($total_items / $items_per_page);
      $current_page = max(1, min($current_page, $total_pages));
      $offset = ($current_page - 1) * $items_per_page;

      return [
            'total_items' => $total_items,
            'items_per_page' => $items_per_page,
            'total_pages' => $total_pages,
            'current_page' => $current_page,
            'offset' => $offset,
            'has_previous' => $current_page > 1,
            'has_next' => $current_page < $total_pages
      ];
}

/**
 * Gera HTML de paginação
 * @param array $pagination_info Informações de paginação
 * @param string $base_url URL base (sem parâmetros)
 * @return string HTML da paginação
 */
function display_pagination($pagination_info, $base_url)
{
      if ($pagination_info['total_pages'] <= 1) {
            return '';
      }

      $html = '<div class="pagination">';

      // Botão Anterior
      if ($pagination_info['has_previous']) {
            $prev_page = $pagination_info['current_page'] - 1;
            $html .= '<a href="' . $base_url . '&page=' . $prev_page . '" class="btn btn-secondary btn-small">« Anterior</a>';
      }

      // Números de página
      $html .= '<span class="pagination-info">Página ' . $pagination_info['current_page'] . ' de ' . $pagination_info['total_pages'] . '</span>';

      // Botão Próximo
      if ($pagination_info['has_next']) {
            $next_page = $pagination_info['current_page'] + 1;
            $html .= '<a href="' . $base_url . '&page=' . $next_page . '" class="btn btn-secondary btn-small">Próximo »</a>';
      }

      $html .= '</div>';

      return $html;
}

/**
 * Constrói URL com parâmetros preservados
 * @param array $params Parâmetros a preservar
 * @return string URL construída
 */
function build_url_with_params($params = [])
{
      $query_params = [];

      foreach ($params as $key => $value) {
            if (!empty($value)) {
                  $query_params[] = urlencode($key) . '=' . urlencode($value);
            }
      }

      return '?' . implode('&', $query_params);
}

