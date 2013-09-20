<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

require_once 'gatekeeper/gatekeeper.class.php';

use Gustavus\Gatekeeper\Gatekeeper,

    InvalidArgumentException;

/**
 * This needs to be separate because these functions are used by Test and TestDB but Test and TestDB need to extend different classes in PHPUnit. Perhaps when traits are added to PHP, we will be able to do this differently.
 * @package Test
 */
abstract class TestLib
{
  /**
   * The name of the overrides directory. All test-specific overrides must live here.
   *
   * @var string
   */
  const OVERRIDE_DIR = 'Overrides';

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
   * @param  string $username
   * @param  Campus\Person $person Campus person to set as gatekeeper's user
   * @return
   */
  public static function authenticate($username, $person = null)
  {
    Gatekeeper::setUsername($username);
    static::set('\Gustavus\Gatekeeper\Gatekeeper', 'loggedIn', true);
    if (is_object($person)) {
      static::set('\Gustavus\Gatekeeper\Gatekeeper', 'user', $person);
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
   * Includes the override file specified. If the file has already been included, this method does
   * nothing.
   *
   * When including overrides, if the caller originates from a library within the Gustavus
   * repository, the file will be included from Gustavus/Project/Test/Overrides. Otherwise, the
   * file will be included from the current working directory when called.
   *
   * @param string $filename
   *  The name of the override file to include, without the file extension.
   *
   * @throws InvalidArgumentException
   *  if $filename is null, empty or not a string, or if the override file specified cannot be read.
   *
   * @return void
   */
  public static function addOverride($filename)
  {
    // @todo:
    // Replace this with some fancy reflection, closure and function injection so it can be
    // programatically reverted when the override is no longer needed (ie: In the case of filtering
    // tests).

    if (!is_string($filename) && empty($filename)) {
      throw new InvalidArgumentException('$filename is null, empty or not a string.');
    }

    // Get the base test directory
    $debugInfo = debug_backtrace(0);

    // Jump past any internal calls from other classes in the Test package...
    while (isset($debugInfo[0]['class']) && preg_match('/\\A\\/cis\\/lib\\/Gustavus\\/Test\\/.+\\z/', $debugInfo[0]['file']) === 1) {
      array_shift($debugInfo);
    }

    if (isset($debugInfo[0]['file']) && preg_match('/\\A(\\/cis\\/lib\\/Gustavus\\/[^\\/]+)\\/.+$\\z/', $debugInfo[0]['file'], $matches) === 1) {
      $base = $matches[1] . DIRECTORY_SEPARATOR . 'Test';
    } else {
      // Whelp... Hope for the best here.
      $base = getcwd();
    }

    // Build an intended target and make sure it's actually a file and can be read
    $target = sprintf('%2$s%1$s%3$s%1$s%4$s.php', DIRECTORY_SEPARATOR, $base, self::OVERRIDE_DIR, $filename);

    if (!is_file($target) || !is_readable($target)) {
      throw new InvalidArgumentException('Target override file does not exist, is not a file or is not readable: ' . $target);
    }

    require_once($target);
  }

  /**
   * Sets GACMailer's instance to be our test instance
   *
   * @return mixed
   */
  public static function setupGACMailerTest()
  {
    self::set('\Gustavus\GACMailer\GACMailer', 'instance', new TestGacMailerInstance);
  }

  /**
   * Gets the sent message from our test instance
   *
   * @return Gustavus\GACMailer\EmailMessage
   */
  public static function getSentMessage()
  {
    return TestGacMailerInstance::$sentMessage;
  }
}