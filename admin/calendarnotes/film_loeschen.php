<?php
require_once 'functions-db.php';
require_once 'functions-filmdb.php';


/** @var int $loeschid ID des geschriebenen Datensatzes */
$loeschid = intval(getParam('loeschid'));

/** @var string $loeschok  Löschbestätigung */
$loeschok = getParam('loeschok');


/** @var string $fehler  Fehlermeldung */
$fehler = '';

/** @var string[] $film Daten des gespeicherten Films */
$film = [];

// Wenn gültige Datensatz-ID übergeben wurde
if($loeschid && filmExist($loeschid)) {
    
    /*
     * Erfasste Daten aus der Datenbank lesen
     */
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect(); 

    
    if(!$loeschok) {
        // SQL-Statement erzeugen
        $sql = <<<EOT
            SELECT id, 
                   titel,
                   SUBSTR(inhalt,1,40) AS inhalt, 
                   land, 
                   DATE_FORMAT(premiere, '%d.%m.%Y') AS premiere, 
                   fsk, 
                   laufzeit
            FROM filme 
            WHERE id = $loeschid
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
    }
    // Lösch-Bestätigung erhalten
    else {
        /*
         * Datensatz löschen
         */
        // SQL-Statement erzeugen
        $sql = "DELETE FROM filme WHERE id = $loeschid"; // WHERE NICHT VERGESSEN!!!
        
        // Statement an die DB schicken
        mysqli_query($db, $sql) || die('DB-Fehler');
        
        // Verbindung zur Datenbank trennen
        mysqli_close($db);
        
        // Weiterleiten auf Bestätigungsseite
        header("location: film-loeschen-ok.php");
        
    }

    // Verbindung zur Datenbank trennen
    mysqli_close($db);
}
elseif(!$loeschid) {
    // Datensatz-ID wurde nicht übergeben
    $fehler = 'Datensatz-ID fehlt!';
}
else {
    // Datensatz mit dieser ID existiert nicht
    $fehler = 'Ungültige Datensatz-ID!';
}



?>


<!DOCTYPE html>
<html lang="de">
    <head>
        <title>Film löschen</title>
        <meta charset="UTF-8">
        <link href="filmliste.css" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <h1>Film löschen!</h1>
            <?php if($fehler): ?>
            <h3><span><?= $fehler ?></span></h3>
            
            
            <?php else: ?>
            <h4><span>Soll dieser Film wirklich gelöscht werden?</span></h4>
            
            <table>
                <?php foreach($film as $name => $wert): ?>
                <tr>
                    <th><?= ucfirst($name) ?></th>
                    <td><?= $wert ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
                <input type="hidden" name="loeschid" value="<?= $film['id'] ?>">
                <div class="center">
                    <button type="submit" name="loeschok" value="1">löschen</button>
                </div>
            </form>
            
            <?php endif; ?>
            <h3><a href="filmliste.php">zurück zur Filmliste</a></h3>
        </div>
    </body>
</html>
