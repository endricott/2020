<?php
require_once 'functions-db.php';


/** @var int $id ID des geschriebenen Datensatzes */
$id = intval(getParam('id'));

/** @var string[] $adresse Daten der gespeicherten Adresse */
$adresse = [];

// Daten holen, wenn ID übergeben wurde
if($id) {
    
    /*
     * Erfasste Daten aus der Datenbank lesen
     */
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect(); 

    // SQL-Statement erzeugen
    $sql = <<<EOT
        SELECT anrede,
               vorname, 
               nachname, 
               plz, 
               ort, 
               telefon, 
               DATE_FORMAT(geburtsdatum, '%d.%m.%Y') AS geburtstag
        FROM adressen 
        WHERE id = $id
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

    // Verbindung zur Datenbank trennen
    mysqli_close($db);

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
            <h1>Vielen Dank für Ihre Anmeldung</h1>
            <?php if($adresse): ?>
            <table>
                <caption>Sie haben folgende Daten eingegeben:</caption>
                <?php foreach($adresse as $name => $wert): ?>
                <tr>
                    <th><?= ucfirst($name) ?></th>
                    <td><?= $wert ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <h3><a href="adressen-db.php">Weitere Adresse erfassen</a></h3>
            <h3><a href="adressausgabe-db.php">Adressliste anzeigen</a></h3>
        </div>
    </body>
</html>
