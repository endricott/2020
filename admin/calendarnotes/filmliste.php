<?php
require_once 'functions-db.php';

/** @var array $filme Daten der gespeicherten Filme */
$filme = [];
const PROSEITE = 10;
//session start
session_start();

// standartwerte
//$_SESSION['suche'] = $_SESSION['suche'] ?? '';
//$_SESSION['sort'] = $_SESSION['sort'] ?? 'titel';
//$_SESSION['dest'] = $_SESSION['dest'] ?? 'ASC';
//$_SESSION['seite'] = $_SESSION['seite'] ?? '1';


dump($_SESSION);
/*
 *  Suchformular auswerten und die WHERE-Klausel f�r die Abfrage erstellen
 */
/** @var string $suche  Als Parameter übergebener Suchstring f�r das Titelfeld */
$suche = getParam('userid');
$titel = htmlspecialchars($suche, ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
$sort = getParam('order') ? getParam('order') : 'userid';
$dest = getParam('dest') ? getParam('dest') : 'ASC';
/** @var string $where  Abfragebedingung f�r die Filmsuche */
$where = $suche ? "WHERE f.userid LIKE '%$suche%'" : '';
$seite = getParam('seite') ? getParam('seite') : '1';
/*
 *  Sortierung für die Filmliste erstellen (Klick auf Kopfzeile der Tabelle)
 */
/** @var string $spalte  Sortierfeld für die Filmliste */
$spalte = getParam('order');

/** @var string $order  Sortierklausel für die Filmsuche */
$order = $spalte ? "ORDER BY f.$spalte ASC" : '';

// Verbindung zur Datenbank aufbauen
$db = dbConnect();

$anzahl = 0;
//SQL-Statement zum Ermitteln der Anzahl der gefundenen Filme
//$sql = "SELECT COUNT(id) AS anzahl FROM filme $where";
$sql = "SELECT id FROM calendarinfo AS f $where";
// SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
if($result = mysqli_query($db, $sql)) {
    $anzahl = mysqli_num_rows($result);
}
else {
    die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
}
      
$seiten = ceil($anzahl / PROSEITE);
$seite = max($seite, 1);
$seite = min($seite, $seiten);
$offset = ($seite - 1)*PROSEITE;
$limit = "LIMIT $offset, " . PROSEITE;
$order = "ORDER BY $sort $dest";
//SQL-Statement zum Lesen der anzuzeigenden Filme
$sql = <<<EOT
SELECT f.id, f.subject, f.userid, place, startdatetime, enddatetime, note, person, description, ittakes, longlat
FROM calendarinfo AS f
$where
$order
$limit
EOT;

// SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
if($result = mysqli_query($db, $sql)) {
    // Alle Datensätze aus dem Resultset holen und in $filme speichern
    while($film = mysqli_fetch_assoc($result)) {
        // Felder für die Ausgabe in HTML-Seite vorbereiten
        foreach($film as $key => $value) {
            $film[$key] = htmlspecialchars($value, ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
        }
        // Film an Filme-Array anhängen
        $filme[] = $film;
    }

    // Resultset freigeben
    mysqli_free_result($result);
}
else {
    die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
}

// Verbindung zum DB-Server schließen
mysqli_close($db);

$pag_opt = "?userid=$suche&order=$sort&dest=$dest&seite=";
// Ausgabe der Seite
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Filmliste</title>
        <link href="../../styles/style.css" rel="stylesheet">
    </head>
    <body>
            <h1>Kalenderliste</h1>
            <h3><?= number_format($anzahl, 0, ',', '.') ?> Eintr�ge gefunden</h3>
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get" class="suchform">
                <div>
                    <label for="titelsuche">Suche nach Thema</label>
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                    <input type="hidden" name="dest" value="<?= $dest ?>">
                    <input type="text" name="titel" id="titel" value="<?= $titel ?>">
                    <button type="submit" name="suchbutton" value="1">suchen</button>
                </div>
            </form>

            <table>
                <div class="paginator">
                    <a href="<?= $_SERVER['PHP_SELF'] . $pag_opt ?>1">&lt;&lt;</a>
                    <a href="<?= $_SERVER['PHP_SELF'] . $pag_opt ?><?= $seite == 1 ? 1 : $seite - 1?>">&lt;</a>
                    <a href="<?= $_SERVER['PHP_SELF'] . $pag_opt ?><?= $seite ?>"><?= $seite ?></a>
                    <a href="<?= $_SERVER['PHP_SELF'] . $pag_opt ?><?= $seite == $seiten ? $seiten : $seite + 1 ?>">&gt;</a>
                    <a href="<?= $_SERVER['PHP_SELF'] . $pag_opt ?><?= $seiten ?>">&gt;&gt;</a>
                </div>
                <tr>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=id&titel=<?= $titel ?>">ID</a></th>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=subject&titel=<?= $titel ?>">Thema</a></th>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=note&titel=<?= $titel ?>">Notiz</a></th>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=userid&titel=<?= $titel ?>">Kunden ID</a></th>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=person&titel=<?= $titel ?>">Persone</a></th>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=ittakes&titel=<?= $titel ?>">Zeit</a></th>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=place&titel=<?= $titel ?>">Platz</a></th>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=description&titel=<?= $titel ?>">Beschreibung</a></th>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=longlat&titel=<?= $titel ?>">Long Lat</a></th>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=startdatetime&titel=<?= $titel ?>">Start Datum</a></th>
                    <th><a href="<?= $_SERVER['PHP_SELF'] ?>?order=enddatetime&titel=<?= $titel ?>">Ende Datum</a></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
                <?php foreach($filme as $film): ?>
                <tr>
                    <?php foreach($film as $value): ?>
                    <td><?= $value ?></td>
                    <?php endforeach; ?>  
                    <td><a href="film_aendern.php?updateid=<?= $film['id'] ?>">bearbeiten</a></td>
                    <td><a href="film_loeschen.php?loeschid=<?= $film['id'] ?>">l�schen</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
    </body>
</html>


