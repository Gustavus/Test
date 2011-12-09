<?php
/**
 * @package Test
 */
class Test extends PHPUnit_Framework_TestCase
{
  /**
   * sets the given object property to be the value specified
   * @param string $class
   * @param object $object
   * @param string $property
   * @param mixed $value
   * @return object
   */
  protected function setReflectionProperties($class, $object, $property, $value)
  {
    $reflectionProperty = $this->getReflectionProperty($class, $property);
    $reflectionProperty->setValue($object, $value);
    return $object;
  }

  /**
   * sets up reflection property object
   * @param string $class
   * @param string $property
   * @return ReflectionProperty
   */
  protected function getReflectionProperty($class, $property)
  {
    $reflectionProperty = new ReflectionProperty($class, $property);
    $reflectionProperty->setAccessible(true);
    return $reflectionProperty;
  }

  /**
   * Gets the value of the property on the given class
   * @param string $class
   * @param object $object
   * @param string $property
   * @return mixed
   */
  protected function getPropertyValue($class, $object, $property)
  {
    return $this->getReflectionProperty($class, $property)->getValue($object);
  }

  /**
   * call protected or private method with $param
   * @param string $class
   * @param object $object
   * @param string $method
   * @param mixed $param
   */
  protected function callReflectionMethod($class, $object, $method, array $params = array())
  {
    $rClass = new ReflectionClass($class);
    $rMethod = $rClass->getMethod($method);
    $rMethod->setAccessible(true);
    return $rMethod->invokeArgs($object, $params);
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
    $reflection = new \ReflectionClass($class);
    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
    return $methods;
  }

  /**
   * @param string $dbName
   * @param string $tableName
   * @return array
   */
  protected function getDBTableColumns($dbName, $tableName)
  {
    $expected = null;
    require_once '/cis/lib/db/db.class.php';
    $db = \DB::_($dbName);
    $sql = sprintf('
      SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = \'%1$s\'',
        $tableName
    );
    $stmt = $db->prepare($sql);
    $db->execute($stmt);
    $stmt->bind_result($column);
    while ($stmt->fetch()) {
      $expected[] = $column;
    }
    return $expected;
  }
}