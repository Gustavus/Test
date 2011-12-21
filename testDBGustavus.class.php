<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

require_once 'testDB.class.php';
require_once 'gustavusDBToPDO.class.php';

/**
 * @package Test
 */
abstract class TestDBGustavus extends TestDB
{
  /**
   * @return GustavusDBToPDO
   */
  protected function getDBH()
  {
    if (self::$dbh === null) {
      self::$dbh = new GustavusDBToPDO('sqlite::memory:');
    }

    return self::$dbh;
  }
}
