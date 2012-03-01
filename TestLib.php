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
   * Gets the class name that contains the property.
   *
   * This is useful for private properties on parent classes that are extended.
   *
   * @param string $class
   * @param string $property
   * @return string Class that contains the property, false on failure
   */
  private static function getClassWithProperty($class, $property)
  {
    if (property_exists($class, $property)) {
      return $class;
    } else if ($class = get_parent_class($class)) {
      return self::getClassWithProperty($class, $property);
    } else {
      return false;
    }
  }

  /**
   * Sets up reflection property object
   *
   * @param string $class
   * @param string $property
   * @return \ReflectionProperty
   */
  private static function getReflectionProperty($class, $property)
  {
    if ($classWithProperty = self::getClassWithProperty($class, $property)) {
      $reflectionProperty = new \ReflectionProperty($classWithProperty, $property);
      $reflectionProperty->setAccessible(true);
      return $reflectionProperty;
    } else {
      throw new \ReflectionException("Property $class::$property does not exist");
    }
  }

  /**
   * Sets the given object property to be the value specified
   *
   * @param object|string $class
   * @param string $property
   * @param mixed $value
   * @return mixed
   */
  public static function set($class, $property, $value)
  {
    $reflectionProperty = self::getReflectionProperty(self::getClass($class), $property);
    $reflectionProperty->setValue($class, $value);

    return $class;
  }

  /**
   * Gets the value of the property on the given class
   *
   * @param object|string $class
   * @param string $property
   * @return mixed
   */
  public static function get($class, $property)
  {
    return self::getReflectionProperty(self::getClass($class), $property)->getValue($class);
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