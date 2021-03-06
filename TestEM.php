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
    unset($this->entityManager);
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
   * Sets up the EntityManager if needed.
   *
   * @param  string $entityLocation Path to entities' parent
   * @param  boolean $new  Whether to get a new entity manager or not
   * @return EntityManager
   */
  protected function getEntityManager($entityLocation, $new = false)
  {
    if ($new) {
      $this->newEntityManager = EntityManager::getEntityManager($entityLocation, $this->getDBH(), 'testDB');
      return $this->newEntityManager;
    }
    if (!isset($this->entityManager)) {
      $this->entityManager = EntityManager::getEntityManager($entityLocation, $this->getDBH(), 'testDB');
    }
    return $this->entityManager;
  }

  /**
   * Gets the new entity manager generated in getEntityManager with $new = true. Or generates a new one itself if a new one hasn't been set yet.
   *
   * @param  string $entityLocation Path to directory containing the entities folder
   * @return EntityManager
   */
  protected function getNewEntityManager($entityLocation)
  {
    if (!isset($this->newEntityManager)) {
      $this->getEntityManager($entityLocation, true);
    }
    return $this->newEntityManager;
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
      $class = $this->getEntityManager($entityLocation)->getClassMetadata($class);
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
    foreach ($classes as &$class) {
      $class = $this->getEntityManager($entityLocation)->getClassMetadata($class);
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