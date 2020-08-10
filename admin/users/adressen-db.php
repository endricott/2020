<?php
require_once 'functions-db.php';
require_once 'functions-adressen-db.php';

// Ausgabevariable initialisieren
$ausgabe = [
    'title'  => 'Adresse erfassen', // Titel der HTML-Seite (auch h1-Tag)
    'form'   => [],                 // Array mit den Formulardaten
    'fehler' => [],                 // Array mit den Fehlermeldungen
];


/** @var string[] $form alle Formularfelder */
$form = [];

/** @var string[] $fehler Fehlermeldungen für Formularfelder */
$fehler = [];

/*
 * Werte sämtlicher Formularfelder holen
 */
$form['anrede']       = getParam('anrede');
$form['vorname']      = getParam('vorname');
$form['nachname']     = getParam('nachname');
$form['plz']          = getParam('plz');
$form['ort']          = getParam('ort');
$form['telefon']      = getParam('telefon');
$form['geburtsdatum'] = getParam('geburtsdatum');


/*
 * Prüfen, ob Formular abgeschickt wurde
 * Falls ja, dann weitere Prüfungen durchführen
 */
if(!empty($_GET['okbutton'])) {
    // Anrede prüfen
    if(!$form['anrede']) {
        $fehler['anrede'] = 'Bitte Anrede auswählen';
    }
    elseif('Frau' != $form['anrede'] && 'Herr' != $form['anrede']) {
        $fehler['anrede'] = 'Ungültige Anrede';
        $form['anrede'] = '';
    }
    
    // Vorname prüfen
    if(!$form['vorname']) {
        $fehler['vorname'] = 'Bitte Vornamen eingeben';
    }
    elseif(strlen($form['vorname']) < 2) {
        $fehler['vorname'] = 'Vorname muss mindestens zwei Zeichen lang sein';
    }
    elseif(strlen($form['vorname']) > 100) {
        $fehler['vorname'] = 'Vorname darf höchstens 100 Zeichen lang sein';
    }
    
    // Nachname prüfen
    if(!$form['nachname']) {
        $fehler['nachname'] = 'Bitte Nachnamen eingeben';
    }
    elseif(strlen($form['nachname']) < 2) {
        $fehler['nachname'] = 'Vorname muss mindestens zwei Zeichen lang sein';
    }
    elseif(strlen($form['nachname']) > 100) {
        $fehler['nachname'] = 'Ort darf höchstens 100 Zeichen lang sein';
    }
    
    // Postleitzahl prüfen
    if(!$form['plz']) {
        $fehler['plz'] = 'Bitte PLZ eingeben';
    }
    elseif(preg_match("/[^0-9]/", $form['plz']) || $form['plz'] < 100 || $form['plz'] > 99999) {
        $fehler['plz'] = 'Bitte eine gültige deutsche PLZ eingeben';
    }
    
    // Ort prüfen
    if(!$form['ort']) {
        $fehler['ort'] = 'Bitte Ort eingeben';
    }
    elseif(strlen($form['ort']) < 2) {
        $fehler['ort'] = 'Ort muss mindestens zwei Zeichen lang sein';
    }
    elseif(strlen($form['ort']) > 100) {
        $fehler['ort'] = 'Ort darf höchstens 100 Zeichen lang sein';
    }
    
    // Telefonnummer prüfen
    if(!$form['telefon']) {
        $fehler['telefon'] = 'Bitte Telefonnummer eingeben';
    }
    elseif(strlen($form['telefon']) > 100) {
        $fehler['telefon'] = 'Telefonnummer darf höchstens 30 Zeichen lang sein';
    }
    
    // Geburtsdatum prüfen (wenn angegeben)
    if($form['geburtsdatum']) {
        // Geburtsdatum extrahieren
        $jahr  = substr($form['geburtsdatum'], 0, 4);
        $monat = substr($form['geburtsdatum'], 5, 2);
        $tag   = substr($form['geburtsdatum'], 8, 2);
        // Datum auf allgemeine Gültigkeit prüfen
        if(!checkdate($monat, $tag, $jahr)) {
            $fehler['geburtsdatum'] = 'Bitte gültiges Geburtsdatum eingeben';
        }
        else {
            $achtzehn = mktime(0, 0, 0, date('n'), date('j'), intval(date('Y'))-18);
            $geburtstag = mktime(0, 0, 0, $monat, $tag, $jahr);
            if($geburtstag > $achtzehn) {
                $fehler['geburtsdatum'] = 'Mindestalter ist 18 Jahre';
            }
        }
    }
    else {
        // Nullwert setzen, wenn Geburtsdatum nicht angegeben wurde
        $form['geburtsdatum'] = null;
    }
    
    /*
     * Wenn keine Fehler in Formularfeldern gefunden
     */
    if(!count($fehler)) {
        /*
         * Erfasste Daten in eine Datenbank schreiben
         */
        // Verbindung zur Datenbank aufbauen
        $db = dbConnect();
        
        // Formularwerte für die Datenbank escapen
        foreach($form as $key => $value) {
            // Strings escapen
            if(is_string($value)) {
                $form[$key] = mysqli_real_escape_string($db, $value);
            }
        }
        
        // String für Geburtstage erzeugen (wegen möglichem NULL-Wert
        $geburtsdatum = is_null($form['geburtsdatum']) ? 'NULL' : "'${form['geburtsdatum']}'";
        
        // SQL-Statement erzeugen
        $sql = <<<EOT
        INSERT INTO adressen (anrede, vorname, nachname, plz, ort, telefon, geburtsdatum) 
        VALUES ('${form['anrede']}', '${form['vorname']}', '${form['nachname']}', '${form['plz']}', '${form['ort']}', '${form['telefon']}', $geburtsdatum)
EOT;
        
        // SQL-Statement an die Datenbank schicken
        mysqli_query($db, $sql) || die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
        
        // Erzeugte ID des geschriebenen Datensatzes ermitteln
        $id = mysqli_insert_id($db);

        // Verbindung zur Datenbank trennen
        mysqli_close($db);

        // Weiterleiten auf Bestätigungsseite, dabei die ID des erzeugten Datensatzes übergeben
        header("location: adressen-db-ok.php?id=$id");
    }
    /*
     * Wenn Fehler in Formularfeldern gefunden
     */
    else {
        // Formularfelder für die Ausgabe im Formular vorbereiten
        foreach($form as $key => $value) {
            if(is_string($value)) {
                $form[$key] = htmlspecialchars($value, ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
            }
        }
    }
}

// Formulardaten in Ausgabevariable schreiben
$ausgabe['form'] = $form;
// Fehlermeldungen in Ausgabevariable schreiben
$ausgabe['fehler'] = $fehler;

/*
 * Ausgabe der HTML-Seite
 */
include TEMPLATES . 'html-kopf.phtml';
include TEMPLATES . 'erfassenform.phtml';
include TEMPLATES . 'html-fuss.phtml';
