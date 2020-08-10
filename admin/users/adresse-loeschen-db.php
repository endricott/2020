<?php
require_once 'functions-db.php';
require_once 'functions-adressen-db.php';


/** @var int $id ID des geschriebenen Datensatzes */
$loeschid = intval(getParam('loeschid'));

/** @var string $loeschok  Löschbestätigung */
$loeschok = getParam('loeschok');

/** @var string $fehler  Fehlermeldung */
$fehler = '';

/** @var string[] $adresse Daten der gespeicherten Adresse */
$adresse = [];

// Wenn gültige Datensatz-ID übergeben wurde
if($loeschid && adressExist($loeschid)) {
    
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect(); 
    
    // Wenn noch keine Lösch-Bestätigung gegeben wurde
    if(!$loeschok) {
        /*
         * Erfasste Daten aus der Datenbank lesen
         */

        // SQL-Statement erzeugen
        $sql = <<<EOT
            SELECT id,
                   anrede,
                   vorname, 
                   nachname, 
                   plz, 
                   ort, 
                   telefon, 
                   DATE_FORMAT(geburtsdatum, '%d.%m.%Y') AS geburtstag
            FROM adressen 
            WHERE id = $loeschid
    EOT;

        // SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
        if($result = mysqli_query($db, $sql)) {
            // Den ersten (und einzigen) Datensatz aus dem Resultset holen
            if($adresse = mysqli_fetch_assoc($result)) {
                // Felder für die Ausgabe in HTML-Seite vorbereiten
                foreach($adresse as $key => $value) {
                    $adresse[$key] = htmlspecialchars($value, ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
                }
            }

            // Resultset freigeben
            mysqli_free_result($result);
        }
        else {
            die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
        }
    }
    // Lösch-Bestätigung wurde gegeben
    else {
        /*
         * Datensatz löschen
         */
        // SQL-Statement erzeugen
        $sql = "DELETE FROM adressen WHERE id = $loeschid";
    
        // SQL-Statement an die Datenbank schicken
        mysqli_query($db, $sql) || die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
        
        // Verbindung zur Datenbank trennen
        mysqli_close($db);

        // Weiterleiten auf Bestätigungsseite
        header("location: adresse-loeschen-db-ok.php");
    }

    // Verbindung zur Datenbank trennen
    mysqli_close($db);
}
elseif(!adressExist($loeschid)) {
    // Datensatz mit dieser ID existiert nicht
    $fehler = 'Ungültige Datensatz-ID!';
}
else {
    // Datensatz-ID wurde nicht übergeben
    $fehler = 'Datensatz-ID fehlt!';
}


?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <title>Adresserfassung</title>
        <meta charset="UTF-8">
        <link href="adressen.css" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <h1>Datensatz löschen!</h1>
            <?php if($fehler): ?>
            <h3><span><?= $fehler ?></span></h3>

            <?php else: ?>
            <h4><span>Soll dieser Datentsatz wirklich gelöscht werden?</span></h4>
            <table>
                <?php foreach($adresse as $name => $wert): ?>
                <tr>
                    <th><?= ucfirst($name) ?></th>
                    <td><?= $wert ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <form action="<?= $_SERVER['PHP_SELF'] ?>">
                <input type="hidden" name="loeschid" value="<?= $adresse['id'] ?>">
                
                <div class="center">
                    <button type="submit" name="loeschok" value="1">löschen</button>
                </div>
            </form>
            <?php endif; ?>
            
            <h3><a href="adressausgabe-db.php">zurück zur Adressliste</a></h3>
        </div>
    </body>
</html>
