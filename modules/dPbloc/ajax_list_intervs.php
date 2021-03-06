<?php
/**
 * $Id: ajax_list_intervs.php 24175 2014-07-28 09:17:55Z aurelie17 $
 *
 * @package    Mediboard
 * @subpackage dPbloc
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 24175 $
 */

CCanDo::checkEdit();
$plageop_id = CValue::get("plageop_id");
$list_type  = CValue::get("list_type", "left");

$anesth = new CTypeAnesth();
$anesth = $anesth->loadGroupList();

// Infos sur la plage op�ratoire
$plage = new CPlageOp();
$plage->load($plageop_id);
$plage->loadRefsFwd();

$intervs = $plage->loadRefsOperations(true, "rank, rank_voulu, horaire_voulu", true, $list_type != "left");

$chirs = CMbObject::massLoadFwdRef($intervs, "chir_id");
CMbObject::massLoadFwdRef($chirs, "function_id");
$sejours = CMbObject::massLoadFwdRef($intervs, "sejour_id");
CMbObject::massLoadFwdRef($sejours, "patient_id");

foreach ($intervs as $_interv) {
  $_interv->loadRefsFwd();
  $_interv->_ref_chir->loadRefFunction();
  $_interv->_ref_sejour->loadRefsFwd();
  $_interv->_count_affectations_personnel = $_interv->countBackRefs("affectations_personnel");
  $patient = $_interv->_ref_sejour->_ref_patient;
  $patient->loadRefDossierMedical();
  $patient->_ref_dossier_medical->countAllergies();
}

// liste des plages du praticien
$where = array(
  "date"    => "= '$plage->date'",
  "chir_id" => "= '$plage->chir_id'",
);

/** @var CPlageOp[] $list_plages */
$list_plages = $plage->loadList($where);
CMbObject::massLoadFwdRef($list_plages, "salle_id");
foreach ($list_plages as $_plage) {
  $_plage->loadRefSalle();
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("listPlages", $list_plages);
$smarty->assign("plage"     , $plage);
$smarty->assign("anesth"    , $anesth);
$smarty->assign("intervs"   , $intervs);
$smarty->assign("list_type" , $list_type);

$smarty->display("inc_list_intervs.tpl");
