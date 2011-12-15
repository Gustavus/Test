<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

require_once 'testlib.class.php';

/**
 * @package Test
 */
abstract class Test extends \PHPUnit_Framework_TestCase
{
  /**
   * sets the given object property to be the value specified
   * @param object $object
   * @param string $property
   * @param mixed $value
   * @return object
   */
  protected function setProperty($object, $property, $value)
  {
    return TestLib::setProperty($object, $property, $value);
  }

  /**
   * sets up reflection property object
   * @param string $class
   * @param string $property
   * @return ReflectionProperty
   */
  private function getReflectionProperty($class, $property)
  {
    return TestLib::getReflectionProperty($class, $property);
  }

  /**
   * Gets the value of the property on the given class
   * @param object $object
   * @param string $property
   * @return mixed
   */
  protected function getProperty($object, $property)
  {
    return TestLib::getProperty($object, $property);
  }

  /**
   * call protected or private method with $param
   * @param object $object
   * @param string $method
   * @param mixed $param
   */
  protected function callMethod($object, $method, array $params = array())
  {
    return TestLib::callMethod($object, $method, $params);
  }

  /**
   * @test
   */
  public function testsTest()
  {
    //so it doesn't complain about not having tests
  }

  /**
   * gets the public get methods
   * @param string $class
   * @return ReflectionMethod
   */
  protected function getGets($class)
  {
    return TestLib::getGets($class);
  }
}
