<?php
/**
 * @package Test
 * @author Billy Visto
 */

namespace Gustavus\Test;

use  Gustavus\GACCache\Workers\ArrayFactoryWorker;

/**
 * Base test class to ease testing with db connections
 *
 * @package Test
 * @author Billy Visto
 */
abstract class TestDB extends \PHPUnit_Extensions_Database_TestCase
{

  /**
   * @var \PDO
   */
  protected static $dbh;

  /**
   * @var array of created tables
   */
  private $createdTableNames = [];

  /**
   * @var \PHPUnit_Extensions_Database_DB_IDatabaseConnection
   */
  private $connection;

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
  private static $globalsStore;

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
   * Gets the database connection to use
   *
   * @return \PDO PDO connection
   */
  abstract protected function getDBH();

  /**
   * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
   */
  protected function getConnection()
  {
    if ($this->connection === null) {
      $this->connection = $this->createDefaultDBConnection($this->getDBH(), ':memory:');
    }

    return $this->connection;
  }

  /**
   * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
   */
  protected function getDataSet()
  {
    // This function should be overridden by the extending class
    return $this->getConnection()->createDataSet(array());
  }

  /**
   * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
   * @param array $filterArray keyed by table name, values are array of columns to be filtered out
   * @return PHPUnit_Extensions_Database_DataSet_DataSetFilter
   */
  protected function getFilteredDataSet($dataSet, array $filterArray)
  {
    return new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet, $filterArray);
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
      if (strpos(get_called_class(), '\\GACCache\\')) {
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
  public function addOverride($filename)
  {
    return TestLib::addOverride($filename);
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
   * @param string $dbName
   * @param string $tableName
   * @return array
   */
  protected function getDBTableColumns($dbName, $tableName)
  {
    require_once '/cis/lib/db/db.class.php';
    $db = \DB::_($dbName);

    $sql = sprintf('
      SELECT DISTINCT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = \'%1$s\'',
        $tableName
    );
    $stmt = $db->prepare($sql);
    $db->execute($stmt);
    $stmt->bind_result($column);

    $expected = array();
    while ($stmt->fetch()) {
      $expected[] = $column;
    }

    return $expected;
  }

  /**
   * Sets a mock object to use our test db
   *
   * Wherever the tested class uses the db, the db should be returned from a non private getDB function so we can mock that function
   *
   * @param string $class Fully qualified name of the class to mock (e.g. '\Gustavus\Project\Class')
   * @param string $method Name of method that returns the database handle (e.g. 'getDBH')
   * @param array $constructorParams
   * @param mixed $returnValue
   * @return mock object
   */
  protected function getMockWithDB($class, $method, array $constructorParams = array(), $returnValue = null)
  {
    $this->getConnection();

    $return = ($returnValue === null) ? $this->getDBH() : $returnValue;
    $dbMock = $this->getMock($class, array($method), $constructorParams);
    $dbMock->expects($this->any())
      ->method($method)
      ->will($this->returnValue($return));
    return $dbMock;
  }

  /**
   * Makes table from expected dataset into db made from get connection
   *
   * @param PHPUnit Dataset $expected
   * @param array $tableNames
   * @param DBConnection $connection
   */
  protected function setUpDBFromDataset($expected, array $tableNames = null)
  {
    $this->getConnection();
    if ($tableNames === null) {
      $tableNames = $expected->getTableNames();
    }
    foreach ($tableNames as $tableName) {
      $tableMetaData = $expected->getTableMetaData($tableName);
      $columns = $tableMetaData->getColumns();
      $id = '';
      if ($columns[0] === "id") {
        $id = '`'.array_shift($columns).'`';
        $id .= ' INTEGER PRIMARY KEY, `';
      }
      $sql = "CREATE TABLE IF NOT EXISTS `$tableName` ($id";
      $sql .= implode('` VARCHAR, `', $columns);
      $sql .= "` VARCHAR);";

      $stmt = $this->getDBH()->prepare($sql);
      $stmt->execute();
    }
    $this->createdTableNames = array_merge($this->createdTableNames, $tableNames);
  }

  /**
   * Drops tables created so you are working with a fresh db for each test if you call this function
   *
   * @param array $tableNames
   * @return void
   */
  protected function dropCreatedTables(array $tableNames = null)
  {
    if ($tableNames === null) {
      $tableNames = $this->createdTableNames;
      //reset createdTableNames
      $this->createdTableNames = array();
    }
    foreach ($tableNames as $tableName) {
      $stmt = $this->getDBH()->prepare("DROP TABLE `$tableName`");
      $stmt->execute();
    }
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
