<?php
/**
 * $Id: CExClassHostField.class.php 28793 2015-06-30 14:50:58Z phenxdesign $
 *
 * @package    Mediboard
 * @subpackage System
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 28793 $
 */

class CExClassHostField extends CMbObject {
  public $ex_class_host_field_id;
  
  public $ex_group_id;
  
  //public $host_type;
  public $host_class;
  public $field;

  // Grid positionning
  public $coord_label_x;
  public $coord_label_y;
  public $coord_value_x;
  public $coord_value_y;

  // Pixel positionning
  public $coord_left;
  public $coord_top;
  public $coord_width;
  public $coord_height;
  public $type;
  
  public $_ref_ex_group;
  public $_ref_host_object;

  public $_no_size = false;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "ex_class_host_field";
    $spec->key   = "ex_class_host_field_id";
    $spec->uniques["coord_value"] = array("ex_group_id", "coord_value_x", "coord_value_y", "coord_label_x", "coord_label_y");
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["ex_group_id"]       = "ref notNull class|CExClassFieldGroup cascade";
    
    //$props["host_type"]     = "enum list|host|reference1|reference2 default|host";
    $props["host_class"]    = "str notNull";
    $props["field"]         = "str notNull canonical";

    // Grid positionning
    $props["coord_value_x"] = "num min|0 max|100";
    $props["coord_value_y"] = "num min|0 max|100";
    $props["coord_label_x"] = "num min|0 max|100";
    $props["coord_label_y"] = "num min|0 max|100";
    
    // Pixel positionned
    $props["coord_left"]   = "num";
    $props["coord_top"]    = "num";
    $props["coord_width"]  = "num min|1";
    $props["coord_height"] = "num min|1";
    $props["type"]         = "enum list|label|value";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields(){
    parent::updateFormFields();

    if (!$this->coord_width && !$this->coord_height) {
      $this->_no_size = true;
    }
    
    $this->_view = $this->field; // FIXME
  }

  /**
   * Load ExGroup
   *
   * @param bool $cache Use cache
   *
   * @return CExClassFieldGroup
   */
  function loadRefExGroup($cache = true){
    return $this->_ref_ex_group = $this->loadFwdRef("ex_group_id", $cache);
  }

  /**
   * Get the host object of $this
   *
   * @param CExObject $ex_object The ExObject to get the host object of
   *
   * @return CMbObject|null
   */
  function getHostObject(CExObject $ex_object) {
    // Load the list of objects
    /** @var CMbObject[] $objects */
    $objects = array();

    // Links are stored
    if ($ex_object->_id) {
      $links = $ex_object->loadRefsLinks(true);

      // Direct reference
      foreach ($links as $_link) {
        $objects[] = $_link->loadTargetObject();
      }
    }

    // Or not
    else {
      $objects = array(
        $ex_object->_ref_object,
        $ex_object->_ref_reference_object_1,
        $ex_object->_ref_reference_object_2,
      );
    }

    // Direct reference
    foreach ($objects as $_object) {
      if ($this->host_class == $_object->_class) {
        return $this->_ref_host_object = $_object;
      }
    }

    // Indirect references
    foreach ($objects as $_object) {
      $_obj = $_object->getRelatedObjectOfClass($this->host_class);

      if ($_obj && $_obj->_id) {
        return $this->_ref_host_object = $_obj;
      }
    }

    return $this->_ref_host_object = new $this->host_class;
  }
}
