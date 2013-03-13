<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

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
    if (!is_string($className)) {
      throw new \InvalidArgumentException('First parameter when constructing TestObjectMock must be a string.');
    }

    $this->object = $this->getMock($className, $methodsToMock);
  }
}
