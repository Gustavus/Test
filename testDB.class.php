<?php
/**
 * @package Test
 */

namespace Gustavus\Test;

require_once 'testlib.class.php';

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
   * sets the given object property to be the value specified
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
   * sets up reflection property object
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
   * @param object $object
   * @param string $property
   * @return mixed
   */
  protected function get($object, $property)
  {
    return TestLib::get($object, $property);
  }

  /**
   * call protected or private method with $param
   * @param object|string $object
   * @param string $method
   * @param mixed $param
   */
  protected function call($object, $method, array $params = array())
  {
    return TestLib::call($object, $method, $params);
  }

  /**
   * @test
   */
  public function testsTest()
  {
    //so it doesn't complain about not having tests
  }

  /**
   * gets the public get methods
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
   * sets a mock object to use our test db
   * wherever the tested class uses the db, the db should be returned from a getDB function so we can mock that function
   *
   * @param string $class
   * @param string $method
   * @param array $constructorParams
   * @return mock object
   */
  protected function getMockWithDB($class, $method, array $constructorParams = null, $returnValue = null)
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
   * makes table from expected dataset into db made from get connection
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
      $sql = "CREATE TABLE IF NOT EXISTS $tableName ($id";
      $sql .= implode('` VARCHAR, `', $columns);
      $sql .= "` VARCHAR);";
      var_dump($sql);
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
      $stmt = $this->getDBH()->prepare("DROP TABLE $tableName");
      $stmt->execute();
    }
  }
}
