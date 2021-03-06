<?php

/**
 * Z80 - Change of medical responsibility - HL7
 *  
 * @category HL7
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

/**
 * Class CHL7v2EventADTZ80
 * Z80 - Change of medical responsibility
 */
class CHL7v2EventADTZ80_FR extends CHL7v2EventADT implements CHL7EventADTA01 {

  /** @var string */
  public $code        = "Z80";

  /** @var string */
  public $struct_code = "A01";

  /**
   * Construct
   *
   * @param string $i18n i18n
   *
   * @return \CHL7v2EventADTZ80_FR
   */
  function __construct($i18n = "FR") {
    parent::__construct($i18n);
  }

  /**
   * Get event planned datetime
   *
   * @param CAffectation $affectation Affectation
   *
   * @return DateTime Event occured
   */
  function getEVNOccuredDateTime($affectation) {
    return CMbDT::dateTime();
  }

  /**
   * Build Z80 event
   *
   * @param CAffectation $affectation Affectation
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($affectation) {
    /** @var CSejour $sejour */
    $sejour                       = $affectation->_ref_sejour;
    $sejour->_ref_hl7_affectation = $affectation;
    
    parent::build($affectation);

    /** @var CPatient $patient */
    $patient = $sejour->_ref_patient;
    // Patient Identification
    $this->addPID($patient, $sejour);
    
    // Patient Additional Demographic
    $this->addPD1($patient);
    
    // Doctors
    $this->addROLs($patient);
    
    // Next of Kin / Associated Parties
    $this->addNK1s($patient);
    
    // Patient Visit
    $this->addPV1($sejour);
    
    // Patient Visit - Additionale Info
    $this->addPV2($sejour);
    
    // Movement segment
    $this->addZBE($sejour);
    
    // Situation professionnelle
    $this->addZFP($sejour);
    
    // Compléments sur la rencontre
    $this->addZFV($sejour);
    
    // Mouvement PMSI
    $this->addZFM($sejour);
    
    // Complément démographique
    $this->addZFD($sejour);
    
    // Guarantor
    $this->addGT1($patient);
  }
}