<?php
/**
 * $Id: vw_edit_classification.php 19316 2013-05-28 09:33:17Z rhum1 $
 *
 * @package    Mediboard
 * @subpackage Qualite
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 19316 $
 */
 
global $can, $g;

$can->needsAdmin();

$typeVue       = CValue::getOrSession("typeVue",       0);
$etablissement = CValue::getOrSession("etablissement", $g);

// Liste des �tablissements
$etablissements = new CMediusers();
$etablissements = $etablissements->loadEtablissements(PERM_READ);

$smarty = new CSmartyDP();

$smarty->assign("etablissements", $etablissements);
$smarty->assign("etablissement",  $etablissement );
$smarty->assign("typeVue",        $typeVue);

if ($typeVue) {
  // Liste des Themes
  $doc_theme_id = CValue::getOrSession("doc_theme_id", null);

  // Chargement du theme demand�
  $theme = new CThemeDoc;
  $theme->load($doc_theme_id);
  $theme->loadRefsFwd();

  // Liste des Themes
  $listThemes = new CThemeDoc;

  $where             = array();
  $where["group_id"] = $etablissement ? "= '$etablissement'" : "IS NULL";

  $listThemes = $listThemes->loadList($where);
  
  // Cr�ation du Template
  $smarty->assign("theme",      $theme);
  $smarty->assign("listThemes", $listThemes);
  $smarty->display("vw_edit_themes.tpl");
}
else {
  $maxDeep = CAppUI::conf("dPqualite CChapitreDoc profondeur") - 2;

  // Chargement du chapitre demand�
  $doc_chapitre_id = CValue::getOrSession("doc_chapitre_id", null);
  $chapitre = new CChapitreDoc;
  $chapitre->load($doc_chapitre_id);
  $chapitre->loadRefsFwd();

  // Chargement du chapitre de navigation
  $nav_chapitre_id = CValue::getOrSession("nav_chapitre_id", null);
  $nav_chapitre = new CChapitreDoc;
  $nav_chapitre->load($nav_chapitre_id);
  $nav_chapitre->loadRefsFwd();

  if ($nav_chapitre->_id) {
    $nav_chapitre->computeLevel();
    $nav_chapitre->computePath();
  }
  else {
    $nav_chapitre->_level = -1;
  }
  // Liste des Chapitres
  $listChapitres = new CChapitreDoc;

  $where             = array();
  $where["group_id"] = $etablissement ? "= '$etablissement'" : "IS NULL";
  $where["pere_id"]  = $nav_chapitre->_id ? "= '$nav_chapitre->_id'" : "IS NULL";

  $listChapitres = $listChapitres->loadList($where);
  
  // Cr�ation du Template
  $smarty->assign("maxDeep",       $maxDeep);
  $smarty->assign("chapitre",      $chapitre);
  $smarty->assign("nav_chapitre",  $nav_chapitre);
  $smarty->assign("listChapitres", $listChapitres);

  $smarty->display("vw_edit_chapitres.tpl"); 
}
