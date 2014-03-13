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
    if (self::$dbh === null) {
      self::$dbh = new \PDO('sqlite::memory:');
    }

    return self::$dbh;
  }
}
