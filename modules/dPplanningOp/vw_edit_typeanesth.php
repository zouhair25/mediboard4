<?php
/**
 * $Id: vw_edit_typeanesth.php 24175 2014-07-28 09:17:55Z aurelie17 $
 *
 * @package    Mediboard
 * @subpackage PlanningOp
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 24175 $
 */

CCanDo::checkAdmin();

$show_inactive = CValue::getOrSession("inactive", 0);

// Liste des Type d'anesth�sie
$type_anesth = new CTypeAnesth();
$where = array(
  "actif" =>  ($show_inactive) ? " IN ('0','1')" : " = '1' "
);

/** @var CTypeAnesth[] $types_anesth */
$types_anesth = $type_anesth->loadList($where, "name");
foreach ($types_anesth as &$_type_anesth) {
  $_type_anesth->countOperations();
  $_type_anesth->loadRefGroup();
}

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("types_anesth"  , $types_anesth);
$smarty->assign("show_inactive" , $show_inactive);
$smarty->display("vw_edit_typeanesth.tpl");