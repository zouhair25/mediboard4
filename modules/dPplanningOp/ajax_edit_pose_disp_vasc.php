<?php
/**
 * $Id: ajax_edit_pose_disp_vasc.php 19840 2013-07-09 19:36:14Z phenxdesign $
 *
 * @package    Mediboard
 * @subpackage PlanningOp
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 19840 $
 */

CCanDo::checkEdit();

// TODO check droit sur dPsalleOp plutot

$pose_disp_vasc_id = CValue::get("pose_disp_vasc_id");
$sejour_id         = CValue::get("sejour_id");
$operation_id      = CValue::get("operation_id");
$operateur_ids     = CValue::get("operateur_ids");

if (!is_array($operateur_ids)) {
  $operateur_ids = explode("-", $operateur_ids);
  CMbArray::removeValue("", $operateur_ids);
}

$operateur = new CMediusers;

if (count($operateur_ids)) {
  $where = array(
    "user_id" => "IN(".implode(",", $operateur_ids).")",
  );
  $operateurs = $operateur->loadList($where);
}
else {
  $operateurs = array();
}

$pose = new CPoseDispositifVasculaire;
$pose->load($pose_disp_vasc_id);

if (!$pose->_id) {
  $pose->sejour_id = $sejour_id;
  $pose->operation_id = $operation_id;
  $pose->date = "now";
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("pose", $pose);
$smarty->assign("operateurs", $operateurs);

$smarty->display("inc_edit_pose_disp_vasc.tpl");
