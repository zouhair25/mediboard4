<?php
/**
 * $Id: download_csv_interventions.php 28411 2015-05-28 08:00:20Z phenxdesign $
 *
 * @package    Mediboard
 * @subpackage Hospi
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 28411 $
 */

CCanDo::checkRead();

// Bornes de date des statistiques
$date_min = CValue::get("_date_min", CMbDT::date("-2 MONTHS"));
$date_max = CValue::get("_date_max", CMbDT::date());

// Autres �l�ments de filtrage
$service_id    = CValue::get("service_id");
$type          = CValue::get("type");
$prat_id       = CValue::get("prat_id");
$func_id       = CValue::get("func_id");
$discipline_id = CValue::get("discipline_id");
$salle_id      = CValue::get("salle_id");
$bloc_id       = CValue::get("bloc_id");
$codes_ccam    = strtoupper(CValue::get("codes_ccam", ""));
$hors_plage    = CValue::get("hors_plage", 1);

$interv = new COperation();
$ds = $interv->getDS();

$group_id = CGroups::loadCurrent()->_id;
$where = array(
  "sejour.group_id" => $ds->prepare("= ?", $group_id),
  "operations.date"  => $ds->prepare("BETWEEN ? AND ?", $date_min, $date_max),
);
$ljoin = array(
  "sejour" => "sejour.sejour_id = operations.sejour_id",
);

if ($service_id) {
  $where["sejour.service_id"] = $ds->prepare("=?", $service_id);
}
if ($type) {
  $where["sejour.type"] = $ds->prepare("=?", $type);
}
if ($prat_id) {
  $where["operations.chir_id"] = $ds->prepare("=?", $prat_id);
}
if ($func_id) {
  $ljoin["users_mediboard"] = "users_mediboard.user_id = operations.chir_id";
  $where["users_mediboard.function_id"] = $ds->prepare("=?", $func_id);
}
if ($discipline_id) {
  $ljoin["users_mediboard"] = "users_mediboard.user_id = operations.chir_id";
  $where["users_mediboard.discipline_id"] = $ds->prepare("=?", $discipline_id);
}
if ($salle_id) {
  $where["operations.salle_id"] = $ds->prepare("=?", $salle_id);
}
if ($bloc_id) {
  $ljoin["sallesbloc"] = "sallesbloc.salle_id = operations.salle_id";
  $where["sallesbloc.bloc_id"] = $ds->prepare("=?", $bloc_id);
}
if ($hors_plage) {
  $where["operations.plageop_id"] = "IS NULL";
}
if ($codes_ccam) {
  $where["operations.codes_ccam"] = $ds->prepare("LIKE %", "%$codes_ccam%");
}

/** @var COperation[] $interventions */
$interventions = $interv->loadList($where, null, null, "operation_id", $ljoin);

// Chargements de masse
$sejours  = CMbObject::massLoadFwdRef($interventions, "sejour_id");
CMbObject::massLoadFwdRef($sejours, "patient_id");
CMbObject::massLoadFwdRef($sejours, "praticien_id");

CMbObject::massLoadFwdRef($interventions, "chir_id");

$columns = array(
  "IPP",
  "Nom",
  "Nom naissance",
  "Pr�nom",
  "Date naissance",
  "Sexe",

  "Date intervention",
  "Libell� intervention",
  "Chirurgien nom",
  "Chirurgien pr�nom",

  "NDA",
  "Praticien nom",
  "Praticien pr�nom",
  "Date entr�e",
  "Date sortie",
);

$csv = new CCSVFile();
$csv->writeLine($columns);

foreach ($interventions as $_intervention) {
  $_sejour    = $_intervention->loadRefSejour();
  $_patient   = $_sejour->loadRefPatient();
  $_praticien = $_sejour->loadRefPraticien();
  $_chir      = $_intervention->loadRefChir();

  $_patient->loadIPP();
  $_sejour->loadNDA();

  $row = array(
    // Patient
    $_patient->_IPP,
    $_patient->nom,
    $_patient->nom_jeune_fille,
    $_patient->prenom,
    $_patient->naissance,
    $_patient->sexe,

    // Intervention
    $_intervention->libelle ?: $_intervention->codes_ccam,
    $_intervention->date,
    $_chir->_user_last_name,
    $_chir->_user_first_name,

    // S�jour
    $_sejour->_NDA,
    $_praticien->_user_last_name,
    $_praticien->_user_first_name,
    $_sejour->entree,
    $_sejour->sortie,
  );

  $csv->writeLine($row);
}

$csv->stream("Interventions $date_min - $date_max", true);
