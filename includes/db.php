<?php
/**
 * Conexão à Base de Dados
 * Utiliza mysqli para conexão segura
 */

// Prevenir acesso direto
if (!defined('SAW_APP')) {
      die('Acesso negado');
}

// Variável global para a conexão
$conn = null;

/**
 * Estabelece conexão com a base de dados
 * @return mysqli|null
 */
function db_connect()
{
      global $conn;

      if ($conn !== null) {
            return $conn;
      }

      try {
            // Criar conexão
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // Verificar conexão
            if ($conn->connect_error) {
                  throw new Exception("Erro de conexão: " . $conn->connect_error);
            }

            // Definir charset
            if (!$conn->set_charset(DB_CHARSET)) {
                  throw new Exception("Erro ao definir charset: " . $conn->error);
            }

            return $conn;

      } catch (Exception $e) {
            error_log("DB Error: " . $e->getMessage());
            if (DEBUG_MODE) {
                  die("Erro de base de dados: " . $e->getMessage());
            } else {
                  die("Erro ao conectar à base de dados. Por favor, tente mais tarde.");
            }
      }
}

/**
 * Fecha a conexão com a base de dados
 */
function db_close()
{
      global $conn;

      if ($conn !== null) {
            $conn->close();
            $conn = null;
      }
}

/**
 * Executa uma query preparada
 * @param string $query Query SQL com placeholders (?)
 * @param string $types Tipos dos parâmetros (s=string, i=int, d=double, b=blob)
 * @param array $params Array com os parâmetros
 * @return mysqli_result|bool
 */
function db_query($query, $types = '', $params = [])
{
      global $conn;

      if ($conn === null) {
            db_connect();
      }

      try {
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                  throw new Exception("Erro ao preparar query: " . $conn->error);
            }

            // Bind parameters se existirem
            if (!empty($types) && !empty($params)) {
                  $stmt->bind_param($types, ...$params);
            }

            // Executar
            if (!$stmt->execute()) {
                  throw new Exception("Erro ao executar query: " . $stmt->error);
            }

            // Retornar resultado
            $result = $stmt->get_result();
            $stmt->close();

            return $result;

      } catch (Exception $e) {
            error_log("Query Error: " . $e->getMessage() . " | Query: " . $query);
            if (DEBUG_MODE) {
                  die("Erro de query: " . $e->getMessage());
            }
            return false;
      }
}

/**
 * Executa uma query de inserção/atualização/eliminação
 * @param string $query Query SQL com placeholders (?)
 * @param string $types Tipos dos parâmetros
 * @param array $params Array com os parâmetros
 * @return int|bool ID inserido ou número de linhas afetadas, false em erro
 */
function db_execute($query, $types = '', $params = [])
{
      global $conn;

      if ($conn === null) {
            db_connect();
      }

      try {
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                  throw new Exception("Erro ao preparar query: " . $conn->error);
            }

            // Bind parameters se existirem
            if (!empty($types) && !empty($params)) {
                  $stmt->bind_param($types, ...$params);
            }

            // Executar
            if (!$stmt->execute()) {
                  throw new Exception("Erro ao executar query: " . $stmt->error);
            }

            // Retornar ID inserido ou linhas afetadas
            $affected = $stmt->affected_rows;
            $insert_id = $stmt->insert_id;
            $stmt->close();

            return $insert_id > 0 ? $insert_id : $affected;

      } catch (Exception $e) {
            error_log("Execute Error: " . $e->getMessage() . " | Query: " . $query);
            if (DEBUG_MODE) {
                  die("Erro de execução: " . $e->getMessage());
            }
            return false;
      }
}

/**
 * Escapa uma string para uso seguro em queries
 * @param string $string
 * @return string
 */
function db_escape($string)
{
      global $conn;

      if ($conn === null) {
            db_connect();
      }

      return $conn->real_escape_string($string);
}

// Inicializar conexão
db_connect();
