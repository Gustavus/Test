<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

require_once __DIR__ . '/TestObject.php';

/**
 * @package Test
 */
class TestObjectMock extends TestObject
{
  /**
   * @param string $className
   * @param array $methodsToMock
   */
  public function __construct($className, array $methodsToMock = null)
  {
    $this->object = $this->getMock($className, $methodsToMock);
  }
}
