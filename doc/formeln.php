<?php
/*
 * Inhaltsangabe der Formelsammlung für:
 * 1. Häufig verwendete Konstanten
 * 2. Zugangsdaten zur Datenbank
 * 3. Verbindungaufbau zum Datenbankserver 
 * 4. Häufig verwendete Funktionen
 */
 
/* 
 * 1. Häufig verwendete Konstanten
 */
// Gewählte Konstanten für die Übertragungsmethode von GET und POST
// GET-Methode (nicht sichere Übertragung) soll hier vorwiegend für die Entwicklung dienlich sein
// Konstante 'VGET' steht für Verbindung-GET, Variable ist 'GET' (Erkennung durch Großbuchstaben)
const VGET              = 'GET';
// POST-Methode (gilt derzeit als sichere Übertragung)
// Konstante 'VPOST' steht für Verbindung-POST, Variable ist 'POST' (Erkennung durch Großbuchstaben)
const VPOST             = 'POST';

// Für Entwickler! 
// Gibt alle Informationen zu einer Variablen unter Verwendung von dump() oder var_dump() aus
// Konstante DV steht für dump() oder var_dump() und 'duv' steht für die Dump-Variable
const DV                = 'duv';
// Gibt die Informationen von Variablen in einer lesbaren Form aus
// Konstante PR steht für print_r() und 'druck' steht für die Dump-Variable
const PR                = 'druck';


/*
 * Platzhalter für weitere Konstanten und Variablen
 */


/* 
 * 2. Zugangsdaten zur Datenbank
 */
// Name bzw. Adresse des Datenbankservers
const SERVER            = 'localhost';
// Username für die Anmeldung beim Datenbankserver
const USER              = 'test';
// Passwort für die Anmeldung beim Datenbankserver
const PASSWORD          = 'kiklopas';
// Name der Datenbank
const NAME              = 'gruppe3';


/*
 * 3. Verbindungsaufbau zum Datenbankserver
*/
// Übertragung durch GET- und/ oder POST-Methode für Hypertext Transfer Protocol - Anfragen (Kurz: HTTP-Request)


// Funktion dline steht für direkt line unter Verwendung von der Variable $database
function dline()
{
// Abfrage der Fehlermeldung bei Verbindungsproblemen zur Datenbank
    if (!$database = mysqli_connect(SERVER, USER, PASSWORD, NAME)) 
    {
        die('Verbindungsfehler zur Datenbank! Bitte wenden Sie sich an Ihren IT-Experten. (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
    }
// Wenn keine Fehlermeldung, dann ...   
// Festgelegter Zeichensatz ist UTF8
mysqli_set_charset($database, 'UTF8');
// Rückgabe der Verbindung
    return $database; 
  
    
/*
 * 4. Häufig verwendete Funktionen
 */
    
    
}












