<?php
require_once 'functions-db.php';
require_once 'functions-calmdb.php';


/** @var int $loeschid ID des geschriebenen Datensatzes */
$loeschid = intval(getParam('loeschid'));

/** @var string $loeschok  L�schbest�tigung */
$loeschok = getParam('loeschok');


/** @var string $fehler  Fehlermeldung */
$fehler = '';

/** @var string[] $one is Kalender eintrag */
$one = [];

// Wenn g�ltige Datensatz-ID �bergeben wurde
if($loeschid && calendarExist($loeschid)) {
    
    /*
     * Erfasste Daten aus der Datenbank lesen
     */
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect(); 

    
    if(!$loeschok) {
        // SQL-Statement erzeugen
        $sql = <<<EOT
            SELECT userid, 
               place, 
               DATE_FORMAT(startdatetime, '%d.%m.%Y') AS start,
               ittakes
               FROM calendarinfo 
            WHERE id = $loeschid
    EOT;

        // SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
        if($result = mysqli_query($db, $sql)) {
            // Den ersten (und einzigen) Datensatz aus dem Resultset holen
            if($one = mysqli_fetch_assoc($result)) {
                // Felder f�r die Ausgabe in HTML-Seite vorbereiten
                foreach($one as $key => $value) {
                    $one[$key] = htmlspecialchars($value, ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
                }
            }

            // Resultset freigeben
            mysqli_free_result($result);
        }
        else {
            die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
        }
    }
    // L�sch-Best�tigung erhalten
    else {
        /*
         * Datensatz l�schen
         */
        // SQL-Statement erzeugen
        $sql = "DELETE FROM calendarinfo WHERE id = $loeschid"; // WHERE NICHT VERGESSEN!!!
        
        // Statement an die DB schicken
        mysqli_query($db, $sql) || die('DB-Fehler');
        
        // Verbindung zur Datenbank trennen
        mysqli_close($db);
        
        // Weiterleiten auf Best�tigungsseite
        header("location: eintrag-loeschen-ok.php");
        
    }

    // Verbindung zur Datenbank trennen
    mysqli_close($db);
}
elseif(!$loeschid) {
    // Datensatz-ID wurde nicht �bergeben
    $fehler = 'Datensatz-ID fehlt!';
}
else {
    // Datensatz mit dieser ID existiert nicht
    $fehler = 'Ung�ltige Datensatz-ID!';
}



?>


<!DOCTYPE html>
<html lang="de">
    <head>
        <title>Eintrag l�schen</title>
        <meta charset="UTF-8">
        <link href="../../styles/style.css" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <h1>Eintrag l�schen!</h1>
            <?php if($fehler): ?>
            <h3><span><?= $fehler ?></span></h3>
            
            
            <?php else: ?>
            <h4><span>Soll dieser Eintrag wirklich gel�scht werden?</span></h4>
            
            <table>
                <?php foreach($one as $name => $wert): ?>
                <tr>
                    <th><?= ucfirst($name) ?></th>
                    <td><?= $wert ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
                <input type="hidden" name="loeschid" value="<?= $one['id'] ?>">
                <div class="center">
                    <button type="submit" name="loeschok" value="1">l�schen</button>
                </div>
            </form>
            
            <?php endif; ?>
            <h3><a href="kundenlistesession.php">zur�ck zur Eintrag</a></h3>
        </div>
    </body>
</html>