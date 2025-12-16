<?php
/**
 * Gestão de Sessões
 * Funções para gerir sessões de forma segura
 */

// Prevenir acesso direto
if (!defined('SAW_APP')) {
      die('Acesso negado');
}

/**
 * Inicia a sessão de forma segura
 */
function session_start_secure()
{
      // Configurações de segurança da sessão
      ini_set('session.cookie_httponly', 1);
      ini_set('session.use_only_cookies', 1);
      ini_set('session.cookie_secure', 0); // Alterar para 1 em HTTPS
      ini_set('session.cookie_samesite', 'Lax');

      // Iniciar sessão se ainda não estiver iniciada
      if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();

            // Regenerar ID da sessão periodicamente
            if (!isset($_SESSION['created'])) {
                  session_regenerate_id(true);
                  $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > 1800) { // 30 minutos
                  session_regenerate_id(true);
                  $_SESSION['created'] = time();
            }
      }
}

/**
 * Destrói a sessão
 */
function session_destroy_secure()
{
      // Limpar variáveis de sessão
      $_SESSION = [];

      // Eliminar cookie de sessão
      if (isset($_COOKIE[SESSION_NAME])) {
            setcookie(SESSION_NAME, '', time() - 3600, '/');
      }

      // Destruir sessão
      session_destroy();
}

/**
 * Define um valor na sessão
 * @param string $key Chave
 * @param mixed $value Valor
 */
function session_set($key, $value)
{
      $_SESSION[$key] = $value;
}

/**
 * Obtém um valor da sessão
 * @param string $key Chave
 * @param mixed $default Valor padrão se não existir
 * @return mixed
 */
function session_get($key, $default = null)
{
      return $_SESSION[$key] ?? $default;
}

/**
 * Verifica se uma chave existe na sessão
 * @param string $key Chave
 * @return bool
 */
function session_has($key)
{
      return isset($_SESSION[$key]);
}

/**
 * Remove um valor da sessão
 * @param string $key Chave
 */
function session_remove($key)
{
      if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
      }
}

/**
 * Verifica se a sessão expirou
 * @return bool
 */
function session_is_expired()
{
      if (isset($_SESSION['last_activity'])) {
            $inactive_time = time() - $_SESSION['last_activity'];

            if ($inactive_time > SESSION_LIFETIME) {
                  return true;
            }
      }

      $_SESSION['last_activity'] = time();
      return false;
}

// Iniciar sessão automaticamente
session_start_secure();

// Verificar expiração
if (session_is_expired()) {
      session_destroy_secure();
      session_start_secure();
}
