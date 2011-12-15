<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

/**
 * @package Test
 */
abstract class TestLib
{
  /**
   * sets the given object property to be the value specified
   * @param object $object
   * @param string $property
   * @param mixed $value
   * @return object
   */
  public static function setProperty($object, $property, $value)
  {
    $reflectionProperty = self::getReflectionProperty(get_class($object), $property);
    $reflectionProperty->setValue($object, $value);
    return $object;
  }

  /**
   * sets up reflection property object
   * @param string $class
   * @param string $property
   * @return ReflectionProperty
   */
  private static function getReflectionProperty($class, $property)
  {
    $reflectionProperty = new \ReflectionProperty($class, $property);
    $reflectionProperty->setAccessible(true);
    return $reflectionProperty;
  }

  /**
   * Gets the value of the property on the given class
   * @param object $object
   * @param string $property
   * @return mixed
   */
  public static function getProperty($object, $property)
  {
    return self::getReflectionProperty(get_class($object), $property)->getValue($object);
  }

  /**
   * call protected or private method with $param
   * @param object $object
   * @param string $method
   * @param mixed $param
   */
  public static function callMethod($object, $method, array $params = array())
  {
    $rClass = new \ReflectionClass(get_class($object));
    $rMethod = $rClass->getMethod($method);
    $rMethod->setAccessible(true);
    return $rMethod->invokeArgs($object, $params);
  }

  /**
   * gets the public get methods
   * @param string $class
   * @return ReflectionMethod
   */
  public static function getGets($class)
  {
    $reflection = new \ReflectionClass($class);
    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
    return $methods;
  }
}