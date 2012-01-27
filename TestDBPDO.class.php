<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

require_once 'Gustavus/Test/TestDB.class.php';

/**
 * @package Test
 */
abstract class TestDBPDO extends TestDB
{
  /**
   * @return \PDO
   */
  final protected function getDBH()
  {
    if (self::$dbh === null) {
      self::$dbh = new \PDO('sqlite::memory:');
    }

    return self::$dbh;
  }
}
