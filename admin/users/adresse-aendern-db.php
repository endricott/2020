<?php
require_once 'functions-db.php';
require_once 'functions-adressen-db.php';

// Ausgabevariable initialisieren
$ausgabe = [
    'title'  => 'Adresse ändern', // Titel der HTML-Seite (auch h1-Tag)
    'form'   => [],               // Array mit den Formulardaten
    'fehler' => [],               // Array mit den Fehlermeldungen
];

/** @var string[] $form alle Formularfelder */
$form = [];

/** @var string[] $fehler Fehlermeldungen für Formularfelder */
$fehler = [];

// Prüfen. ob formular abgeschickt wurde, oder ob ID zum Ändern übergeben wurde
// Übergebene ID holen
$updateid = getParam('updateid');

if($updateid && adressExist($updateid)) {
    // ID zum Ändern wurde übergeben.
    // Datensatz aus DB lesen und zur Anzeige vorbereiten
    
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect(); 

    // SQL-Statement erzeugen
    $sql = "SELECT * FROM adressen WHERE id = $updateid";

    // SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
    if($result = mysqli_query($db, $sql)) {
        // Den ersten (und einzigen) Datensatz aus dem Resultset holen
        if($form = mysqli_fetch_assoc($result)) {
            // Felder für die Ausgabe in HTML-Seite vorbereiten
            foreach($form as $key => $value) {
                $form[$key] = htmlspecialchars($value, ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
            }
        }
        
        // Resultset freigeben
        mysqli_free_result($result);
    }
    else {
        die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
    }

    // Verbindung zur Datenbank trennen
    mysqli_close($db);
}
elseif(!empty($_POST['okbutton'])) {


    /*
     * Werte sämtlicher Formularfelder holen
     */
    $form['id']           = intval(getParam('id', METHOD_POST));
    $form['anrede']       = getParam('anrede', METHOD_POST);
    $form['vorname']      = getParam('vorname', METHOD_POST);
    $form['nachname']     = getParam('nachname', METHOD_POST);
    $form['plz']          = getParam('plz', METHOD_POST);
    $form['ort']          = getParam('ort', METHOD_POST);
    $form['telefon']      = getParam('telefon', METHOD_POST);
    $form['geburtsdatum'] = getParam('geburtsdatum', METHOD_POST);
    $form['portrait']     = $_FILES['portrait'] ?? null;

    /*
     * Prüfungen der Feldwerte durchführen
     */
    // ID prüfen
    if(preg_match("/[^0-9]/", $form['id']) || $form['id'] < 1) {
        $fehler['id'] = 'Ungültige Datensatz-ID!';
    }
    elseif(!adressExist($form['id'])) {
        $fehler['id'] = 'Datensatz-ID nicht gefunden!';
    }
    
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
    }
    else {
        // Nullwert setzen, wenn Geburtsdatum nicht angegeben wurde
        $form['geburtsdatum'] = null;
    }
    
    // Portraitbild prüfen
    if(!empty($form['portrait']['tmp_name'])) {
        // Dateierweiterung prüfen
        $extension = strtolower(pathinfo($form['portrait']['name'], PATHINFO_EXTENSION));
        if(!in_array($extension, ['jpg', 'jpeg'])) {
            $fehler['portrait'] = "Ungültige Dateierweiterung";
        }
        // Dateityp prüfen
        $typ = exif_imagetype($form['portrait']['tmp_name']);
        if($typ != IMAGETYPE_JPEG) {
            $fehler['portrait'] = "Bitte nur jpg-Bilder hochladen";
        }
        // Dateigröße prüfen
        if($form['portrait']['tmp_name'] > 500 * 1024) {
            $fehler['portrait'] = "Bild darf höchstens 500KB groß sein";
        }
        
        // Datei in Bildordner verschieben, wenn kein Fehler aufgetreten ist
        if(empty($fehler['portrait'])) {
            $bildname = uniqid() . ".jpg";
            move_uploaded_file($form['portrait']['tmp_name'], BILDER . $bildname);
            $form['portrait'] = $bildname;
        }
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
        
        // String für Geburtstage erzeugen (wegen möglichem NULL-Wert)
        $geburtsdatum = is_null($form['geburtsdatum']) ? 'NULL' : "'${form['geburtsdatum']}'";
        
        // String für Portraitbild erzeugen (wegen möglichem NULL-Wert)
        $portrait = is_string($form['portrait']) ? "'${form['portrait']}'" : 'NULL';
        
        
        
        // SQL-Statement erzeugen
        $sql = <<<EOT
        UPDATE adressen 
        SET anrede       = '${form['anrede']}', 
            vorname      = '${form['vorname']}', 
            nachname     = '${form['nachname']}', 
            plz          = '${form['plz']}', 
            ort          = '${form['ort']}', 
            telefon      = '${form['telefon']}', 
            geburtsdatum = $geburtsdatum,
            portrait     = $portrait
        WHERE id = ${form['id']}
EOT;
        
        // SQL-Statement an die Datenbank schicken
        mysqli_query($db, $sql) || die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
        
        // Verbindung zur Datenbank trennen
        mysqli_close($db);

        // Weiterleiten auf Bestätigungsseite, dabei die ID des erzeugten Datensatzes übergeben
        header("location: adresse-aendern-db-ok.php?id=" . $form['id']);
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
else {
    // Weder gültige Datensatz-ID zum Ändern wurde übergeben, noch wurde das Formular abgeschickt
    $fehler['id'] = 'Datensatz-ID fehlt oder Datensatz nicht gefunden!';
}


// Formulardaten in Ausgabevariable schreiben
$ausgabe['form'] = $form;
// Fehlermeldungen in Ausgabevariable schreiben
$ausgabe['fehler'] = $fehler;

/*
 * Ausgabe der HTML-Seite
 */
$ausgabe['include'] = 'aendernform.phtml';
include TEMPLATES . 'htmlgeruest.phtml';
//include TEMPLATES . 'aendernform.phtml';
//include TEMPLATES . 'html-fuss.phtml';