<?php
/**
 * $Id: CSourceToViewSender.class.php 24018 2014-07-17 14:47:31Z phenxdesign $
 *
 * @package    Mediboard
 * @subpackage System
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 24018 $
 */

class CSourceToViewSender extends CMbObject {
  // DB Table key
  public $source_to_view_sender_id;

  // DB fields
  public $source_id;
  public $sender_id;
  public $last_datetime;
  public $last_duration;
  public $last_size;
  public $last_status;
  public $last_count;

  // Form fields
  public $_last_age;

  /** @var CViewSenderSource */
  public $_ref_source;
  
  /** @var CViewSender */
  public $_ref_sender;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "source_to_view_sender";
    $spec->key   = "source_to_view_sender_id";
    $spec->loggable = false;
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["sender_id"]     = "ref class|CViewSender notNull autocomplete|name";
    $props["source_id"]     = "ref class|CViewSenderSource notNull autocomplete|name";
    $props["last_datetime"] = "dateTime";
    $props["last_duration"] = "float";
    $props["last_size"]     = "num min|0";
    $props["last_status"]   = "enum list|triggered|uploaded|checked";
    $props["last_count"]    = "num min|0";

    $props["_last_age"]     = "num";
    return $props;
  }

  /**
   * Charge le sender
   *
   * @return CViewSender
   */
  function loadRefSender() {
    $sender = $this->loadFwdRef("sender_id", true);
    $this->_last_age = CMbDT::minutesRelative($this->last_datetime, CMbDT::dateTime());
    return $this->_ref_sender = $sender;
  }

  /**
   * Charge la source
   *
   * @return CViewSenderSource
   */
  function loadRefSource() {
    return $this->_ref_source = $this->loadFwdRef("source_id", true);
  }
}
