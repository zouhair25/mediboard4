<?php

/**
 * Represents an HL7 PV1 FR message segment (Patient Visit) - HL7
 *  
 * @category HL7
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

/**
 * Class CHL7v2SegmentPV1_FR 
 * PV1 - Represents an HL7 PV1 FR message segment (Patient Visit)
 */

class CHL7v2SegmentPV1_FR extends CHL7v2Segment {
  public $name   = "PV1";
  public $set_id;
  

  /** @var CSejour */
  public $sejour;
  

  /** @var CAffectation */
  public $curr_affectation;

  /**
   * Build PV1 (FR) segment
   *
   * @param CHL7v2Event $event Event
   *
   * @return null
   */
  function build(CHL7v2Event $event) {
    parent::build($event);
    
    $receiver = $event->_receiver;
    $group    = $receiver->_ref_group;
    
    $sejour  = $this->sejour;
    
    $data = array();

    // PV1-1: Set ID - PV1 (SI) (optional)
    $data[] = $this->set_id;
    
    // PV1-2: Patient Class (IS)
    // Table - 0004
    // E - Emergency - Passage aux Urgences - Arriv�e aux urgences
    // I - Inpatient - Hospitalisation
    // N - Not Applicable - Non applicable - 
    // O - Outpatient - Actes et consultation externe
    // R - Recurring patient - S�ances
    // Cas de la transaction ITI-30 "Patient Identity Field"
    if (!$sejour) {
      $data[] = "N";
      $this->fill($data);
      return;
    } 

    $data[] = CHL7v2TableEntry::mapTo("4", $sejour->type);
    
    // PV1-3: Assigned Patient Location (PL) (optional)
    $data[] = $this->getPL($receiver, $sejour, $this->curr_affectation);
    
    // PV1-4: Admission Type (IS) (optional)
    // Table - 0007
    // C  - Confort (chirurgie esth�tique)
    // L  - Accouchement maternit�
    // N  - Nouveau n�
    // R  - Routine (par d�faut)
    // U  - Caract�re d'urgence aigue du probl�me quel que soit le service d'entr�e
    // RM - R�trocession du m�dicament
    // IE - Prestation inter-�tablissements
    $naissance = new CNaissance();
    $naissance->sejour_enfant_id = $sejour->_id;
    $naissance->loadMatchingObject();
    
    // Cas d'une naissance
    if ($naissance->_id) {
      $data[] = "N";
    }
    // Cas accouchement maternit�
    elseif ($sejour->type_pec == "O") {
      $data[] = "L";
    }
    // D�faut
    else {
      $data[] = "R";
    }

    // PV1-5: Preadmit Number (CX) (optional)
    if ($receiver->_configs["build_PV1_5"] == "none") {
      $data[] = null;
    }
    else if ($sejour->_etat != "preadmission" && $sejour->_admit !== true) {
      $data[] = null;
    }
    else {
      // PV1-5: Preadmit Number (CX) (optional)
      if (CHL7v2Message::$build_mode == "simple") {
        $data[] = array (
          $sejour->_id,
        );
      }
      else {
        // M�me traitement que pour l'IPP
        switch ($receiver->_configs["build_PID_3_4"]) {
          case 'actor':
            $assigning_authority = $this->getAssigningAuthority("actor", null, $receiver);
            break;

          default:
            $assigning_authority = $this->getAssigningAuthority("FINESS", $group->finess);
            break;
        }

        $sejour->loadNPA($group->_id);
        $sejour->loadNDA($group->_id);

        $NPA    = $sejour->_NPA;
        $number = $NPA ? $NPA : $sejour->_NDA;

        $data[] = $number ? array(
          array(
            $number,
            null,
            null,
            // PID-3-4 Autorit� d'affectation
            $assigning_authority,
            "AN"
          )
        ) : null;
      }
    }

    // PV1-6: Prior Patient Location (PL) (optional)
    $data[] = null;
    
    // PV1-7: Attending Doctor (XCN) (optional repeating)
    $sejour->loadRefPraticien();
    $data[] = $this->getXCN($sejour->_ref_praticien, $receiver, true);
    
    // PV1-8: Referring Doctor (XCN) (optional repeating)
    $data[] = $sejour->adresse_par_prat_id ? $this->getXCN($sejour->loadRefAdresseParPraticien(), $receiver, true) : null;
    
    // PV1-9: Consulting Doctor (XCN) (optional repeating)
    $data[] = null;
    
    // PV1-10: Hospital Service (IS) (optional)
    $data[] = $sejour->discipline_id;
    
    // PV1-11: Temporary Location (PL) (optional)
    $data[] = null;
    
    // PV1-12: Preadmit Test Indicator (IS) (optional)
    $data[] = null;
    
    // PV1-13: Re-admission Indicator (IS) (optional)
    $data[] = null;
    
    // PV1-14: Admit Source (IS) (optional)  
    $data[] = $this->getPV114($receiver, $sejour);
    
    // PV1-15: Ambulatory Status (IS) (optional repeating)
    $data[] = null;
    
    // PV1-16: VIP Indicator (IS) (optional)
    // Table - 0099
    // P - Public
    // I - Incognito
    $data[] = $sejour->loadRefPatient()->vip ? "I" : "P";
    
    // PV1-17: Admitting Doctor (XCN) (optional repeating)
    $data[] = $this->getXCN($sejour->_ref_praticien, $receiver, true);
    
    // PV1-18: Patient Type (IS) (optional)
    $data[] = null;
    
    // PV1-19: Visit Number (CX) (optional)
    $identifiers = array();
    if ($receiver->_configs["build_PV1_19"] == "simple") {
      $identifiers[] = $sejour->_NDA;
    }
    else {
      if ($receiver->_configs["build_NDA"] == "PV1_19") {
        $identifiers[] = $sejour->_NDA ? array(
          $sejour->_NDA,
          null,
          null,
          // PID-3-4 Autorit� d'affectation
          $this->getAssigningAuthority("FINESS", $group->finess),
          "AN"
        ) : array();
      }
      else {
        /* @todo Gestion des s�ances */
        $identifiers[] = array(
          $sejour->_id,
          null,
          null,
          // PID-3-4 Autorit� d'affectation
          $this->getAssigningAuthority("mediboard"),
          $receiver->_configs["build_PV1_19_identifier_authority"]
        );
      }

      // Ajout des identifiants des acteurs d'int�gration
      $this->fillActorsIdentifiers($identifiers, $sejour, $receiver);
    }
    $data[] = $identifiers;
    
    // PV1-20: Financial Class (FC) (optional repeating)
    $data[] = $sejour->loadRefPrestation()->code;

    // PV1-21: Charge Price Indicator (IS) (optional)
    // Table - 0032
    $data[] = $this->getModeTraitement($sejour);

    // PV1-22: Courtesy Code (IS) (optional)
    // Table - 0045
    // Y - Demande de chambre particuli�re
    // N - Pas de demande de chambre particuli�re
    $data[] = $sejour->chambre_seule ? "Y" : "N";
    
    // PV1-23: Credit Rating (IS) (optional)
    $data[] = null;
    
    // PV1-24: Contract Code (IS) (optional repeating)
    $data[] = null;
    
    // PV1-25: Contract Effective Date (DT) (optional repeating)
    $data[] = null;
    
    // PV1-26: Contract Amount (NM) (optional repeating)
    $data[] = null;
    
    // PV1-27: Contract Period (NM) (optional repeating)
    $data[] = null;
    
    // PV1-28: Interest Code (IS) (optional)
    $data[] = null;
    
    // PV1-29: Transfer to Bad Debt Code (IS) (optional)
    $data[] = null;
    
    // PV1-30: Transfer to Bad Debt Date (DT) (optional)
    $data[] = null;
    
    // PV1-31: Bad Debt Agency Code (IS) (optional)
    $data[] = null;
    
    // PV1-32: Bad Debt Transfer Amount (NM) (optional)
    $data[] = null;
    
    // PV1-33: Bad Debt Recovery Amount (NM) (optional)
    $data[] = null;
    
    // PV1-34: Delete Account Indicator (IS) (optional)
    $data[] = null;
    
    // PV1-35: Delete Account Date (DT) (optional)
    $data[] = null;
    
    // PV1-36: Discharge Disposition (IS) (optional)
    $sejour->loadRefsAffectations();
    $data[] = $this->getPV136($receiver, $sejour);
    
    // PV1-37: Discharged to Location (DLD) (optional)
    $data[] = ($sejour->etablissement_sortie_id &&
              ($event->code == "A03" || $event->code == "A16" || $event->code == "A21" || $event->code == "Z99")) ?
                array($sejour->loadRefEtablissementTransfert()->finess) : null;
    
    // PV1-38: Diet Type (CE) (optional)
    $data[] = null;
    
    // PV1-39: Servicing Facility (IS) (optional)
    $data[] = null;
    
    // PV1-40: Bed Status (IS) (optional)
    // Interdit par IHE France
    $data[] = null;
    
    // PV1-41: Account Status (IS) (optional)
    // Utilisation que pour les �v�nements A03 et Z99
    // Table - 0117
    // D - C'�tait la derni�re venue pour ce dossier administratif
    // N - Ce n'�tait pas la derni�re venue pour ce dossier administratif
    if ($event->code == "A03" || $event->code == "Z99") {
      $data[] = ($sejour->type != "seances" && $sejour->sortie_reelle) ? "D" : "N";
    }
    else {
      $data[] = null;
    }
    
    // PV1-42: Pending Location (PL) (optional)
    $data[] = null;
    
    // PV1-43: Prior Temporary Location (PL) (optional)
    $data[] = null;
    
    // PV1-44: Admit Date/Time (TS) (optional)
    $data[] = $sejour->entree_reelle;
    
    // PV1-45: Discharge Date/Time (TS) (optional repeating)
    $data[] = $sejour->sortie_reelle;
    
    // PV1-46: Current Patient Balance (NM) (optional)
    $data[] = null;
    
    // PV1-47: Total Charges (NM) (optional)
    $data[] = null;
    
    // PV1-48: Total Adjustments (NM) (optional)
    $data[] = null;
    
    // PV1-49: Total Payments (NM) (optional)
    $data[] = null;
    
    // PV1-50: Alternate Visit ID (CX) (optional)
    $data[] = null;
    
    // PV1-51: Visit Indicator (IS) (optional)
    $data[] = null;
    
    // PV1-52: Other Healthcare Provider (XCN) (optional repeating)
    $data[] = null;
    
    $this->fill($data);
  }
}