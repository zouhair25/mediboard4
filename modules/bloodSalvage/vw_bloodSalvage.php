<?php
/**
 * $Id: vw_bloodSalvage.php 20938 2013-11-13 11:02:47Z aurelie17 $
 *
 * @package    Mediboard
 * @subpackage bloodSalvage
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 20938 $
 */

CCanDo::checkRead();
/* Récupération des variables en session et ou issues des formulaires.*/
$salle            = CValue::getOrSession("salle");
$op               = CValue::getOrSession("op");
$date             = CValue::getOrSession("date", CMbDT::date());

$blood_salvage = new CBloodSalvage();

$selOp = new COperation();

if ($op) {
  $selOp->load($op);
  $selOp->loadRefs();
  $where = array();
  $where["operation_id"] = "='$selOp->_id'";
  $blood_salvage->loadObject($where);
}

$smarty = new CSmartyDP();

$smarty->assign("blood_salvage",    $blood_salvage);
$smarty->assign("blood_salvage_id", $blood_salvage->_id);
$smarty->assign("selOp",            $selOp);
$smarty->assign("date",             $date);

$smarty->display("vw_bloodSalvage.tpl");
