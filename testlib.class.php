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
  public static function set($object, $property, $value)
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
  public static function get($object, $property)
  {
    return self::getReflectionProperty(get_class($object), $property)->getValue($object);
  }

  /**
   * call protected or private method with $param
   * @param object|string $object
   * @param string $method
   * @param mixed $param
   */
  public static function call($object, $method, array $params = array())
  {
    $rClass   = new \ReflectionClass($object);
    $rMethod  = $rClass->getMethod($method);
    $rMethod->setAccessible(true);

    if (is_string($object)) {
      return $rMethod->invokeArgs(null, $params);
    } else {
      return $rMethod->invokeArgs($object, $params);
    }
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