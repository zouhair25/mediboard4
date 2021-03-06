<?php
/**
 * $Id: inc_graph_audio_tonal.php 20068 2013-07-26 13:21:27Z rhum1 $
 *
 * @package    Mediboard
 * @subpackage Cabinet
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 20068 $
 */

global $can, $m;

CAppUI::requireLibraryFile("jpgraph/src/mbjpgraph");
CAppUI::requireLibraryFile("jpgraph/src/jpgraph_line");

class AudiogrammeTonal extends Graph {
  /** @var self */
  static public $gauche = null;
  /** @var self */
  static public $droite = null;
  
  function setTitle($title) {
    $this->title->Set($title);
  }
  
  function AudiogrammeTonal($with_legend = true) {
    $frequences = CExamAudio::$frequences;
    
    $delta = $with_legend ? 75 : 0;
    
    // Setup the graph.
    $this->Graph(300 + $delta, 250, "auto"); 
       
    $this->SetScale("textlin", -120, 10);
    $this->SetMarginColor("lightblue");
    
    // Image setup
    //$this->img->SetAntiAliasing();
    
    $this->img->SetMargin(45, 20 + $delta, 30, 15);
    
    // Legend setup
    if ($with_legend) {
      $this->legend->Pos(0.02, 0.5, "right", "center");
      $this->legend->SetShadow("darkgray@0.5", 3);
      $this->legend->SetFont(FF_ARIAL, FS_NORMAL, 7);
      $this->legend->SetFillColor('white@0.3');
    }
    else {
      $this->legend->Hide();
    }
  
    // Title setup
    $this->title->SetFont(FF_ARIAL, FS_NORMAL, 10);
    $this->title->SetColor("darkred");
    
    //Setup X-axis labels
    $this->xgrid->Show(true);
    $this->xgrid->SetColor("lightgray", "lightgray:1.8");
    $this->xaxis->SetFont(FF_ARIAL, FS_NORMAL, 8);
    $this->xaxis->SetTickLabels($frequences);
    $this->xaxis->SetLabelSide(1);
    $this->xaxis->SetLabelMargin(22);
    
    
    // Setup Y-axis labels 
    $this->ygrid->Show(true, true);
    $this->ygrid->SetColor("lightgray", "lightgray:1.8");

    $this->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 8);
    $this->yaxis->SetLabelFormatString("%ddB");
    
    $this->yaxis->scale->ticks->Set(20, 10);
    $this->yaxis->scale->ticks->SupressZeroLabel(false);
    $this->yaxis->scale->ticks->SupressMinorTickMarks(false);
    
    // Empty plots for scale window
    foreach ($frequences as $value) {
      $datay[] = 100;
    }
    $p1 = new LinePlot($datay);
    $p1->SetCenter();
    
    $this->Add($p1);
  }
  
  function addAudiogramme($values, $value_name, $title, $mark_color, $mark_type, $mark_file = null, $line = true) {
    $frequences = CExamAudio::$frequences;

    $root = CAppUI::conf("root_dir");
    $image_file = "$root/images/icons/$mark_file"; 

    // Empty plot case
    $datay = $values;
    CMbArray::removeValue("", $datay);
    if (!count($datay)) {
      return;
    }
    
    $words = explode(" ", $this->title->t);
    $cote = $words[1];
    $labels = array();
    $jscalls = array();
      // Remove empty values to connect distant points
      $datax = array();
      $datay = array();
    foreach ($values as $key => $value) {
      if ($value !== "" && $value!== null) {
        $frequence = $frequences[$key];
        $jstitle = strtr($title, "\n", " ");
        $labels[] = "Modifier la valeur {$value}dB pour $jstitle � $frequence";
        $jscalls[] = "javascript:changeTonalValue('$cote','$value_name',$key)";
        
        $datay[] = - intval($value);
        $datax[] = "$key"; // Needs to be a string when null
      }
    }
    
    $p1 = new LinePlot($datay, $datax);
    $p1->mark->SetType($mark_type, $image_file, 1.0);
    $this->Add($p1);    
    
    // Create the first line
    $p1->SetColor($mark_color);
    $p1->SetCenter();
    $p1->SetLegend($title);
    $p1->SetWeight($line ? 1 : -10);
    
    $p1->SetCSIMTargets($jscalls, $labels);
    
    // Marks
    
    $p1->mark->SetColor($mark_color);
    $p1->mark->SetFillColor("$mark_color@0.6");
    $p1->mark->SetWidth(4);
    
  }
}

global $exam_audio,$reloadGraph;

if (!$reloadGraph || $reloadGraph=="gauche") {
  AudiogrammeTonal::$gauche = new AudiogrammeTonal(true);
  AudiogrammeTonal::$gauche->setTitle("Oreille gauche");
  AudiogrammeTonal::$gauche->addAudiogramme($exam_audio->_gauche_aerien, "aerien", "Conduction\na�rienne", "blue", MARK_FILLEDCIRCLE);
  AudiogrammeTonal::$gauche->addAudiogramme($exam_audio->_gauche_osseux, "osseux", "Conduction\nosseuse", "red", MARK_STAR);
  AudiogrammeTonal::$gauche->addAudiogramme(
    $exam_audio->_gauche_pasrep, "pasrep", "Pas de\nr�ponse", "green", MARK_DTRIANGLE, null, false
  );
  AudiogrammeTonal::$gauche->addAudiogramme(
    $exam_audio->_gauche_ipslat, "ipslat", "Stap�dien\nipsilat�ral", "black", MARK_IMG, "si.png", false
  );
  AudiogrammeTonal::$gauche->addAudiogramme(
    $exam_audio->_gauche_conlat, "conlat", "Stap�dien\ncontrolat�ral", "black", MARK_IMG, "sc.png", false
  );
}

if (!$reloadGraph || $reloadGraph=="droite") {
  AudiogrammeTonal::$droite = new AudiogrammeTonal(true);
  AudiogrammeTonal::$droite->setTitle("Oreille droite");
  AudiogrammeTonal::$droite->addAudiogramme($exam_audio->_droite_aerien, "aerien", "Conduction\na�rienne", "blue", MARK_FILLEDCIRCLE);
  AudiogrammeTonal::$droite->addAudiogramme($exam_audio->_droite_osseux, "osseux", "Conduction\nosseuse", "red", MARK_STAR);
  AudiogrammeTonal::$droite->addAudiogramme(
    $exam_audio->_droite_pasrep, "pasrep", "Pas de\nr�ponse", "green", MARK_DTRIANGLE, null, false
  );
  AudiogrammeTonal::$droite->addAudiogramme(
    $exam_audio->_droite_ipslat, "ipslat", "Stap�dien\nipsilat�ral", "black", MARK_IMG, "si.png", false
  );
  AudiogrammeTonal::$droite->addAudiogramme(
    $exam_audio->_droite_conlat, "conlat", "Stap�dien\ncontrolat�ral", "black", MARK_IMG, "sc.png", false
  );
}
