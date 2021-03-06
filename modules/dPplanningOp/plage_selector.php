<?php
/**
 * $Id: plage_selector.php 22873 2014-04-22 07:51:07Z mytto $
 *
 * @package    Mediboard
 * @subpackage PlanningOp
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 22873 $
 */

CCanDo::checkRead();
$ds = CSQLDataSource::get("std");

$chir         = CValue::get("chir"         , 0);
$date         = CValue::getOrSession("date_plagesel", CMbDT::date());
$group_id     = CValue::get("group_id"     , CGroups::loadCurrent()->_id);
$operation_id = CValue::get("operation_id" , null);
$curr_op_time = CValue::get("curr_op_time" , "25:00");
$resp_bloc = CModule::getInstalled("dPbloc")->canEdit();

// Liste des mois selectionnables

$date = CMbDT::format($date, "%Y-%m-01");
$listMonthes = array();
for ($i = -6; $i <= 12; $i++) {
  $curr_key   = CMbDT::transform("$i month", $date, "%Y-%m-%d");
  $curr_month = CMbDT::transform("$i month", $date, "%B %Y");
  $listMonthes[$i]["date"] = $curr_key;
  $listMonthes[$i]["month"] = $curr_month;
}
 
 // Chargement du chirurgien
$mediChir = new CMediusers();
$mediChir->load($chir);
$mediChir->loadBackRefs("secondary_functions");
$secondary_functions = array();
foreach ($mediChir->_back["secondary_functions"] as $curr_sec_func) {
  $secondary_functions[] = $curr_sec_func->function_id;
}

// Chargement de la liste des blocs op�ratoires
$bloc = new CBlocOperatoire();
$blocs = $bloc->loadGroupList(null, "nom");
foreach ($blocs as $_bloc) {
  $_bloc->loadRefsSalles();
  $_bloc->_date_min = CMbDT::date("+ " . $_bloc->days_locked . " DAYS");
}

// Chargement des plages pour le chir ou sa sp�cialit� par bloc
$where = array();
$selectPlages  = "(plagesop.chir_id = %1 OR plagesop.spec_id = %2 OR plagesop.spec_id ".CSQLDataSource::prepareIn($secondary_functions).")";
$where[]       = $ds->prepare($selectPlages, $mediChir->user_id, $mediChir->function_id);
$month_min = CMbDT::transform("+ 0 month", $date, "%Y-%m-00");
$month_max = CMbDT::transform("+ 1 month", $date, "%Y-%m-00");
$where["date"] = "BETWEEN '$month_min' AND '$month_max'";
if (!$resp_bloc) {
  $where[] = "date >= '".CMbDT::date()."'";
}
$order = "date, debut";
$plage = new CPlageOp;
$listPlages = array();
foreach ($blocs as $_bloc) {
  $where["salle_id"] = CSQLDataSource::prepareIn(array_keys($_bloc->_ref_salles));
  $listPlages[$_bloc->_id] = $plage->loadList($where, $order);
  if (!count($listPlages[$_bloc->_id])) {
    unset($listPlages[$_bloc->_id]);
  }
}

$time = explode(":", $curr_op_time);
$nb_secondes = $time[0]*3600 + $time[1]*60;

$_plage = new CPlageOp();
foreach ($listPlages as &$_bloc) {
  foreach ($_bloc as &$_plage) {
    $_plage->loadRefSalle();
    $_plage->multicountOperations($nb_secondes, false);
    $_plage->loadRefsNotes();
    $_plage->loadRefSpec(1);
  }
}

// Heures d'admission
$config              = CAppUI::conf("dPplanningOp CSejour");
$hours               = range($config["heure_deb"], $config["heure_fin"]);
$mins                = range(0, 59, $config["min_intervalle"]);
$heure_entree_veille = $config["heure_entree_veille"];
$heure_entree_jour   = $config["heure_entree_jour"];

// Horaire souhait�
$config = CAppUI::conf("dPplanningOp COperation");
$list_hours_voulu   = range(7, 20);
$list_minutes_voulu = range(0, 59, $config["min_intervalle"]);
foreach ($list_hours_voulu as &$hour) {
  $hour = str_pad($hour, 2, '0', STR_PAD_LEFT);
}
foreach ($list_minutes_voulu as &$minute) {
  $minute = str_pad($minute, 2, '0', STR_PAD_LEFT);
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("date"        , $date);
$smarty->assign("listMonthes" , $listMonthes);
$smarty->assign("operation_id", $operation_id);
$smarty->assign("chir"        , $chir);
$smarty->assign("group_id"    , $group_id);
$smarty->assign("curr_op_time", $curr_op_time);
$smarty->assign("blocs"       , $blocs);
$smarty->assign("listPlages"  , $listPlages);

$smarty->assign("hours"              , $hours);
$smarty->assign("mins"               , $mins);
$smarty->assign("heure_entree_veille", $heure_entree_veille);
$smarty->assign("heure_entree_jour"  , $heure_entree_jour);
$smarty->assign("list_hours_voulu"   , $list_hours_voulu);
$smarty->assign("list_minutes_voulu" , $list_minutes_voulu);

$smarty->assign("resp_bloc", $resp_bloc);

$smarty->display("plage_selector.tpl");
