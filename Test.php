<?php
/**
 * @package Test
 * @author Billy Visto
 */

namespace Gustavus\Test;

use InvalidArgumentException,
  Gustavus\GACCache\Workers\ArrayFactoryWorker;

/**
 * Base test class to ease testing
 *
 * @package Test
 * @author Billy Visto
 */
abstract class Test extends \PHPUnit_Framework_TestCase
{
  /**
   * Override tokens
   *
   * @var array
   */
  protected static $testOverrides = [];

  /**
   * Storage for our initial server array
   *
   * @var array
   */
  private static $globalsStore = [];

  /**
   * Flag to specify if we turned on our notice handler
   *
   * @var boolean
   */
  private $noticeHandlerEnabled = false;

  /**
   * Flag to specify if a notice was triggered or not
   *
   * @var boolean
   */
  private $noticeTriggered = false;

  /**
   * Error string of the triggered notice
   *
   * @var string
   */
  private $noticeString;

  /**
   * Flag to specify if we turned on our warning handler
   *
   * @var boolean
   */
  private $warningHandlerEnabled = false;

  /**
   * Flag to specify if a warning was triggered or not
   *
   * @var boolean
   */
  private $warningTriggered = false;

  /**
   * Error string of the triggered warning
   *
   * @var string
   */
  private $warningString;

  /**
   * Tears down the environment after each test
   *
   * @return void
   */
  public function tearDown()
  {
    if ($this->noticeHandlerEnabled || $this->warningHandlerEnabled) {
      restore_error_handler();
      $this->noticeHandlerEnabled = false;
      $this->warningHandlerEnabled = false;
    }
    $this->noticeTriggered = false;
    $this->noticeString = null;

    $this->warningTriggered = false;
    $this->warningString = null;
  }

  /**
   * Sets up the environment before tests start in a class
   *
   * @return void
   */
  public static function setUpBeforeClass()
  {
    self::$globalsStore = [
      'server' => $_SERVER,
      'post'   => $_POST,
      'get'    => $_GET,
    ];
    $renderResourceToken = override_method('\Gustavus\Resources\Resource', 'renderResource',
        function($resourceName, $minified = true, $cssCrush = true, $includeHost = true) use (&$renderResourceToken) {
          $origDocRoot = $_SERVER['DOCUMENT_ROOT'];
          $_SERVER['DOCUMENT_ROOT'] = '/cis/www/';

          return call_overridden_func($renderResourceToken, null, $resourceName, $minified, $cssCrush, $includeHost);

          $_SERVER['DOCUMENT_ROOT'] = $origDocRoot;
        }
    );
    self::$testOverrides['renderResource'] = $renderResourceToken;

    $getGlobalDataStoreToken = override_method('\Gustavus\GACCache\GlobalCache', 'getGlobalDataStore', function() use(&$getGlobalDataStoreToken) {
      if (strpos(get_called_class(), '\\GACCache\\') && (!isset(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[0]['file']) ||  strpos(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[0]['file'], 'GACCache'))) {
        return call_overridden_func($getGlobalDataStoreToken, null);
      }
      return (new ArrayFactoryWorker())->buildDataStore();
    });
    self::$testOverrides['getGlobalDataStore'] = $getGlobalDataStoreToken;
  }

  /**
   * Tears down the environment.
   * <strong>Note:</strong> This won't get called if an extending class has tearDownAfterClass defined. That class would need to call parent::tearDownAfterClass.
   *
   * @return void
   */
  public static function tearDownAfterClass()
  {
    $_SERVER = self::$globalsStore['server'];
    $_POST   = self::$globalsStore['post'];
    $_GET    = self::$globalsStore['get'];
    self::$testOverrides = [];
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

  // Notice and Warning testing

  /**
   * Handles notices so we can test that user notices get triggered
   *
   * @param  integer $errno  Error number
   * @param  string $errstr Error string
   * @return void
   */
  public function handleNotice($errno, $errstr)
  {
    $this->noticeTriggered = true;
    $this->noticeString = $errstr;
    $this->assertSame(E_USER_NOTICE, $errno);
  }

  /**
   * Sets up our noticeHandler
   */
  protected function setUpNoticeHandler()
  {
    set_error_handler([$this, 'handleNotice'], E_USER_NOTICE);
    $this->noticeHandlerEnabled = true;
  }

  /**
   * Asserts that a notice was triggered and matches the specified string
   * @param  string $noticeString Notice message to test
   * @param  boolern $strict Whether to check if the noticeString is the same or is contained within the actual notice message
   *
   * @return void
   */
  protected function assertNoticeTriggered($noticeString, $strict = false)
  {
    $this->assertTrue($this->noticeTriggered);
    if ($strict) {
      $this->assertSame($noticeString, $this->noticeString);
    } else {
      $this->assertContains($noticeString, $this->noticeString);
    }
  }

  /**
   * Handles warnings so we can test that user warnings get triggered
   *
   * @param  integer $errno  Error number
   * @param  string $errstr Error string
   * @return void
   */
  public function handleWarning($errno, $errstr)
  {
    $this->noticeTriggered = true;
    $this->noticeString = $errstr;
    $this->assertSame(E_USER_WARNING, $errno);
  }

  /**
   * Sets up our noticeHandler
   */
  protected function setUpWarningHandler()
  {
    set_error_handler([$this, 'handleWarning'], E_USER_WARNING);
    $this->noticeHandlerEnabled = true;
  }

  /**
   * Asserts that a notice was triggered and matches the specified string
   * @param  string $warningString Warning message to test
   * @param  boolern $strict Whether to check if the noticeString is the same or is contained within the actual notice message
   *
   * @return void
   */
  protected function assertWarningTriggered($warningString, $strict = false)
  {
    $this->assertTrue($this->warningTriggered);
    if ($strict) {
      $this->assertSame($warningString, $this->warningString);
    } else {
      $this->assertContains($warningString, $this->warningString);
    }
  }
}
