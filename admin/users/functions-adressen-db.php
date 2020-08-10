<?php
require_once 'functions-db.php';

// Verzeichnis für Bilder
const BILDER = './images/';

// Verzeichnis für Templates
const TEMPLATES = './templates/';

/**
 * Prüft und gibt zurück, ob eine ID in der Tabelle adressen existiert
 * 
 * @param int $id
 * @return bool
 */
function adressExist($id)
{
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect(); 
    
    /** @var bool $gefunden  Schalter, ob ID gefunden wurde oder nicht */
    $gefunden = false;

    // SQL-Statement erzeugen
    $sql = "SELECT id FROM adressen WHERE id = $id";
    
    // SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
    if($result = mysqli_query($db, $sql)) {
        
        // Schalter auf true setzen, wenn Datensatz gefunden wurde
        $gefunden = boolval(mysqli_num_rows($result));

        // Resultset freigeben
        mysqli_free_result($result);
    }
    else {
        die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
    }
    
    // Zurückgeben, ob Datensatz gefunden wurde oder nicht
    return $gefunden;
}
