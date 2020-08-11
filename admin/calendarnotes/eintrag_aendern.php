<?php
require_once 'functions-db.php';
require_once 'functions-caldb.php';

/** @var string[] $form alle Formularfelder */
$form = [];


//** @var string $updateid  ID des zu ändernden Datensatzes */
$updateid = getParam('updateid');

/** @var string[] $laender  Array mit allen Ländern */
//$laender = getLaender();

/*
 *  Prüfen, ob ID zum Ändern übergeben wurde und ob ID korrekt ist
 */
if($updateid && calendarExist($updateid)) {
    // ID zum Ändern wurde übergeben.
    // Datensatz aus DB lesen und zur Anzeige vorbereiten
    
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect();
    
    // SQL-Statement erzeugen
    $sql = "SELECT * FROM calendarinfo WHERE id = $updateid";
    
    // SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
    if($result = mysqli_query($db, $sql)) {

        // den ersten (und einzigen) Datensatz aus dem Resultset holen
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
        die('DB-Fehler');
    }
}
/*
 * Pr?fen, ob Formular abgeschickt wurde
 * Falls ja, dann weitere Prüfungen durchführen
 */
elseif(!empty($_GET['okbutton'])) {
    
    /** @var string[] $fehler Fehlermeldungen für Formularfelder */
    $fehler = [];

    /*
     * Werte sämtlicher Formularfelder holen
     */
    $form['id']       = getParam('id');
    $form['subject']    = getParam('subject');
    $form['note']   = getParam('note');
    $form['userid']     = getParam('userid');
    $form['person'] = getParam('person');
    $form['ittakes']      = getParam('ittakes');
    $form['place'] = getParam('place');
    $form['description']    = getParam('description');
    $form['longlat']   = getParam('longlat');
    $form['startdatetime']     = getParam('startdatetime');
    $form['enddatetime'] = getParam('enddatetime');
    

    // ID prüfen
    if(preg_match("/[^0-9]/", $form['id']) || $form['id'] < 1) {
        $fehler['id'] = 'Ung?ltige Datensatz-ID!';
    }
    elseif(!calendarExist($form['id'])) {
        $fehler['id'] = 'Datensatz nicht gefunden!';
    }
    
    
    // kalenderthema pr?fen
    if(!$form['subject']) {
        $fehler['subject'] = 'Bitte Kalender Thema angeben';
    }
    elseif(strlen($form['subject']) > 255) {
        $fehler['subject'] = 'das Thema darf h?chstens 255 Zeichen lang sein';
    }
    
    // Inhaltsangabe prüfen
    if(strlen($form['description']) > 255) {
        $fehler['description'] = 'Inhaltsangabe darf h?chstens 255 Zeichen lang sein';
    }
    
//    // Land prüfen
//    if(!$form['land']) {
//        $fehler['land'] = 'Bitte Land auswählen';
//    }
//    elseif(!array_key_exists($form['land'], $laender)) {
//        $fehler['land'] = 'Ungültiges Land eingeben';
//        $form['land'] = null;
//    }
    
    // Premierendatum prüfen (wenn angegeben)
//    if($form['premiere']) {
//        // Premierendatum extrahieren
//        $jahr  = substr($form['premiere'], 0, 4);
//        $monat = substr($form['premiere'], 5, 2);
//        $tag   = substr($form['premiere'], 8, 2);
//        // Datum auf allgemeine Gültigkeit prüfen
//        if(!checkdate($monat, $tag, $jahr)) {
//            $fehler['premiere'] = 'Bitte gültiges Premierendatum eingeben';
//        }
//    }
//    else {
//        // Nullwert setzen, wenn Geburtsdatum nicht angegeben wurde
//        $form['premiere'] = null;
//    }
//    
    // Altersfreigabe prüfen (wenn angegeben)
//    if(strlen($form['fsk']) && !in_array($form['fsk'], ['null', '0', '6', '12', '16', '18'])) {
//        $fehler['fsk'] = 'Bitte gültige Altersfreigabe eingeben';
//        $form['fsk'] = '';
//    }
//    elseif('null' == $form['fsk']) {
//        // Nullwert setzen, wenn FSK nicht angegeben wurde
//        $form['fsk'] = null;
//    }
    
    // Laufzeit prüfen (wenn angegeben)
    if($form['ittakes'] && !preg_match("/^(2[0-3]|[01]{0,1}[0-9])(:[0-5]{0,1}[0-9]){2}$/", $form['ittakes'])) {
        $fehler['ittakes'] = 'Bitte g?ltige Laufzeit eingeben';
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
        
        // String für Premierendatum erzeugen (wegen möglichem NULL-Wert)
//        $premiere = is_null($form['premiere']) ? 'NULL' : "'${form['premiere']}'";
//        
//        // String für FSK erzeugen (wegen möglichem NULL-Wert)
//        $fsk = is_null($form['fsk']) ? 'NULL' : $form['fsk'];
        
        // SQL-Statement erzeugen
        $sql = <<<EOT
        UPDATE calendarinfo
        SET subject    = '${form['subject']}',
            description   = '${form['description']}',
            place     = '${form['userid']}',
            startdatetime = '${form['startdatetime']}',
            enddatetime = '${form['enddatetime']}',
            ittakes = '${form['ittakes']}'
        WHERE id = ${form['id']}
EOT;
        
        // SQL-Statement an die Datenbank schicken
        mysqli_query($db, $sql) || die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
        
        // Verbindung zur Datenbank trennen
        mysqli_close($db);

        // Weiterleiten auf Bestätigungsseite, dabei die ID des erzeugten Datensatzes übergeben
        header("location: eintrag-aendern-ok.php?id=" . $form['id']);
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

?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <title>passwort ?ndern</title>
        <meta charset="UTF-8">
        <link href="../../styles/style.css" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <h1>Eintrag ?ndern!</h1>
            <?php if(!empty($fehler['id'])): ?>
            <h3><span><?= $fehler['id'] ?></span></h3>
            <h4><a href="kundenlistesession.php">zur?ck zur ?bersicht</a></h4>
            <?php else: ?>
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
                <input type="hidden" name="id" id="id" value="<?= $form['id'] ?>">
                
                <div>
                    <label for="subject" class="pflicht">Thema</label>
                    <input type="text" name="subject" id="subject" value="<?= $form['subject'] ?>">
                    <span><?= $fehler['subject'] ?? '' ?></span>
                </div>

                <div>
                    <label for="note">Inhalt</label>
                    <textarea name="note" id="note"><?= $form['note'] ?></textarea>
                    <span><?= $fehler['note'] ?? '' ?></span>
                </div>

<!--                <div>
                    <label for="land" class="pflicht">Land</label>
                    <select name="land" id="land">
                        <option value="" label="Bitte auswählen"></option>
                        <?php foreach($laender as $kuerzel => $land): ?>
                        <option value="<?= $kuerzel ?>" <?= $kuerzel == $form['land'] ? 'selected' : '' ?>><?= $land ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span><?= $fehler['land'] ?? '' ?></span>
                </div>-->

                <div>
                    <label for="startdatetime">start date</label>
                    <input type="date" name="startdatetime" id="startdatetime" value="<?= $form['startdatetime'] ?>">
                    <span><?= $fehler['startdatetime'] ?? '' ?></span>
                </div>

<!--                <div>
                    <label for="fsk">FSK</label>
                    <select name="fsk" id="fsk">
                        <option value="null">unbekannt</option>
                        <option value="0"  <?= '0'  === $form['fsk'] ? 'selected' : '' ?>>0</option>
                        <option value="6"  <?= '6'  === $form['fsk'] ? 'selected' : '' ?>>6</option>
                        <option value="12" <?= '12' === $form['fsk'] ? 'selected' : '' ?>>12</option>
                        <option value="16" <?= '16' === $form['fsk'] ? 'selected' : '' ?>>16</option>
                        <option value="18" <?= '18' === $form['fsk'] ? 'selected' : '' ?>>18</option>
                    </select>
                    <span><?= $fehler['fsk'] ?? '' ?></span>
                </div>-->
                
                <div>
                    <label for="ittakes">Laufzeit</label>
                    <input type="text" name="ittakes" id="ittakes" value="<?= $form['ittakes'] ?>">
                    <span><?= $fehler['ittakes'] ?? '' ?></span>
                </div>

                <div>
                    <button type="submit" name="okbutton" value="1">speichern</button>
                </div>

                <div class='formcaption'>Felder in <span class='pflicht'>blau</span> sind Pflichtfelder</div>
            </form>
            <?php endif; ?>
        </div>
    </body>
</html>
