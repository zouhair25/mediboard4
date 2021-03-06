<?php
/**
 * $Id: configuration.php 28753 2015-06-29 09:39:00Z kgrisel $
 *
 * @category Cabinet
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision: 28753 $
 * @link     http://www.mediboard.org
 */

CConfiguration::register(
  array(
    "CGroups" => array(
      "dPcabinet" => array(
        "CConsultation" => array(
          "keep_motif_rdv_multiples"  => "bool default|1",
          "complete_atcd_mode_grille" => "bool default|0",
        ),
        "CPrescription" => array(
          "view_prescription"         => "bool default|0",
          "view_prescription_externe" => "bool default|0"
        ),
        'Planning'     => array(
          'show_print_order_mode' => 'bool default|0'
        )
      )
    )
  )
);