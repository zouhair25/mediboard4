<?php
/**
 * $Id: httpreq_liste_plages.php 20938 2013-11-13 11:02:47Z aurelie17 $
 *
 * @package    Mediboard
 * @subpackage bloodSalvage
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 20938 $
 */

CCanDo::checkRead();
$date         = CValue::getOrSession("date", CMbDT::date());
$operation_id = CValue::getOrSession("operation_id");
$salle_id     = CValue::getOrSession("salle");

// Chargement des praticiens
$listAnesths = new CMediusers;
$listAnesths = $listAnesths->loadAnesthesistes(PERM_READ);

// Liste des blocs
$listBlocs = new CBlocOperatoire();
$listBlocs = $listBlocs->loadGroupList();

// Selection des plages op�ratoires de la journ�e
$salle = new CSalle;
if ($salle->load($salle_id)) {
  $salle->loadRefsForDay($date); 
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("vueReduite"    , false        );
$smarty->assign("salle"         , $salle       );
$smarty->assign("praticien_id"  , null         );
$smarty->assign("listBlocs"     , $listBlocs   );
$smarty->assign("listAnesths"   , $listAnesths );
$smarty->assign("date"          , $date        );
$smarty->assign("operation_id"  , $operation_id);

$smarty->display("inc_liste_plages.tpl");
