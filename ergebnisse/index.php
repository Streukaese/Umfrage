<?php
@session_start();
require_once '../db.php';

$fragen = array();
$result = $db->query("SELECT * FROM frage");
while ($frage = $result->fetch_object()) {
    $frage->antworten = array(); // Array für Antworten initialisieren
    $fragen[$frage->id] = $frage; // Frage in Liste und Map speichern
}
$result->free();

foreach ($fragen as $frage) {
  $result = $db->query(
    "SELECT ma.id, ma.antworttext, COUNT(aa.id) AS anzahl
    FROM moeglicheantwort ma
    LEFT JOIN abgegebeneantwort aa ON ma.id = aa.antwortid
    WHERE ma.frageid = $frage->id
    GROUP BY ma.id");
  
  while ($antwort = $result->fetch_object()) {
      $frage->antworten[] = $antwort;
  }
  $result->free();

  $result = $db->query(
    "SELECT COUNT(DISTINCT id) AS anzahl
    FROM nutzertoken
    WHERE id NOT IN (SELECT DISTINCT nutzertokenid FROM abgegebeneantwort WHERE frageid = $frage->id)");
  $row = $result->fetch_assoc();
  $frage->keineAntwortAnzahl = $row['anzahl'];
  $result->free();
}

$result = $db->query("SELECT COUNT(*) AS anzahl FROM nutzertoken");
$row = $result->fetch_assoc();
$gesamtNutzertokenAnzahl = $row['anzahl'];
$result->free();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Umfrageergebnisse</title>
</head>
<body>
    <h1>Umfrageergebnisse</h1>
    <ul>
<?php 
foreach ($fragen as $frage) { 
?>
      <li>
          Frage: <?= $frage->fragentext ?><br>
          Gesamtanzahl Antworten: <?= count($frage->antworten) ?><br>
        <ul>
<?php 
foreach ($frage->antworten as $antwort) { 
?>
           <li>
               Antwort: <?= $antwort->antworttext ?> -
               Anzahl: <?= $antwort->anzahl ?> -
               Prozentsatz: <?= round(($antwort->anzahl / $gesamtNutzertokenAnzahl) * 100, 2) ?>%
           </li>
<?php 
} 
?>
           <li>
              Keine Antwort - Anzahl: <?= $frage->keineAntwortAnzahl ?> -
              Prozentsatz: <?= round(($frage->keineAntwortAnzahl / $gesamtNutzertokenAnzahl) * 100, 2) ?>%
          </li>
        </ul>
      </li>
<?php
}
?>
    </ul>
    <a href="json_export.php" download>
  <button style="background-color:#007bff;color:white;border-radius:10px;padding:10px20px;border:none; cursor: pointer;">
    Ergebnisse herunterladen
  </button>
</a>

</body>
</html>