<?php
/**
 * $Id: vw_procedures.php 19316 2013-05-28 09:33:17Z rhum1 $
 *
 * @package    Mediboard
 * @subpackage Qualite
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 19316 $
 */

CCanDo::checkRead();

$doc_ged_id   = CValue::getOrSession("doc_ged_id");
$theme_id     = CValue::getOrSession("theme_id");
$chapitre_id  = CValue::getOrSession("chapitre_id");
$sort_by      = CValue::getOrSession("sort_by", "date");
$sort_way     = CValue::getOrSession("sort_way", "DESC");

$docGed = new CDocGed();
if (!$docGed->load($doc_ged_id)) {
  // Ce document n'est pas valide
  $doc_ged_id = null;
  CValue::setSession("doc_ged_id");
  $docGed = new CDocGed;
}
else {
  $docGed->loadLastActif();
  if (!$docGed->_lastactif->doc_ged_suivi_id || $docGed->annule) {
    // Ce document n'est pas Termin� ou est suspendu
    $doc_ged_id = null;
    CValue::setSession("doc_ged_id");
    $docGed = new CDocGed;
  }
  else {
    $docGed->_lastactif->loadFile();
    $docGed->loadRefs();
  }
}

$group = CGroups::loadCurrent();

// Liste des Th�mes
$theme = new CThemeDoc();
$where = array();
$where[] = "group_id = '$group->_id' OR group_id IS NULL";
/** @var CThemeDoc[] $listThemes */
$listThemes = $theme->loadlist($where, "nom");

// Liste des chapitres
$chapitre = new CChapitreDoc();
$order = "group_id, nom";
$where = array();
$where["pere_id"] = "IS NULL";
$where[] = "group_id = '$group->_id' OR group_id IS NULL";
/** @var CChapitreDoc[] $listChapitres */
$listChapitres = $chapitre->loadlist($where, $order);
foreach ($listChapitres as $_chapitre) {
  $_chapitre->loadChapsDeep(); 
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("theme_id"       , $theme_id);
$smarty->assign("chapitre_id"    , $chapitre_id);
$smarty->assign("listThemes"     , $listThemes);
$smarty->assign("listChapitres"  , $listChapitres);
$smarty->assign("docGed"         , $docGed);
$smarty->assign("fileSel"        , new CFile());
$smarty->assign("sort_by"        , $sort_by);
$smarty->assign("sort_way"       , $sort_way);

$smarty->display("vw_procedures.tpl");
