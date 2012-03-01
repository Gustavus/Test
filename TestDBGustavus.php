<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

require_once 'db/GustavusDBToPDO.php';

/**
 * @package Test
 */
abstract class TestDBGustavus extends TestDB
{
  /**
   * @return GustavusDBToPDO
   */
  final protected function getDBH()
  {
    if (self::$dbh === null) {
      self::$dbh = new \Gustavus\DB\GustavusDBToPDO('sqlite::memory:');
    }

    return self::$dbh;
  }
}
