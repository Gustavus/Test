<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

/**
 * This needs to be separate because these functions are used by Test and TestDB but Test and TestDB need to extend different classes in PHPUnit. Perhaps when traits are added to PHP, we will be able to do this differently.
 * @package Test
 */
abstract class TestLib
{
  /**
   * @param object $object
   * @return string
   */
  private static function getClass($object)
  {
    if (is_object($object)) {
      return get_class($object);
    }

    return $object;
  }

  /**
   * Sets the given object property to be the value specified
   *
   * @param object $object
   * @param string $property
   * @param mixed $value
   * @return object
   */
  public static function set($object, $property, $value)
  {
    $reflectionProperty = self::getReflectionProperty(self::getClass($object), $property);
    $reflectionProperty->setValue($object, $value);
    return $object;
  }

  /**
   * Sets up reflection property object
   *
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
   *
   * @param object $object
   * @param string $property
   * @return mixed
   */
  public static function get($object, $property)
  {
    return self::getReflectionProperty(self::getClass($object), $property)->getValue($object);
  }

  /**
   * Call protected or private method with $arguments as the arguments
   *
   * @param object|string $object
   * @param string $method
   * @param array $arguments
   */
  public static function call($object, $method, array $arguments = array())
  {
    $rClass   = new \ReflectionClass($object);
    $rMethod  = $rClass->getMethod($method);
    $rMethod->setAccessible(true);

    if (is_string($object)) {
      return $rMethod->invokeArgs(null, $arguments);
    } else {
      return $rMethod->invokeArgs($object, $arguments);
    }
  }

  /**
   * Gets the public get methods
   *
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