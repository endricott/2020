<?php
/**
 * Bibliothek mit allgemeinen Funktionen
 */

/** @const var_dump() als Methode für Variablen-Dumps */
const DUMP_VARDUMP = 'v';

/** @const print_r() als Methode für Variablen-Dumps */
const DUMP_PRINTR  = 'p';

/** @const Übertragungsmethode GET bei HTTP-Request */
const METHOD_GET  = 'GET';

/** @const Übertragungsmethode POST bei HTTP-Request */
const METHOD_POST = 'POST';

/*
 *  Zugangsdaten für den Datenbankserver
 */
/** @const string Name/Adresse des Datenbankservers */
const DB_SERVER = 'localhost';

/** @const string Username für Anmeldung am Datenbankserver */
const DB_USER = 'test';

/** @const string Passwort für Anmeldung am Datenbankserver */
const DB_PASSWORD = 'kiklopas';

/** @const string Name der Datenbank */
const DB_NAME = 'caldb';


/**
 * Gibt einen Dump der übergebenen Variable in einem präformatierten HTML-Block aus
 *
 * @param  mixed   $varToDump  Variable, deren Dump ausgegeben wird
 * @param  string  $title      Titelzeile für die Ausgabe
 * @param  string  $method     [DUMP_VARDUMP] Dump-Methode
 */
function dump($varToDump, $title = '', $method = DUMP_VARDUMP) 
{
    // Block für präformatierten Text öffnen
    echo '<pre>';
    // Ausgabe des Titels, falls angegeben
    if($title) {
        echo '<strong><u>'.(string) $title.':</u></strong><br>';
    }
    // Dump der Variablen mit angeforderter Funktion
    if(DUMP_PRINTR == $method) {
        print_r($varToDump);
    }
    else {
        var_dump($varToDump);
    }
    echo '</pre>';
}

/**
 * Gibt einen Dump der übergebenen Variable in einem präformatierten HTML-Block aus
 * und beendet dann die Programmausführung
 *
 * @param  mixed   $varToDump  Variable, deren Dump ausgegeben wird
 * @param  string  $title      Titelzeile für die Ausgabe
 * @param  string  $method     [DUMP_VARDUMP] Dump-Methode
 */
function dieDump($varToDump, $title = '', $method = DUMP_VARDUMP) 
{
    // Dump der Variablen
    dump($varToDump, $title, $method);
    // Programmausführung beenden
    die;
}

/**
 * Liefert einen Wert, der mittels der Methode 'get' übergeben wurde.
 * 
 * @param   string  $name    Name des Datenfeldes
 * @param   string  $method  [METHOD_GET] Art der Übermittlung, METHOD_GET oder METHOD_POST
 * @return  mixed           Inhalt des übergebenen Feldes, bei Fehler NULL
 */
function getParam($name, $method = METHOD_GET)
{
    // Wert des Parameters holen, dabei überflüssige Leerzeichen entfernen
    if(METHOD_POST == $method) {
        $param = $_POST[$name] ?? NULL;  // NULL-Coalescing-Operator
    }
    else {
        $param = $_GET[$name] ?? NULL;
    }

    /*
     * Wert weiter verarbeiten
     */
    if(is_array($param))    // Alle Werte von Arrays verarbeiten
    {		
        for($i = 0; $i < count($param); $i++) 
        {
            // Überflüssige Leerzeichen entfernen
            $param[$i] = trim($param[$i]);
            // HTML-Tags entfernen
            $param[$i] = strip_tags($param[$i]);
            // HTML-Entities ersetzen
            // $param[$i] = htmlspecialchars($param[$i], ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
        }
    }
    elseif(NULL !== $param) // alle übrigen nicht-leeren Werte verarbeiten
    {	
        // Überflüssige Leerzeichen entfernen
        $param = trim($param);
        // HTML-Tags entfernen
        $param = strip_tags($param);
        // HTML-Entities ersetzen
        // $param = htmlspecialchars($param, ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
    }
	
    // Wert des Parameters zurückgeben
    return $param;
}

/**
 * Gibt eine Verbindung zur Datenbank zurück
 * 
 * @return mysqli
 */
function dbConnect()
{
    // Verbindung zur Datenbank aufbauen
    if (!$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME)) {
        die('DB-Verbindungsfehler (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
    }

    // Zeichensatz für die Verbindung explizit festlegen
    mysqli_set_charset($db, 'UTF8');
    
    // Verbindung zurückgeben
    return $db;
}
