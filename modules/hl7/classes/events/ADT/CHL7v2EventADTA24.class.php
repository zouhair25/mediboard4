<?php

/**
 * A24 - Link patient information - HL7
 *  
 * @category HL7
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

/**
 * Class CHL7v2EventADTA24
 * A24 - Link patient information
 */
class CHL7v2EventADTA24 extends CHL7v2EventADT implements CHL7EventADTA24 {

  /** @var string */
  public $code        = "A24";

  /** @var string */
  public $struct_code = "A24";

  /**
   * Get event planned datetime
   *
   * @param CSejour $sejour Admit
   *
   * @return DateTime Event occured
   */
  function getEVNOccuredDateTime($sejour) {
    return CMbDT::dateTime();
  }

  /**
   * Build A24 event
   *
   * @param CPatient $patient Person
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($patient) {
    parent::build($patient);
    
    // Patient Identification
    $this->addPID($patient);

    // Patient link Identification
    $this->addPID($patient->_patient_link);
  }
}