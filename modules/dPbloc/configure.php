<?php

/**
 * dPbloc
 *
 * @category Bloc
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  SVN: $Id: configure.php 19148 2013-05-15 12:41:42Z rhum1 $
 * @link     http://www.mediboard.org
 */

CCanDo::checkAdmin();

$hours = range(0, 23);

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("hours", $hours);
$smarty->display("configure.tpl");
