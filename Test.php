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
   * @param  Campus\Person $person Campus person to set as gatekeeper's user
   * @return
   */
  protected function authenticate($username, $person = null)
  {
    return TestLib::authenticate($username, $person);
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

  /**
   * Sets GACMailer's instance to be our test instance
   *
   * @return mixed
   */
  protected function setupGACMailerTest()
  {
    return TestLib::setupGACMailerTest();
  }

  /**
   * Gets the sent message from our test instance
   *
   * @return Gustavus\GACMailer\EmailMessage
   */
  protected function getSentMessage()
  {
    return TestGacMailerInstance::$sentMessage;
  }
}
