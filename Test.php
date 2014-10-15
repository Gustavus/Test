<?php
/**
 * @package Test
 * @author Billy Visto
 */

namespace Gustavus\Test;

use InvalidArgumentException;


/**
 * Base test class to ease testing
 *
 * @package Test
 * @author Billy Visto
 */
abstract class Test extends \PHPUnit_Framework_TestCase
{
  /**
   * Tears down the environment.
   * <strong>Note:</strong> This won't get called if an extending class has tearDownAfterClass defined. That class would need to call parent::tearDownAfterClass.
   *
   * @return void
   */
  public static function tearDownAfterClass()
  {
    TestLib::resetEnvironment();
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
   * @coversNothing
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
   * @param  string         $username     Username to use.
   * @param  Campus\Person  $person       Campus person to set as gatekeeper's user
   * @param  string         $application  Name of application.
   * @param  array          $permissions  A 2D array of permissions with the 1D key being application.
   * @return
   */
  protected function authenticate($username, $person = null, array $permissions = null)
  {
    return TestLib::authenticate($username, $person, $permissions);
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
  protected function savestate(&$var)
  {
    return TestLib::savestate($var);
  }
}
