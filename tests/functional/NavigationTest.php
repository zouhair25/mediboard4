<?php

/**
 * NavigationTest
 *
 * @description Test Navigation on the app by URL
 * @screen      ConsultationPage, CimPage, CcamPage, DossierPatientPage,
 *              ModelesPage, BlocPage, PlanifSejourPage
 *
 * @package    Mediboard
 * @subpackage Tests
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @link       http://www.mediboard.org
 */
class NavigationTest extends SeleniumTestCase {

  public static $endOfClass = false;

  public function testNavCim() {
    $homePage = new HomePage($this);
    $homePage->goToCim();
    $this->assertEquals("Aide au codage CIM", $homePage->getTitle());
  }

  public function testNavCcam() {
    $homePage = new HomePage($this);
    $homePage->goToCcam();
    $this->assertEquals("Aide au codage CCAM", $homePage->getTitle());
  }

  public function testNavDossierPatient() {
    $homePage = new HomePage($this);
    $homePage->goToDossierPatient();
    $this->assertEquals("Gestion des dossiers patient", $homePage->getTitle());
  }

  public function testNavConsultations() {
    $homePage = new HomePage($this);
    $homePage->goToConsultations();
    $this->assertEquals("Gestion des consultations", $homePage->getTitle());
  }

  public function testNavModeles() {
    $homePage = new HomePage($this);
    $homePage->goToModeles();
    $this->assertEquals("Mod�les de document", $homePage->getTitle());
  }

  public function testNavBloc() {
    $homePage = new HomePage($this);
    $homePage->goToBloc();
    $this->assertEquals("Planning du bloc op�ratoire", $homePage->getTitle());
  }

  public function testNavPlanifSejour() {
    $homePage = new HomePage($this);
    $homePage->goToPlanifSejour();
    $this->assertEquals("Planification des hospitalisations et chirurgies", $homePage->getTitle());
    self::$endOfClass = true;
  }

  public function tearDown() {
    if (self::$endOfClass) {
      $this->closeWindow();
      self::$endOfClass = false;
    }
  }
}