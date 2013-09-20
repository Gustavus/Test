<?php
/**
 * @package Test
 * @subpackage Test
 * @author  Billy Visto
 */

namespace Gustavus\Test\Test;

use Gustavus\GACMailer\EmailMessage,
  Gustavus\Test\TestLib;

/**
 * Class for testing TestLib
 *
 * @package Test
 * @subpackage Test
 * @author  Billy Visto
 */
class TestLibTest extends \PHPUnit_Framework_TestCase
{
  public function setUp()
  {
  }

  public function tearDown()
  {
  }

  /**
   * @test
   */
  public function setupGacMailerTest()
  {
    TestLib::setupGacMailerTest();

    $message = (new EmailMessage)
      ->setSubject('Test')
      ->setFrom('test@gustavus.edu')
      ->setTo('web@gustavus.edu')
      ->setReplyTo('no-reply@gustavus.edu')
      ->setBody('Test body');

    $count = $message->send();
    $sentMessage = TestLib::getSentMessage();

    $this->assertSame('Test', $sentMessage->getSubject());
    $this->assertSame('Test body', $sentMessage->getBody());
    $this->assertSame(['web@gustavus.edu' => null], $sentMessage->getTo());
    $this->assertSame(1, $count);
  }
}
