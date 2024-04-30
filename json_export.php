<?php
@session_start();
require_once 'db.php';

$fragen = array();
$result = $db->query("SELECT * FROM `frage`");
while ($row = $result->fetch_object()) {
    $frage = new stdClass();
    $frage->id = $row->id;
    $frage->fragentext = $row->fragentext;
    $frage->moeglicheAntworten = array();
    $antwortResult = $db->query("SELECT * FROM `moeglicheantwort` WHERE `frageid` = $row->id");
    while ($antwort = $antwortResult->fetch_object()) {
        $frage->moeglicheAntworten[] = $antwort;
    }
    $antwortResult->free();
    
    $fragen[] = $frage;
}
$result->free();

$jsonData = json_encode($fragen);

$filename = "umfrageergebnisse_" . date('Y-m-d') . ".json";

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($jsonData));

echo $jsonData;
?>
