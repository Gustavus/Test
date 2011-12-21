<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

/**
 * A class that bridges the Gustavus DB and PDO.
 *
 * Allows code that uses the Gustavus DB to use PDO behind-the-scenes without changing any code.
 *
 * @package Test
 */
class GustavusDBToPDO extends \PDO
{

  /**
   * @param string $sql
   * @param integer $cacheForSeconds
   * @param boolean $doDebug
   * @param boolean $multipleQueries
   * @return \PDOStatement
   */
  public function query($sql, $cacheForSeconds = 0, $doDebug = true, $multipleQueries = false)
  {
    return parent::query($sql);
  }

  /**
   * @param \PDOStatement
   * @return integer
   */
  public function rows(\PDOStatement $result)
  {
    return $result->rowCount();
  }

  /**
   * @param \PDOStatement
   * @return integer
   */
  public function affected(\PDOStatement $result)
  {
    return $this->rows($result);
  }

  /**
   * @param \PDOStatement
   * @return array
   */
  public function assoc(\PDOStatement $result)
  {
    return $result->fetch(\PDO::FETCH_ASSOC);
  }

  /**
   * @param \PDOStatement
   * @return array
   */
  public function all(\PDOStatement $result)
  {
    return $result->fetchAll();
  }

  /**
   * @param string $sql
   * @return \PDOStatement
   */
  public function prepare($sql)
  {
    return parent::prepare($sql);
  }

  /**
   * @param \PDOStatement $stmt
   * @return void
   */
  public function execute(\PDOStatement $stmt)
  {
    $stmt->execute();
  }

  /**
   * @param \PDOStatement $stmt
   * @param array $parameters
   * @return boolean
   */
  public function bindArray(\PDOStatement $stmt, array &$parameters)
  {
    $i = 1;
    foreach ($parameters as &$parameter) {
      $stmt->bindParam($i, $parameter);
      ++$i;
    }

    return true;
  }

  /**
   * Escapes single quotes in a string to be used in a database query
   *
   * @param string|array $stringOrArray A string or an array of strings to escape single quotes in
   * @return string|array
   */
  public function escape($stringOrArray)
  {
    // escape single quotes
    if (is_array($stringOrArray)) {
      // this is an array so we want to escape single quotes on each item
      return array_map(array($this, 'escape'), $stringOrArray);
    } else {
      return $this->quote($stringOrArray);
    }
  }
}
