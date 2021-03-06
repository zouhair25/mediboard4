<?php
/**
 * $Id: ajax_link_sejour.php 28965 2015-07-15 11:43:47Z kgrisel $
 *
 * @package    Mediboard
 * @subpackage Cabinet
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 28965 $
 */

CCanDo::checkRead();

$post_redirect = CValue::get('post_redirect', 'm=cabinet&tab=edit_planning');

$consult_id = CValue::get("consult_id");
$group_id   = CGroups::loadCurrent()->_id;

$consult = new CConsultation();
$consult->load($consult_id);
$consult->loadRefPlageConsult();

// next consultations
$dateW = $consult->_ref_plageconsult->date;
$whereN = array();
$ljoin = array();
$ljoin["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";
$whereN["patient_id"] = " = '$consult->patient_id'";
$whereN["plageconsult.date"] = " >= '$dateW'";
$whereN["heure"]  = " >= '$consult->heure'";
/** @var CConsultation[] $consults */
$consults = $consult->loadListWithPerms(PERM_READ, $whereN, null, null, null, $ljoin);
foreach ($consults as $_consult) {
  $_consult->loadRefPraticien()->loadRefFunction();
  $_consult->loadRefSejour();
}

// sejours
$where = array();
$where[] = "'$consult->_date' BETWEEN DATE(entree) AND DATE(sortie)";
$where["sejour.type"] = "!= 'consult'";
$where["sejour.group_id"] = "= '$group_id'";
$where["sejour.patient_id"] = "= '$consult->patient_id'";
/** @var CSejour[] $sejours */
$sejour = new CSejour();
$sejours = $sejour->loadListWithPerms(PERM_READ, $where);
CMbObject::massLoadFwdRef($sejours, "praticien_id");
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPraticien()->loadRefFunction();
}


$smarty = new CSmartyDP();
$smarty->assign("sejours", $sejours);
$smarty->assign("consult", $consult);
$smarty->assign("next_consults", $consults);
$smarty->assign("post_redirect", $post_redirect);
$smarty->display("inc_link_sejour.tpl");