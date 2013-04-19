<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

use InvalidArgumentException;


/**
 * @package Test
 */
abstract class Test extends \PHPUnit_Framework_TestCase
{
  /**
   * The name of the overrides directory. All test-specific overrides must live here.
   *
   * @var string
   */
  const OVERRIDE_DIR = 'Overrides';


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
   */
  protected function addOverride($filename)
  {
    if (!is_string($filename) && empty($filename)) {
      throw new InvalidArgumentException('$filename is null, empty or not a string.');
    }

    // Get the base test directory
    $debugInfo = debug_backtrace(0, 1);

    if (isset($debugInfo[0]['file']) && preg_match('/^(\\/cis\\/lib\\/Gustavus\\/[^\\/]+)\\/.+$/', $debugInfo[0]['file'], $matches) === 1) {
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
   * Sets the given object property to be the value specified
   *
   * @param object $object
   * @param string $property
   * @param mixed $value
   * @return object
   */
  protected function set($object, $property, $value)
  {
    return TestLib::set($object, $property, $value);
  }

  /**
   * Sets up reflection property object
   *
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
   *
   * @param object $object
   * @param string $property
   * @return mixed
   */
  protected function get($object, $property)
  {
    return TestLib::get($object, $property);
  }

  /**
   * Calls protected or private method with $arguments as the arguments
   *
   * @param object|string $object
   * @param string $method
   * @param array $arguments
   */
  protected function call($object, $method, array $arguments = array())
  {
    return TestLib::call($object, $method, $arguments);
  }

  /**
   * @test
   */
  public function testsTest()
  {
    //so it doesn't complain about not having tests
  }

  /**
   * Gets the public get methods
   *
   * @param string $class
   * @return ReflectionMethod
   */
  protected function getGets($class)
  {
    return TestLib::getGets($class);
  }

  /**
   * Mocks authentication
   *
   * @param  string $username
   * @return
   */
  protected function authenticate($username)
  {
    return TestLib::authenticate($username);
  }

  /**
   * Mocks authentication logged out
   *
   * @return
   */
  protected function unAuthenticate()
  {
    return TestLib::unAuthenticate();
  }
}
