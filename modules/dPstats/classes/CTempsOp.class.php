<?php
/**
 * $Id: CTempsOp.class.php 27046 2015-02-04 10:02:20Z rhum1 $
 *
 * @package    Mediboard
 * @subpackage dPstats
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 27046 $
 */

/**
 * Class CTempsOp
 *
 * Classe de mining des temps op�ratoires
 *
 * @todo Passer au mining framework�
 */
class CTempsOp extends CMbObject {
  // DB Table key
  public $temps_op_id;

  // DB Fields
  public $chir_id;
  public $ccam;
  public $nb_intervention;
  public $estimation;
  public $occup_moy;
  public $occup_ecart;
  public $duree_moy;
  public $duree_ecart;
  public $reveil_moy;
  public $reveil_ecart;

  // Object References
  /** @var  CMediusers */
  public $_ref_praticien;

  // Derived Fields
  public $_codes;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'temps_op';
    $spec->key   = 'temps_op_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();
    $specs["chir_id"]         = "ref class|CMediusers";
    $specs["nb_intervention"] = "num pos";
    $specs["estimation"]      = "time";
    $specs["occup_moy"]       = "time";
    $specs["occup_ecart"]     = "time";
    $specs["duree_moy"]       = "time";
    $specs["duree_ecart"]     = "time";
    $specs["reveil_moy"]       = "time";
    $specs["reveil_ecart"]     = "time";
    $specs["ccam"]            = "str";
    return $specs;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_codes = explode("|", strtoupper($this->ccam));
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefPraticien();
    $this->_ref_praticien->loadRefFunction();
  }

  /**
   * Chargement du praticien
   *
   * @return CMediusers Le praticien li�
   */
  function loadRefPraticien() {
    return $this->_ref_praticien = $this->loadFwdRef("chir_id", 1);
  }

  /**
   * Dur�e moyenne d'intervention
   *
   * @param int          $chir_id [optional]
   * @param array|string $ccam    [optional]
   *
   * @return int|bool Dur�e en minutes, 0 si aucune intervention, false si temps non calcul�
   */
  static function getTime($chir_id = 0, $ccam = null){
    $where = array();
    $total = array();
    $total["occup_somme"] = 0;
    $total["nbInterventions"] = 0;
    $where["chir_id"] = "= '$chir_id'";

    if (is_array($ccam)) {
      foreach ($ccam as $code) {
        $where[] = "ccam LIKE '%".strtoupper($code)."%'";
      }
    }
    elseif ($ccam) {
      $where["ccam"] = "LIKE '%".strtoupper($ccam)."%'";
    }

    $temp = new CTempsOp;
    if (null == $liste = $temp->loadList($where)) {
      return false;
    }

    foreach ($liste as $temps) {
      $total["nbInterventions"] += $temps->nb_intervention;
      $total["occup_somme"] += $temps->nb_intervention * strtotime($temps->occup_moy);
    }

    if ($total["nbInterventions"]) {
      $time = $total["occup_somme"] / $total["nbInterventions"];
    }
    else {
      $time = 0;
    }

    return $time;
  }
}
