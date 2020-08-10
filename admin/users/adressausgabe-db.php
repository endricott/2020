<?php
require_once 'functions-db.php';
require_once 'functions-adressen-db.php';

// Anzahl anzuzeigender Adressen pro Seite
const PROSEITE = 5;

// Ausgabevariable initialisieren
$ausgabe = [
    'title' => 'Adressliste',   // Titel der HTML-Seite (auch h1-Tag)
];

// Starten der Session
session_start();

// Standardwerte für Sessionvariablen setzen
$_SESSION['suche'] = $_SESSION['suche'] ?? '';
$_SESSION['sort']  = $_SESSION['sort']  ?? 'nachname';
$_SESSION['dest']  = $_SESSION['dest']  ?? 'ASC';
$_SESSION['seite'] = $_SESSION['seite'] ?? '1';



/** @var array $adresse Daten der gespeicherten Adressen */
$adressen = [];

/** @var string $suche Suchstring aus Formular */
$suche = getParam('suche');
if(!is_null($suche)) {
    $_SESSION['suche'] = $suche;
    $_SESSION['seite'] = '1';
}

/** @var string $sort Sortierfeld aus Formular */
$sort = getParam('sort');
if(!is_null($sort)) {
    // Prüfen, ob alte Sortierung der neuen entspricht, dann Richtung umdrehen
    if($sort == $_SESSION['sort']) {
        $_SESSION['dest'] = 'ASC' == $_SESSION['dest'] ? 'DESC' : 'ASC';
    }
    else {
        $_SESSION['dest'] = 'ASC';
    }
    $_SESSION['sort'] = $sort;
    $_SESSION['seite'] = '1';
}

/** @var string $seite  Aktuell anzuzeigende Seite */
$seite = getParam('seite');
if(!is_null($seite)) {
    $_SESSION['seite'] = $seite;
}

   
// Verbindung zur Datenbank aufbauen
$db = dbConnect(); 

// Suchbedingung formulieren
$where = $_SESSION['suche'] ? "WHERE vorname LIKE '%{$_SESSION['suche']}%' OR  nachname LIKE '%{$_SESSION['suche']}%'" : '';


/*
 * Gesamtzahl gefundener Datensätze ermitteln
 */
/** @var int $anzahl  Anzahl gefundener Datensätze */
$anzahl = 0;

// SQL-Statement erzeugen
$sql = "SELECT id FROM adressen $where";

// SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
if($result = mysqli_query($db, $sql)) {
    // Anzahl der Treffer ermitteln
    $anzahl = mysqli_num_rows($result);
}
else {
    die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
}

// Anzahl der Seiten, aktuelle Seite sowie Suchoffset bestimmen
/** @var int $seiten  Anzahl der Seiten */
$seiten = ceil($anzahl / PROSEITE);

// aktuelle Seite prüfen
$_SESSION['seite'] = max($_SESSION['seite'], 1);
$_SESSION['seite'] = min($_SESSION['seite'], $seiten);

/** @var int $offset  Offset für anzuzeigende Datensätze */
$offset = ($_SESSION['seite'] - 1) * PROSEITE; 

// LIMIT-Klausel erstellen
$limit = "LIMIT $offset, " . PROSEITE;

/*
 * Gespeicherte Daten aus der Datenbank lesen
 */
// Sortierung formulieren
$order = "ORDER BY {$_SESSION['sort']} {$_SESSION['dest']}";

// SQL-Statement erzeugen
$sql = <<<EOT
    SELECT adressen.id,
           anrede,
           vorname, 
           nachname, 
           plz, 
           ort, 
           telefon, 
           DATE_FORMAT(geburtsdatum, '%d.%m.%Y') AS geburtstag,
           werke.werk
    FROM adressen 
    LEFT JOIN werke ON adressen.werk = werke.id
    $where
    $order
    $limit
EOT;

// SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
if($result = mysqli_query($db, $sql)) {
    // Alle Datensätze aus dem Resultset holen und in $adressen speichern
    while($adresse = mysqli_fetch_assoc($result)) {
        // Felder für die Ausgabe in HTML-Seite vorbereiten
        foreach($adresse as $key => $value) {
            $adresse[$key] = htmlspecialchars($value, ENT_DISALLOWED | ENT_HTML5 | ENT_QUOTES);
        }
        // Adresse an Adress-Array anhängen
        $adressen[] = $adresse;
    }

    // Resultset freigeben
    mysqli_free_result($result);
}
else {
    die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
}

// Verbindung zur Datenbank trennen
mysqli_close($db);

include TEMPLATES . 'html-kopf.phtml';
?>
<h3><?= $anzahl ?> Adresse<?= 1 == $anzahl ? '' : 'n' ?> gefunden</h3>
<table>
    <caption>
        <div>
            <div>
                <form action="<?= $_SERVER['PHP_SELF'] ?>" name="suche" method="get">
                    <label for="suche">Suche nach</label>
                    <input type="text" name="suche" id="suche" value="<?= $_SESSION['suche'] ?>">
                    <button type="submit">suchen</button>
                </form>
            </div>
            <div class="paginator">
                <a href="<?= $_SERVER['PHP_SELF'] ?>?seite=1">&lt;&lt;</a>
                <a href="<?= $_SERVER['PHP_SELF'] ?>?seite=<?= $_SESSION['seite'] > 1 ? ($_SESSION['seite']-1) : 1 ?>">&lt;</a>
                <a href="<?= $_SERVER['PHP_SELF'] ?>?seite=<?= $_SESSION['seite'] ?>"><?= $_SESSION['seite'] ?></a>
                <a href="<?= $_SERVER['PHP_SELF'] ?>?seite=<?= $_SESSION['seite'] < $seiten ? ($_SESSION['seite']+1) : $seiten ?>">&gt;</a>
                <a href="<?= $_SERVER['PHP_SELF'] ?>?seite=<?= $seiten ?>">&gt;&gt;</a>
            </div>
        </div>
    </caption>
    <tr>
        <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=anrede">Anrede</a></th>
        <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=vorname">Vorname</a></th>
        <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=nachname">Nachname</a></th>
        <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=plz">PLZ</a></th>
        <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=ort">Ort</a></th>
        <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=telefon">Telefon</a></th>
        <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=geburtsdatum">Geburtsdatum</a></th>
        <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=werk">Werk</a></th>
        <th class="nolink">Portrait</th>
        <th colspan="2">&nbsp;</th>
    </tr>
    <?php foreach($adressen as $adresse): ?>
    <tr>
        <?php foreach($adresse as $schluessel => $wert): ?>
            <?php if('portrait' == $schluessel): ?>
            <td><img src="<?= BILDER . $wert ?>" alt="" class="thumb"></td>
            <?php elseif('id' != $schluessel): ?>
            <td><?= $wert ?></td>
            <?php endif; ?>
        <?php endforeach; ?>
        <td><a  href="adresse-aendern-db.php?updateid=<?= $adresse['id'] ?>">ändern</a></td>
        <td><a href="adresse-loeschen-db.php?loeschid=<?= $adresse['id'] ?>">löschen</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<h4><a href="adressen-db.php">Adresse erfassen</a></h4>
<?php
include TEMPLATES . 'html-fuss.phtml';