<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

/**
 * @package Test
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
  private $createdTableNames = array();

  /**
   * @var \PHPUnit_Extensions_Database_DB_IDatabaseConnection
   */
  private $connection;

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
