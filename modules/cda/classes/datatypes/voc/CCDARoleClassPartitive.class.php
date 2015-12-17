<?php

/**
 * $Id$
 *
 * @category CDA
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @link     http://www.mediboard.org
 */

/**
 * abstDomain: V10429 (C-0-D11555-V13940-V10429-cpt)
 */
class CCDARoleClassPartitive extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'CONT',
    'MBR',
    'PART',
  );
  public $_union = array (
    'RoleClassIngredientEntity',
    'RoleClassLocatedEntity',
    'RoleClassSpecimen',
  );


  /**
   * Retourne les propri�t�s
   *
   * @return array
   */
  function getProps() {
    parent::getProps();
    $props["data"] = "str xml|data enum|".implode("|", $this->getEnumeration(true));
    return $props;
  }
}