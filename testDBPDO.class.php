<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

require_once 'testDB.class.php';

/**
 * @package Test
 */
abstract class TestDBPDO extends TestDB
{
  /**
   * @return \PDO
   */
  protected function getDBH()
  {
    if (self::$dbh === null) {
      self::$dbh = new \PDO('sqlite::memory:');
    }

    return self::$dbh;
  }
}
