<?php
/**
 * $Id: fileviewer.php 27188 2015-02-17 14:08:56Z charlyecho $
 *
 * @category Files
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision: 27188 $
 * @link     http://www.mediboard.org
 */

$user = CUser::get();

CAppUI::requireLibraryFile("phpThumb/phpthumb.class");
include_once "lib/phpThumb/phpThumb.config.php";

//require_once("./lib/phpThumb/phpthumb.class.php");
//trigger_error("Source is $file->_file");
    
ob_clean();

// Direct acces needs Administrator rights
$file_path = CValue::get("file_path");
if ($file_path) {
  $file_size = filesize($file_path);
  $file_type = "text/xml";
  $file_name = basename($file_path);
  
  if ($user->user_type == 1) {
    // BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
    // [http://bugs.php.net/bug.php?id=16173]
    header("Pragma: ");
    header("Cache-Control: ");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    // END extra headers to resolve IE caching bug
  
    header("MIME-Version: 1.0");
    header("Content-length: $file_size");
    header("Content-type: $file_type");
    header("Content-disposition: attachment; filename=\"".$file_name."\"");
    readfile($file_path);
    return;
  }
  else {
    CAppUI::setMsg("Permissions administrateur obligatoire", UI_MSG_ERROR);
    CAppUI::redirect();
  }
}

if ($file_id = CValue::get("file_id")) {
  $disposition = CValue::get("force_dl", 0);
  $disposition = $disposition ? "attachement" : "inline";
  $file = new CFile();
  $file->load($file_id);
  $file->loadRefsFwd();

  if ($file->object_class === "CCompteRendu") {
    $cr = $file->loadTargetObject();
    $cr->makePDFPreview();
  }

  if (!is_file($file->_file_path)) {
    header("Location: images/pictures/notfound.png");
    return;
  }
  elseif (!$file->canRead()) {
    header("Location: images/pictures/accessdenied.png");
    return;
  }

  // is the file a drawing draft ?
  if ($file->file_type == "image/fabricjs") {
    header("Location: modules/drawing/images/draft.png");
    CApp::rip();
  }
  elseif (CValue::get("phpThumb")) {
    
    $w  = CValue::get("w" , "");
    $h  = CValue::get("h" , "");
    $zc = CValue::get("zc" , "");
    $hp = CValue::get("hp", "");
    $wl = CValue::get("wl", "");
    $f  = CValue::get("f" , "png");
    $q  = CValue::get("q" , 80);
    $dpi = CValue::get("dpi" , 150);
    $sfn = CValue::get("sfn" , 0);
    
    //creation fin URL
    $finUrl="";

    if ($f) {
      $finUrl.="&f=$f";
    }
    if ($q) {
      $finUrl.="&q=$q";
    }

    if (strpos($file->file_type, "image") !== false && strpos($file->file_type, "svg") === false) {
      if ($hp) {
        $finUrl .= "&hp=$hp";
      }
      if ($wl) {
        $finUrl .= "&wl=$wl";
      }
      if ($h) {
        $finUrl .= "&h=$h";
      }
      if ($w) {
        $finUrl .= "&w=$w";
      }
      if ($zc) {
        $finUrl .= "&zc=$zc";
      }
      //trigger_error("Source is $file->_file_path$finUrl");
      header("Location: lib/phpThumb/phpThumb.php?src=$file->_file_path" . $finUrl);
    }
    elseif (strpos($file->file_type, "pdf") !== false) {

      if ($hp) {
        $finUrl .= "&h=$hp";
      }
      if ($wl) {
        $finUrl .= "&w=$wl";
      }

      if ($sfn) {
        $finUrl .= "&sfn=$sfn";
      }
      if ($dpi) {
        $finUrl .= "&dpi=$dpi";
      }

      if ($file->oldImageMagick() && ($file->rotation % 180 == 90)) {
        $w = intval($w * sqrt(2));
      }

      $finUrl .= "&ra={$file->rotation}";

      if ($h) {
        $finUrl .= "&h=$h";
      }
      if ($w) {
        $finUrl .= "&w=$w";
      }

      header("Location: lib/phpThumb/phpThumb.php?src=$file->_file_path" . $finUrl);
    }
    // vector image
    elseif (strpos($file->file_type, "svg") !== false) {
      header("Content-type: image/svg+xml");
      readfile($file->_file_path);
      CApp::rip();
    }
    elseif ($file->isPDFconvertible()) {
      if ($hp) {
        $finUrl .= "&h=$hp";
      }
      if ($wl) {
        $finUrl .= "&w=$wl";
      }
      if ($h) {
        $finUrl .= "&h=$h";
      }
      if ($w) {
        $finUrl .= "&w=$w";
      }
      if ($sfn) {
        $finUrl .= "&sfn=$sfn";
      }
      if ($dpi) {
        $finUrl .= "&dpi=$dpi";
      }
      
      $fileconvert = $file->loadPDFconverted();
      $success = 1;
      if (!$fileconvert->_id) {
        $success = $file->convertToPDF();
      }
      if ($success == 1) {
        $fileconvert = $file->loadPDFconverted();
        header("Location: lib/phpThumb/phpThumb.php?src=$fileconvert->_file_path".$finUrl);
      }
      else {
        header("Location: images/pictures/medifile.png");
      }
    }
    else {
      header("Location: images/pictures/medifile.png");
    }
  }
  else {
    // BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
    // [http://bugs.php.net/bug.php?id=16173]

    header("Pragma: ");
    header("Cache-Control: ");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    // END extra headers to resolve IE caching bug

    header("MIME-Version: 1.0");
    header("Content-length: {$file->doc_size}");
    header("Content-type: {$file->file_type}");

    header('Content-disposition: '.$disposition.'; filename="'.$file->file_name.'"');
    readfile($file->_file_path);
  }
}
else {
  CAppUI::setMsg("fileIdError", UI_MSG_ERROR);
  CAppUI::redirect();
}
