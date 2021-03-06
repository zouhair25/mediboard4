<?php
/**
 * $Id: CMbAPCMutex.class.php 19441 2013-06-06 08:23:26Z phenxdesign $
 *
 * @package    Mediboard
 * @subpackage classes
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    SVN: $Id: CMbAPCMutex.class.php 19441 2013-06-06 08:23:26Z phenxdesign $
 */

/**
 * APC mutex handler
 */
class CMbAPCMutex extends CMbMutexDriver {
  static protected $_slam_defense = null;

  protected $_error;

  /**
   * @see parent::__construct()
   */
  function __construct($key, $label = null) {
    if (!function_exists("apc_exists")) {
      throw new Exception("APC unavailable");
    }

    if (ini_get("apc.slam_defense")) {
      throw new Exception("APC available, but slam defense should be disabled");
    }

    parent::__construct($key, $label);
  }

  /**
   * @see parent::release()
   */
  function release() {
    if ($this->canRelease()) {
      apc_delete($this->getLockKey());
    }
  }

  /**
   * @see parent::setLock()
   */
  protected function setLock($duration) {
    return (bool) @apc_add($this->getLockKey(), 1, $duration);
  }

  /**
   * Never has to recover as keys are volatile
   *
   * @see parent::recover()
   */
  protected function recover($duration){
    return false;
  }
}
