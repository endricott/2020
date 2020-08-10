<?php
require_once 'functions-db.php';

/** @var int $id ID des geschriebenen Datensatzes */
$id = intval(getParam('id'));

/** @var string[] $film Daten des gespeicherten Films */
$film = [];

// Daten holen, wenn ID übergeben wurde
if($id) {
    
    /*
     * Erfasste Daten aus der Datenbank lesen
     */
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect(); 

    // SQL-Statement erzeugen
    $sql = <<<EOT
        SELECT titel,
               SUBSTR(inhalt,1,70) as inhalt, 
               land, 
               DATE_FORMAT(premiere, '%d.%m.%Y') AS premiere,
               fsk, 
               laufzeit
        FROM filme 
        WHERE id = $id
EOT;

    // SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
    if($result = mysqli_query($db, $sql)) {
        // Den ersten (und einzigen) Datensatz aus dem Resultset holen
        if($film = mysqli_fetch_assoc($result)) {
            // Felder für die Ausgabe in HTML-Seite vorbereiten
            foreach($film as $key => $value) {
                $film[$key] = htmlspecialchars($value, ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
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
        <title>Film ändern</title>
        <meta charset="UTF-8">
        <link href="../styles/style.css" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <h1>Die Änderung war erfolgreich</h1>
            <?php if($film): ?>
            <table>
                <caption>Sie haben folgende Daten eingegeben:</caption>
                <?php foreach($film as $name => $wert): ?>
                <tr>
                    <th><?= ucfirst($name) ?></th>
                    <td><?= $wert ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            <h3><a href="filmliste.php">Filmliste anzeigen</a></h3>
        </div>
    </body>
</html>
