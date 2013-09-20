<?php
/**
 * @package  Test
 * @author  Billy Visto
 */

namespace Gustavus\Test;

use Gustavus\Test\TestLib;

/**
 * Class used for testing email messages
 *
 * @package  Test
 * @author  Billy Visto
 */
class TestGacMailerInstance
{
  /**
   * Sent email message from GACMailer
   *
   * @var Gustavus\GACMailer\EmailMessage
   */
  public static $sentMessage;

  /**
   * Intercepts send from GACMailer::getInstance()->send() for testing
   *   Sets a static property in TestBase to get the message properties
   *
   * @param  Gustavus\GACMailer\EmailMessage $message   Message to be sent
   * @param  array $failedRecipients
   * @return integer Supposed to be number of people email was sent to. I'm just doing 2.
   */
  public function send($message, $failedRecipients)
  {
    self::$sentMessage = $message;
    $recipients = [];
    foreach (['to', 'cc', 'bcc'] as $recipientType) {
      $getter = 'get' . ucFirst($recipientType);
      if ((${$recipientType} = $message->{$getter}()) !== null) {
        $recipients = array_merge($recipients, ${$recipientType});
      }
    }
    return count($recipients);
  }
}