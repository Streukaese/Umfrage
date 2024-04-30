<?php
@session_start();
require_once 'db.php';

if (!isset($_POST['ausgewaehlte_antwort'])) {
  header('Location:index.php');
  exit;
}

$ausgewaehlteAntwortID=intval($_POST['ausgewaehlte_antwort']);

function generateUniqueToken() {
  return bin2hex(random_bytes(16));
}
$nutzertoken = generateUniqueToken();
setcookie('nutzertoken', $nutzertoken, time() + (86400 * 30), "/"); // 86400 Sekunden = 1 Tag

if ($_SESSION['fragenindex'] + 1 < count($_SESSION['fragen'])) {
} else {
  header('Location:danke.html');
  exit;
}

$_SESSION['fragenindex']++;

$fragenindex=$_SESSION['fragenindex']; 
$aktuelleFrage=$_SESSION['fragen'][$fragenindex]; 

if($aktuelleFrage->ausgewaehlteAntwortID != $ausgewaehlteAntwortID) {
  if(!isset($_SESSION['nutzertoken'])) {
      $db->query("INSERT INTO nutzertoken () VALUES ()");
      $_SESSION['nutzertoken']=$db->insert_id;
  }

  if($ausgewaehlteAntwortID == 0) {
      $db->query("DELETE FROM abgegebeneantwort WHERE nutzertokenid = ".$_SESSION['nutzertoken']." AND frageid = ".$aktuelleFrage->id);
  } else {
      $db->query("INSERT INTO abgegebeneantwort (nutzertokenid, frageid, antwortid) VALUES (".$_SESSION['nutzertoken'].", ".$aktuelleFrage->id.", ".$ausgewaehlteAntwortID.") ON DUPLICATE KEY UPDATE antwortid = VALUES(antwortid)");
  }

  $aktuelleFrage->ausgewaehlteAntwortID=$ausgewaehlteAntwortID;
}

header('Location:index.php');
exit;
?>