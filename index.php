<?php
@session_start();
require_once 'db.php';

  if (!isset($_SESSION['fragen'])) {
    $fragen=array();
    $fragenById = array();
    $result=$db->query("SELECT * FROM `frage` ");
    while ($frage=$result->fetch_object()) {
      $frage->moeglicheAntworten=array();
      $fragen[]=$frage;
      $fragenById[$frage->id] = $frage; 
    }
    $_SESSION['fragen']=$fragen;
    $result->free();

    foreach($fragen as $frage) {
      $frage->ausgewaehlteAntwortID=0;
  }
  $result=$db->query("SELECT * FROM `moeglicheantwort`");
  

  while ($antwort=$result->fetch_object()) {
    $fragenById[$antwort->frageid]->moeglicheAntworten[]=$antwort;
  }

  $result->free();

  if (isset($_COOKIE['nutzertoken'])) {
    $nutzertoken = $_COOKIE['nutzertoken'];
    $result = $db->query("SELECT * FROM `abgegebeneantwort` WHERE `nutzertokenid` = '$nutzertoken'");
    while ($antwort = $result->fetch_object()) {
        $frageID = $antwort->frageid;
        $ausgewaehlteAntwortID = $antwort->antwortid;
        if (isset($fragenById[$frageID])) {
            $fragenById[$frageID]->ausgewaehlteAntwortID = $ausgewaehlteAntwortID;
        }
    }
    $result->free();
  }
}

if (!isset($_SESSION['fragenindex']) || $_SESSION['fragenindex']>=count($_SESSION['fragen'])) {
    $_SESSION['fragenindex']=0;
}

$aktuelleFrage=$_SESSION['fragen'][$_SESSION['fragenindex']];


?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Umfrage</title>
</head>
<body>
<h1>Umfrage</h1>
<h2>Frage <?php echo $_SESSION['fragenindex'] + 1; ?>:</h2>
<p>Fragentext: <?= htmlentities($aktuelleFrage->fragentext, ENT_COMPAT) ?></p><br />
<form action="naechste_frage.php" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
  <ul>
<?php
foreach($aktuelleFrage->moeglicheAntworten as $antwort) {
  //echo count($aktuelleFrage->moeglicheAntworten);
?>
    <li>
      <input type="radio" name="ausgewaehlte_antwort" value="<?= htmlentities($antwort->id, ENT_COMPAT); ?>"/>
      <?= htmlentities($antwort->antworttext, ENT_COMPAT) ?>
    </li>
<?php
}
?>
    <li>
      <input type="radio" name="ausgewaehlte_antwort" value="0">
      Keine Antwort
    </li>
  </ul>
  <input type="submit" value="Nächste Frage"/>
</form>

<a href="vorige_frage.php">Vorige Frage</a><br /><br />

<a href="json_export.php" download>
  <button style="background-color:#007bff;color:white;border-radius:10px;padding:10px20px;border:none; cursor: pointer;">
    Ergebnisse herunterladen
  </button>
</a>


<div style="font-size:30px;color:red;">TODO</div>

</body>
</html>