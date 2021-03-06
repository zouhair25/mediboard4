<?php
/**
 * $Id: vw_compta.php 28340 2015-05-20 10:14:30Z aurelie17 $
 *
 * @package    Mediboard
 * @subpackage Cabinet
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 28340 $
 */

CCanDo::checkEdit();

// Gestion des bouton radio des dates
$now             = CMbDT::date();
$yesterday       = CMbDT::date("-1 DAY"         , $now);
$week_deb        = CMbDT::date("last sunday"    , $now);
$week_fin        = CMbDT::date("next sunday"    , $week_deb);
$week_deb        = CMbDT::date("+1 day"         , $week_deb);
$rectif          = CMbDT::transform("+0 DAY", $now, "%d")-1;
$month_deb       = CMbDT::date("-$rectif DAYS"  , $now);
$month_fin       = CMbDT::date("+1 month"       , $month_deb);
$three_month_deb = CMbDT::date("-3 month"       , $month_fin);
$month_fin       = CMbDT::date("-1 day"         , $month_fin);

$filter = new CConsultation;

$filter->_date_min = CMbDT::date();
$filter->_date_max = CMbDT::date("+ 0 day");

$filter->_etat_paiement = CValue::getOrSession("_etat_paiement", 0);
$filter->_type_affichage = CValue::getOrSession("_type_affichage", 0);

$filter_reglement = new CReglement();
$filter_reglement->mode = CValue::getOrSession("mode", 0);

// L'utilisateur est-il praticien ?
$mediuser = CMediusers::get();
$mediuser->loadRefFunction();

$is_praticien = $mediuser->isPraticien();
$is_admin     = in_array(CUser::$types[$mediuser->_user_type], array("Administrator"));
$is_admin_or_secretaire = in_array(CUser::$types[$mediuser->_user_type], array("Administrator", "Secr�taire"));
$listPrat     = CConsultation::loadPraticiensCompta();

$bloc = new CBlocOperatoire();
$blocs = $bloc->loadGroupList();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("filter"                , $filter);
$smarty->assign("filter_reglement"      , $filter_reglement);
$smarty->assign("mediuser"              , $mediuser);
$smarty->assign("is_praticien"          , $is_praticien);
$smarty->assign("is_admin_or_secretaire", $is_admin_or_secretaire);
$smarty->assign("listPrat"              , $listPrat);
$smarty->assign("now"                   , $now);
$smarty->assign("yesterday"             , $yesterday);
$smarty->assign("week_deb"              , $week_deb);
$smarty->assign("week_fin"              , $week_fin);
$smarty->assign("month_deb"             , $month_deb);
$smarty->assign("three_month_deb"       , $three_month_deb);
$smarty->assign("month_fin"             , $month_fin);
$smarty->assign("blocs"                 , $blocs);

$smarty->display("vw_compta.tpl");