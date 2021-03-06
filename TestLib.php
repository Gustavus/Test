<?php
/**
 * @package Test
 * @author Billy Visto
 */

namespace Gustavus\Test;

require_once 'gatekeeper/gatekeeper.class.php';

use Gustavus\Gatekeeper\Gatekeeper,
    Gustavus\GACCache\DataStoreFactory,
    Gustavus\GACCache\Workers\ArrayFactoryWorker,
    InvalidArgumentException;

/**
 * This needs to be separate because these functions are used by Test and TestDB but Test and TestDB
 * need to extend different classes in PHPUnit. Perhaps when traits are added to PHP, we will be
 * able to do this differently.
 *
 * <strong>Test Utility Locations</strong>
 * <ul>
 *   <li>Revisions entities for db creation: /cis/lib/Gustavus/Revisions/Test/Entities</li>
 *   <li>GACMailer test utility: /cis/lib/Gustavus/GACMailer/Test/MockMailer</li>
 *   <li>FakePerson test utility: /cis/lib/campus/Test/FakePerson</li>
 * </ul>
 *
 * @package Test
 * @author Billy Visto
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
      if (count($arguments) === 0) {
        // Temporary partial workaround for bug in PHP. It looks like this bug was fixed in PHP 5.3.11 so this code can be removed after upgrading to that version.
        // @link https://bugs.php.net/bug.php?id=60968
        return $rMethod->invoke(null);
      } else {
        return $rMethod->invokeArgs(null, $arguments);
      }
    } else {
      if (count($arguments) === 0) {
        // Temporary partial workaround for bug in PHP. It looks like this bug was fixed in PHP 5.3.11 so this code can be removed after upgrading to that version.
        // @link https://bugs.php.net/bug.php?id=60968
        return $rMethod->invoke($object);
      } else {
        return $rMethod->invokeArgs($object, $arguments);
      }
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

  /**
   * Mocks authentication
   *
   * @param  string         $username     Username to use.
   * @param  Campus\Person  $person       Campus person to set as gatekeeper's user
   * @param  string         $application  Name of application.
   * @param  array          $permissions  A 2D array of permissions with the 1D key being application.
   * @return
   */
  public static function authenticate($username, $person = null, array $permissions = null)
  {
    Gatekeeper::setUsername($username);
    static::set('\Gustavus\Gatekeeper\Gatekeeper', 'loggedIn', true);
    if (is_object($person)) {
      static::set('\Gustavus\Gatekeeper\Gatekeeper', 'user', $person);
    }
    if (isset($permissions)) {
      $newPermissions = [$username => $permissions];
      static::set('\Gustavus\Gatekeeper\Gatekeeper', 'permissions', $newPermissions);
    }
  }

  /**
   * Mocks authentication logged out
   *
   * @return
   */
  public static function unAuthenticate()
  {
    static::set('\Gustavus\Gatekeeper\Gatekeeper', 'user', null);
    static::set('\Gustavus\Gatekeeper\Gatekeeper', 'username', null);
    static::set('\Gustavus\Gatekeeper\Gatekeeper', 'permissions', array());
    static::set('\Gustavus\Gatekeeper\Gatekeeper', 'permissionsCache', array());
    static::set('\Gustavus\Gatekeeper\Gatekeeper', 'loggedIn', false);
  }

  /**
   * Saves the current state of the specified variable to be restored upon destruction of the
   * returned token.
   *
   * @param mixed &$var
   *  The variable for which to save the current state.
   *
   * @return DelayedExecutionToken
   *  A DelayedExecutionToken that will restore the variable's state upon destruction
   */
  public static function savestate(&$var)
  {
    $temp = $var;

    return new DelayedExecutionToken(function() use (&$var, $temp) {
      $var = $temp;
    });
  }

  /**
   * Tears down the environment for tests
   * @return void
   */
  public static function resetEnvironment()
  {
    static::unAuthenticate();
    static::set('\Gustavus\Template\Template', 'template', null);
    static::set('\Gustavus\Extensibility\Base', 'items', []);

    // make the global cache data store use array cache instead of memcache or anything any tests set it to use.
    static::set('\Gustavus\GACCache\GlobalCache', 'factory', new DataStoreFactory([new ArrayFactoryWorker()]));
    // verify that the datastore isn't set so it can build one off of our new factory
    static::set('\Gustavus\GACCache\GlobalCache', 'datastore', null);
  }
}