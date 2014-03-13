<?php
/**
 * DelayedExecutionToken.php
 *
 * @package Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Test;




/**
 * The DelayedExecutionToken delays an action by taking advantage of PHP's fast garbage collection
 * to execute a callback when the token goes out of scope and is destructed.
 *
 * Note:
 *  Due to the mechanism used to execute the callback, any value returned will be silently
 *  discarded. As such, it may be necessary to wrap a callback in another callback to process the
 *  return value.
 *
 * @package Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
class DelayedExecutionToken
{
  /**
   * The callback to execute.
   *
   * @var callable
   */
  protected $callback;

  /**
   * The arguments to pass to the callback. If there are no arguments, this variable will be an
   * empty array.
   *
   * @var array
   */
  protected $arguments;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Creates a new DelayedExecutionToken for the specified callback.
   *
   * @param callable $callback
   *  The callback for which to delay execution.
   *
   * @param array $arguments
   *  <em>Optional</em>.
   *  A collection of arguments to pass to the callback.
   */
  public function __construct(callable $callback, array $arguments = [])
  {
    $this->callback = $callback;
    $this->arguments = $arguments;
  }

  /**
   * Executes the delayed callback.
   */
  public function __destruct()
  {
    call_user_func_array($this->callback, $this->arguments);
  }

}