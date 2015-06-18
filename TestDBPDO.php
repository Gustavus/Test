<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

/**
 * @package Test
 */
abstract class TestDBPDO extends TestDB
{
  /**
   * {@inheritdoc}
   */
  final protected function getDBH()
  {
    return self::getDBHStatic();
  }

  /**
   * Static implementation of getDBH()
   */
  final static protected function getDBHStatic()
  {
    if (self::$dbh === null) {
      self::$dbh = new \PDO('sqlite::memory:');
    }

    return self::$dbh;
  }
}
