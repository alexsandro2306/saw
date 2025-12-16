<?php
/**
 * Sanitização de Dados
 * Funções para limpar e sanitizar inputs
 */

// Prevenir acesso direto
if (!defined('SAW_APP')) {
      die('Acesso negado');
}

/**
 * Sanitiza uma string removendo tags HTML e caracteres especiais
 * @param string $string String a sanitizar
 * @return string String sanitizada
 */
function sanitize_string($string)
{
      // Remove tags HTML
      $string = strip_tags($string);
      // Remove espaços em branco no início e fim
      $string = trim($string);
      // Converte caracteres especiais para entidades HTML
      $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');

      return $string;
}

/**
 * Sanitiza um email
 * @param string $email Email a sanitizar
 * @return string Email sanitizado
 */
function sanitize_email($email)
{
      $email = trim($email);
      $email = filter_var($email, FILTER_SANITIZE_EMAIL);
      return strtolower($email);
}

/**
 * Sanitiza um número inteiro
 * @param mixed $number Número a sanitizar
 * @return int
 */
function sanitize_int($number)
{
      return (int) filter_var($number, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Sanitiza um número decimal
 * @param mixed $number Número a sanitizar
 * @return float
 */
function sanitize_float($number)
{
      return (float) filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Sanitiza um URL
 * @param string $url URL a sanitizar
 * @return string URL sanitizado
 */
function sanitize_url($url)
{
      return filter_var($url, FILTER_SANITIZE_URL);
}

/**
 * Sanitiza texto permitindo HTML básico
 * @param string $text Texto a sanitizar
 * @param array $allowed_tags Tags HTML permitidas
 * @return string Texto sanitizado
 */
function sanitize_html($text, $allowed_tags = ['p', 'br', 'strong', 'em', 'ul', 'ol', 'li'])
{
      $allowed = '<' . implode('><', $allowed_tags) . '>';
      return strip_tags($text, $allowed);
}

/**
 * Sanitiza um array de strings
 * @param array $array Array a sanitizar
 * @return array Array sanitizado
 */
function sanitize_array($array)
{
      return array_map('sanitize_string', $array);
}

/**
 * Sanitiza dados de POST
 * @param array $keys Chaves a sanitizar
 * @return array Array com dados sanitizados
 */
function sanitize_post($keys)
{
      $sanitized = [];

      foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                  $sanitized[$key] = sanitize_string($_POST[$key]);
            } else {
                  $sanitized[$key] = '';
            }
      }

      return $sanitized;
}

/**
 * Sanitiza dados de GET
 * @param array $keys Chaves a sanitizar
 * @return array Array com dados sanitizados
 */
function sanitize_get($keys)
{
      $sanitized = [];

      foreach ($keys as $key) {
            if (isset($_GET[$key])) {
                  $sanitized[$key] = sanitize_string($_GET[$key]);
            } else {
                  $sanitized[$key] = '';
            }
      }

      return $sanitized;
}

/**
 * Remove caracteres perigosos de um nome de ficheiro
 * @param string $filename Nome do ficheiro
 * @return string Nome sanitizado
 */
function sanitize_filename($filename)
{
      // Remove path
      $filename = basename($filename);
      // Remove caracteres especiais
      $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
      return $filename;
}

/**
 * Previne XSS ao exibir dados
 * @param string $data Dados a exibir
 * @return string Dados seguros para exibição
 */
function escape_output($data)
{
      return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitiza e valida uma data
 * @param string $date Data a sanitizar
 * @return string|false Data sanitizada ou false
 */
function sanitize_date($date)
{
      $date = trim($date);

      // Tentar converter para formato Y-m-d
      $d = DateTime::createFromFormat('Y-m-d', $date);

      if ($d && $d->format('Y-m-d') === $date) {
            return $date;
      }

      return false;
}

/**
 * Sanitiza e valida uma hora
 * @param string $time Hora a sanitizar
 * @return string|false Hora sanitizada ou false
 */
function sanitize_time($time)
{
      $time = trim($time);

      // Retornar no formato HH:MM
      if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            return $time;
      }

      // Se vier com segundos, converter para HH:MM
      if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $time)) {
            return substr($time, 0, 5);
      }

      return false;
}
