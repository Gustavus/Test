<?php
/**
 * @package Test
 * @author  Billy Visto
 */

namespace Gustavus\Test;

use Gustavus\Test\TestDBPDO,
  Gustavus\Doctrine\EntityManager,
  Doctrine\ORM\Tools\SchemaTool;

/**
 * @package Test
 * @author  Billy Visto
 */
class TestEM extends TestDBPDO
{
  /**
   * EntityManager to be used in tests
   *
   * @var EntityManager
   */
  protected $entityManager;

  /**
   * sets up the object for each test
   * @return void
   */
  public function setUp()
  {
  }

  /**
   * destructs the object after each test
   * @return void
   */
  public function tearDown()
  {
  }

  /**
   * Sets up the EntityManager if needed.
   *
   * @param  string $entityLocation Path to entities' parent
   * @return EntityManager
   */
  protected function getEntityManager($entityLocation)
  {
    if (!isset($this->entityManager)) {
      $this->entityManager = EntityManager::getEntityManager($entityLocation, $this->getDBH(), 'testDB');
    }
    return $this->entityManager;
  }

  /**
   * Sets up DB from the entities
   *
   * @param  string $entityLocation Path to entities' parent
   * @param array $classes array of entityClasses to create
   * @return  void
   */
  protected function setUpDB($entityLocation, array $classes)
  {
    $tools = new SchemaTool($this->getEntityManager($entityLocation));
    foreach ($classes as &$class) {
      $class = $this->entityManager->getClassMetadata($class);
    }

    return $tools->updateSchema($classes);
  }

  /**
   * Destroys DB for the entities specified
   *
   * @param  string $entityLocation Path to entities' parent
   * @param  array $classes array of entityClasses to remove
   * @return  void
   */
  protected function destroyDBClasses($entityLocation, array $classes)
  {
    $tools = new SchemaTool($this->getEntityManager($entityLocation));
    $classMetaData = [];
    foreach ($classes as $class) {
      $classMentaData[] = $this->getEntityManager()->getClassMetadata($class);
    }

    return $tools->dropSchema($classes);
  }

  /**
   * Destroys DB for all the entities in the entityLocation's Entities folder
   *
   * @param  string $entityLocation Path to entities' parent
   * @return  void
   */
  protected function destroyDB($entityLocation)
  {
    $tools = new SchemaTool($this->getEntityManager($entityLocation));
    return $tools->dropDatabase();
  }
}