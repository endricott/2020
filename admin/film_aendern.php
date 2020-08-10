<?php
require_once 'functions-db.php';
require_once 'functions-filmdb.php';

/** @var string[] $form alle Formularfelder */
$form = [];


//** @var string $updateid  ID des zu ändernden Datensatzes */
$updateid = getParam('updateid');

/** @var string[] $laender  Array mit allen Ländern */
$laender = getLaender();

/*
 *  Prüfen, ob ID zum Ändern übergeben wurde und ob ID korrekt ist
 */
if($updateid && filmExist($updateid)) {
    // ID zum Ändern wurde übergeben.
    // Datensatz aus DB lesen und zur Anzeige vorbereiten
    
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect();
    
    // SQL-Statement erzeugen
    $sql = "SELECT * FROM filme WHERE id = $updateid";
    
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
 * Prüfen, ob Formular abgeschickt wurde
 * Falls ja, dann weitere Prüfungen durchführen
 */
elseif(!empty($_GET['okbutton'])) {
    
    /** @var string[] $fehler Fehlermeldungen für Formularfelder */
    $fehler = [];

    /*
     * Werte sämtlicher Formularfelder holen
     */
    $form['id']       = getParam('id');
    $form['titel']    = getParam('titel');
    $form['inhalt']   = getParam('inhalt');
    $form['land']     = getParam('land');
    $form['premiere'] = getParam('premiere');
    $form['fsk']      = getParam('fsk');
    $form['laufzeit'] = getParam('laufzeit');

    // ID prüfen
    if(preg_match("/[^0-9]/", $form['id']) || $form['id'] < 1) {
        $fehler['id'] = 'Ungültige Datensatz-ID!';
    }
    elseif(!filmExist($form['id'])) {
        $fehler['id'] = 'Datensatz nicht gefunden!';
    }
    
    
    // Filmtitel prüfen
    if(!$form['titel']) {
        $fehler['titel'] = 'Bitte Filmtitel angeben';
    }
    elseif(strlen($form['titel']) > 255) {
        $fehler['titel'] = 'Filmtitel darf höchstens 255 Zeichen lang sein';
    }
    
    // Inhaltsangabe prüfen
    if(strlen($form['inhalt']) > 10000) {
        $fehler['inhalt'] = 'Inhaltsangabe darf höchstens 10.000 Zeichen lang sein';
    }
    
    // Land prüfen
    if(!$form['land']) {
        $fehler['land'] = 'Bitte Land auswählen';
    }
    elseif(!array_key_exists($form['land'], $laender)) {
        $fehler['land'] = 'Ungültiges Land eingeben';
        $form['land'] = null;
    }
    
    // Premierendatum prüfen (wenn angegeben)
    if($form['premiere']) {
        // Premierendatum extrahieren
        $jahr  = substr($form['premiere'], 0, 4);
        $monat = substr($form['premiere'], 5, 2);
        $tag   = substr($form['premiere'], 8, 2);
        // Datum auf allgemeine Gültigkeit prüfen
        if(!checkdate($monat, $tag, $jahr)) {
            $fehler['premiere'] = 'Bitte gültiges Premierendatum eingeben';
        }
    }
    else {
        // Nullwert setzen, wenn Geburtsdatum nicht angegeben wurde
        $form['premiere'] = null;
    }
    
    // Altersfreigabe prüfen (wenn angegeben)
    if(strlen($form['fsk']) && !in_array($form['fsk'], ['null', '0', '6', '12', '16', '18'])) {
        $fehler['fsk'] = 'Bitte gültige Altersfreigabe eingeben';
        $form['fsk'] = '';
    }
    elseif('null' == $form['fsk']) {
        // Nullwert setzen, wenn FSK nicht angegeben wurde
        $form['fsk'] = null;
    }
    
    // Laufzeit prüfen (wenn angegeben)
    if($form['laufzeit'] && !preg_match("/^(2[0-3]|[01]{0,1}[0-9])(:[0-5]{0,1}[0-9]){2}$/", $form['laufzeit'])) {
        $fehler['laufzeit'] = 'Bitte gültige Laufzeit eingeben';
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
        $premiere = is_null($form['premiere']) ? 'NULL' : "'${form['premiere']}'";
        
        // String für FSK erzeugen (wegen möglichem NULL-Wert)
        $fsk = is_null($form['fsk']) ? 'NULL' : $form['fsk'];
        
        // SQL-Statement erzeugen
        $sql = <<<EOT
        UPDATE filme
        SET titel    = '${form['titel']}',
            inhalt   = '${form['inhalt']}',
            land     = '${form['land']}',
            premiere = $premiere,
            fsk      = $fsk,
            laufzeit = '${form['laufzeit']}'
        WHERE id = ${form['id']}
EOT;
        
        // SQL-Statement an die Datenbank schicken
        mysqli_query($db, $sql) || die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
        
        // Verbindung zur Datenbank trennen
        mysqli_close($db);

        // Weiterleiten auf Bestätigungsseite, dabei die ID des erzeugten Datensatzes übergeben
        header("location: film-aendern-ok.php?id=" . $form['id']);
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
        <title>Film ändern</title>
        <meta charset="UTF-8">
        <link href="filmliste.css" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <h1>Film ändern!</h1>
            <?php if(!empty($fehler['id'])): ?>
            <h3><span><?= $fehler['id'] ?></span></h3>
            <h4><a href="filmliste.php">zurück zur Übersicht</a></h4>
            <?php else: ?>
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
                <input type="hidden" name="id" id="id" value="<?= $form['id'] ?>">
                
                <div>
                    <label for="titel" class="pflicht">Filmtitel</label>
                    <input type="text" name="titel" id="titel" value="<?= $form['titel'] ?>">
                    <span><?= $fehler['titel'] ?? '' ?></span>
                </div>

                <div>
                    <label for="inhalt">Inhalt</label>
                    <textarea name="inhalt" id="inhalt"><?= $form['inhalt'] ?></textarea>
                    <span><?= $fehler['inhalt'] ?? '' ?></span>
                </div>

                <div>
                    <label for="land" class="pflicht">Land</label>
                    <select name="land" id="land">
                        <option value="" label="Bitte auswählen"></option>
                        <?php foreach($laender as $kuerzel => $land): ?>
                        <option value="<?= $kuerzel ?>" <?= $kuerzel == $form['land'] ? 'selected' : '' ?>><?= $land ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span><?= $fehler['land'] ?? '' ?></span>
                </div>

                <div>
                    <label for="premiere">Premiere</label>
                    <input type="date" name="premiere" id="premiere" value="<?= $form['premiere'] ?>">
                    <span><?= $fehler['premiere'] ?? '' ?></span>
                </div>

                <div>
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
                </div>
                
                <div>
                    <label for="laufzeit">Laufzeit</label>
                    <input type="text" name="laufzeit" id="laufzeit" value="<?= $form['laufzeit'] ?>">
                    <span><?= $fehler['laufzeit'] ?? '' ?></span>
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
