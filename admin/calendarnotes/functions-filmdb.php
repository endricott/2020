<?php
/**
 * Bibliothek mit speziellen Funktionen f�r die calendarinfo
 */
require_once 'functions-db.php';


/**
 * Pr�ft und gibt zur�ck, od eine ID in der Tabelle Kalender existiert
 * 
 * @param string|int $id
 * @return bool
 */
function calendarExist($id)
{
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect();
    
    /** @var bool $gefunden  Schalter, ob Datensatz gefunden wurde */
    $gefunden = false;

    // SQL-Statement erzeugen
    $sql = "SELECT id FROM calendarinfo WHERE id = $id";

    // SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
    if($result = mysqli_query($db, $sql)) {

        // Schalter, ob Datensatz gefunden wurde
        $gefunden = boolval(mysqli_num_rows($result));

        // Resultset freigeben
        mysqli_free_result($result);
    }
    else {
        die('DB-Fehler');
    }

    // Datenbank schließen
    mysqli_close($db);
    
    // Zurückgeben des Ergebnisses
    return $gefunden;
}

/**
 * Liefert ein Assoziatives Array mit allen Ländern, wobei der Schlüssel die ID ist und
 * der Wert die Bezeichnung des Landes
 * 
 * @return string[]
 */
//function getLaender()
//{
//    // Verbindung zur Datenbank aufbauen
//    $db = dbConnect();
//    
//    /** @var string[] $laender  Assoziatives Array mit den Ländern */
//    $laender = [];
//
//    // SQL-Statement erzeugen
//    $sql = "SELECT id, bezeichnung FROM laender ORDER BY bezeichnung ASC";
//
//    // SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
//    if($result = mysqli_query($db, $sql)) {
//
//        // Datensätze auslesen und in Ergebnisarray speichern
//        while($land = mysqli_fetch_assoc($result)) {
//            $laender[$land['id']] = htmlspecialchars($land['bezeichnung'], ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
//        }
//
//        // Resultset freigeben
//        mysqli_free_result($result);
//    }
//    else {
//        die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
//    }
//
//    // Datenbank schließen
//    mysqli_close($db);
//    
//    // Zurückgeben des Ergebnisses
//    return $laender;
//}