<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

require_once 'Gustavus/Test/TestDB.class.php';
require_once 'Gustavus/Test/GustavusDBToPDO.class.php';

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
      self::$dbh = new GustavusDBToPDO('sqlite::memory:');
    }

    return self::$dbh;
  }
}
