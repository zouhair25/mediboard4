<?php
/**
 * $Id: httpreq_code_tarif.php 23340 2014-05-27 15:05:38Z rhum1 $
 *
 * @package    Mediboard
 * @subpackage Cabinet
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 23340 $
 */

CCanDo::checkRead();

$codeacte = CValue::getOrSession("code");
$callback = CValue::getOrSession("callback");

// Chargement du code
$code = CDatedCodeCCAM::get($codeacte);

if (!$code->code) {
  $tarif = 0;
  CAppUI::stepAjax("$codeacte: code inconnu", UI_MSG_ERROR);
}

// si le code CCAM est complet (activite + phase), on selectionne le tarif correspondant
if ($code->_activite != "" && $code->_phase != "") {
  $tarif = $code->activites[$code->_activite]->phases[$code->_phase]->tarif;
}
// sinon, on prend le tarif par default
else {
  $tarif = $code->_default;
}

CAppUI::callbackAjax($callback, $tarif);
CAppUI::stepAjax("$codeacte: $tarif");
