<?php
/**
 * $Id: vw_anesthesie.php 24175 2014-07-28 09:17:55Z aurelie17 $
 *
 * @package    Mediboard
 * @subpackage SalleOp
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 24175 $
 */

CCanDo::checkRead();

$ds = CSQLDataSource::get("std");

$op    = CValue::getOrSession("op");
$date  = CValue::getOrSession("date", CMbDT::date());

$consultAnesth  = new CConsultAnesth();
$consult        = new CConsultation();
$userSel        = new CMediusers();
$operation      = new COperation();
$operation->load($op);
$operation->loadRefChir();
$operation->loadRefSejour();
$consult_anesth = $operation->loadRefsConsultAnesth();

if ($consult_anesth->_id) {
  $consult_anesth->loadRefConsultation();
  $consult = $consult_anesth->_ref_consultation;
  $consult->_ref_consult_anesth = $consultAnesth;
  $consult->loadRefPlageConsult();
  $consult->loadRefsDocItems();
  $consult->loadRefPatient();
  $prat_id = $consult->_ref_plageconsult->chir_id;

  $consult_anesth->loadRefs();

  // On charge le praticien
  $userSel->load($prat_id);
  $userSel->loadRefs();
}

$anesth = new CTypeAnesth();
$anesth = $anesth->loadGroupList();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("op"             , $op);
$smarty->assign("date"           , $date);
$smarty->assign("operation"      , $operation);
$smarty->assign("anesth"         , $anesth);
$smarty->assign("techniquesComp" , new CTechniqueComp());
$smarty->assign("isPrescriptionInstalled", CModule::getActive("prescription"));

$smarty->display("vw_anesthesie.tpl");
