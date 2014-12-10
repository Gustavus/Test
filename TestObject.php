<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

/**
 * @package Test
 */
class TestObject
{
  /**
   * @var object
   */
  protected $object;

  /**
   * @param object $object
   */
  public function __construct($object)
  {
    $this->object = $object;
  }

  /**
   * Returns the object we are testing
   *
   * @return mixed
   */
  public function getTestObject()
  {
    return $this->object;
  }

  /**
   * @param string $method
   * @param array $arguments
   * @return mixed
   */
  public function __call($method, array $arguments)
  {
    if (is_callable(array($this->object, $method))) {
      return call_user_func_array(array($this->object, $method), $arguments);
    } else {
      return TestLib::call($this->object, $method, $arguments);
    }
  }

  /**
   * @param string $property
   * @param mixed $value
   * @return mixed
   */
  public function __set($property, $value)
  {
    if (is_callable(array($this->object, '__set'))) {
      return $this->object->__set($property, $value);
    } else {
      return TestLib::set($this->object, $property, $value);
    }
  }

  /**
   * @param string $property
   * @return mixed
   */
  public function __get($property)
  {
    if (is_callable(array($this->object, '__get'))) {
      return $this->object->__get($property);
    } else {
      return TestLib::get($this->object, $property);
    }
  }
}
