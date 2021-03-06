<?php
/**
 * $Id: CDailyCheckItemCategory.class.php 28107 2015-04-28 09:07:12Z aurelie17 $
 *
 * @package    Mediboard
 * @subpackage SalleOp
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 28107 $
 */

/**
 * Check item category
 */
class CDailyCheckItemCategory extends CMbObject {
  public $daily_check_item_category_id;

  // DB Fields
  public $title;
  public $desc;
  public $index;

  ////////
  public $target_class;
  public $target_id;
  public $type;
  // OR //
  public $list_type_id;
  ////////

  /** @var CDailyCheckItemType[] */
  public $_ref_item_types;

  /** @var CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire */
  public $_ref_target;

  /** @var CDailyCheckListType */
  public $_ref_list_type;

  public $_target_guid;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'daily_check_item_category';
    $spec->key   = 'daily_check_item_category_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props['title'] = 'str notNull';
    $props['desc']  = 'text';
    $props['index'] = 'num notNull min|1 default|1';

    $props['target_class'] = 'enum list|CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire notNull default|CSalle';
    $props['target_id']    = 'ref class|CMbObject meta|target_class';
    $props['type']         = 'enum list|'.implode('|', array_keys(CDailyCheckList::$types));
    $props['list_type_id'] = 'ref class|CDailyCheckListType autocomplete|_view';

    $props['_target_guid'] = 'str notNull';
    return $props;
  }

  /**
   * @see parent::getBackProps()
   */
  function getBackProps() {
    $backProps = parent::getBackProps();
    $backProps['item_types'] = 'CDailyCheckItemType category_id';
    return $backProps;
  }

  /**
   * Load target object
   *
   * @return CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire
   */
  function loadRefTarget(){
    return $this->_ref_target = $this->loadFwdRef("target_id");
  }

  /**
   * Load list type
   *
   * @return CDailyCheckListType
   */
  function loadRefListType(){
    return $this->_ref_list_type = $this->loadFwdRef("list_type_id");
  }

  /**
   * Load item types
   *
   * @return CDailyCheckItemType[]
   */
  function loadRefItemTypes() {
    return $this->_ref_item_types = $this->loadBackRefs("item_types", "`index`, title");
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = ($this->target_class == 'CBlocOperatoire' ? 'Salle de r�veil' : $this->getLocale("target_class"))." - $this->title";
  }

  /**
   * Get categories tree
   *
   * @param bool $operation see operations
   *
   * @return array
   */
  static function getCategoriesTree($operation = false){
    $object = new self();

    $target_classes = CDailyCheckList::getNonHASClasses($operation);

    $targets = array();
    $by_class = array();

    foreach ($target_classes as $_class) {
      if ($_class != "COperation") {
        /** @var CSalle|CBlocOperatoire $_object */
        $_object = new $_class;
        //$_targets = $_object->loadGroupList();
        $_targets = $_object->loadList();
        array_unshift($_targets, $_object);

        $targets[$_class] = array_combine(CMbArray::pluck($_targets, "_id"), $_targets);
      }

      $where = array("target_class" => "= '$_class'");

      if ($_class == "COperation") {
        $where["list_type_id"] = ' IS NOT NULL';
      }

      /** @var CDailyCheckItemCategory[] $_list */
      $_list = $object->loadList($where, "target_id+0, title"); // target_id+0 to have NULL at the beginning

      $by_object = array();
      foreach ($_list as $_category) {
        $_key = $_category->target_id ? $_category->target_id : "all";
        $by_object[$_key][$_category->_id] = $_category;
      }

      $by_class[$_class] = $by_object;
    }

    return array(
      $targets,
      $by_class,
    );
  }
}
