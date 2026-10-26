<?php
// Erabiltzailearen izena cookiean gorde edo berreskuratu.
if (isset($_POST["erabiltzailea"])) {
    $erabiltzailea = $_POST["erabiltzailea"];
    setcookie("erabiltzailea", $erabiltzailea, time() + 3600, "/");
} else {
    $erabiltzailea = $_COOKIE["erabiltzailea"] ?? "";
}
?>

<?php
require_once "filma.php";
require_once "filmak.php";

$filmak = new Filmak();
$mezua = "";
$emaitzak = [];

$izena = "";
$ISAN = "";
$urtea = "";
$puntuazioa = "0";

// Erabiltzaileak sartutako testua modu seguruan erakutsi.
function seguru($testua) {
    return htmlspecialchars((string)$testua, ENT_QUOTES, 'UTF-8');
}

// Ezkutuko eremuetatik filmak berreskuratu.
if (isset($_POST["filmak"]) && is_array($_POST["filmak"])) {
    foreach ($_POST["filmak"] as $f) {
        if (isset($f["izena"], $f["ISAN"], $f["urtea"], $f["puntuazioa"])) {
            $filmak->gehitu(
                new Filma($f["izena"], $f["ISAN"], $f["urtea"], $f["puntuazioa"])
            );
        }
    }
} else {
    // Hasierako filmak gehitu.
    $filmak->gehitu(new Filma("Superman", "12345678", "1978", "5"));
    $filmak->gehitu(new Filma("Batman", "23456789", "2022", "4"));
    $filmak->gehitu(new Filma("Batman vs. Superman", "34567890", "2016", "3"));
    $filmak->gehitu(new Filma("Superman & Lois", "45678901", "2021", "4"));
}

// Inprimakiaren datuak prozesatu.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $izena = trim($_POST["izena"] ?? "");
    $ISAN = trim($_POST["ISAN"] ?? "");
    $urtea = trim($_POST["urtea"] ?? "");
    $puntuazioa = $_POST["puntuazioa"] ?? "0";

    // Izena eta ISAN hutsik badaude, abisua erakutsi.
    if ($izena == "" && $ISAN == "") {
        $mezua = "Abisua: sartu izena edo ISAN zenbakia.";
    }
    // Izenaren bidez bilatu.
    elseif ($ISAN == "" && $izena != "") {
        $emaitzak = $filmak->bilatuIzenez($izena);
        if (count($emaitzak) == 0) {
            $mezua = "Ez da filmik aurkitu.";
        }
    }
    // ISANaren bidez filma ezabatu.
    elseif ($izena == "" && $ISAN != "") {
        if ($filmak->ezabatu($ISAN)) {
            $mezua = "Filma ezabatu da.";
        } else {
            $mezua = "Ez dago filmik aukeratutako ISANarekin.";
        }
    }
    // Dagoen filma eguneratu.
    elseif ($filmak->bilatuISAN($ISAN) != null) {
        if (
            $urtea != "" &&
            $puntuazioa !== "" &&
            ctype_digit((string)$puntuazioa) &&
            (int)$puntuazioa >= 0 &&
            (int)$puntuazioa <= 5
        ) {
            $filmak->eguneratu($ISAN, $izena, $puntuazioa);
            $mezua = "Filma eguneratu da.";
        } else {
            $mezua = "Abisua: bete eremu guztiak.";
        }
    }
    // Film berria gehitu.
    else {
        if (
            $izena != "" &&
            $ISAN != "" &&
            $urtea != "" &&
            $puntuazioa !== "" &&
            ctype_digit((string)$puntuazioa) &&
            (int)$puntuazioa >= 0 &&
            (int)$puntuazioa <= 5
        ) {
            if (!preg_match('/^[0-9]{8}$/', $ISAN)) {
                $mezua = "ISANak 8 zenbaki izan behar ditu.";
            } else {
                $filmak->gehitu(new Filma($izena, $ISAN, $urtea, $puntuazioa));
                $mezua = "Filma gehitu da.";
            }
        } else {
            $mezua = "Abisua: eremu guztiak bete behar dituzu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <title>TOP MOVIES</title>
</head>
<body>

    <h1><?php echo seguru($erabiltzailea); ?>-REN FILMAK</h1>
    <h2>Film zerrenda</h2>

    <table border="1">
        <tr>
            <th>Izena</th>
            <th>ISAN</th>
            <th>Urtea</th>
            <th>Puntuazioa</th>
        </tr>

        <?php foreach ($filmak->getFilmak() as $filma) { ?>
            <tr>
                <td><?php echo seguru($filma->getIzena()); ?></td>
                <td><?php echo seguru($filma->getISAN()); ?></td>
                <td><?php echo seguru($filma->getUrtea()); ?></td>
                <td><?php echo seguru($filma->getPuntuazioa()); ?></td>
            </tr>
        <?php } ?>
    </table>

    <p><?php echo seguru($mezua); ?></p>

    <?php if (count($emaitzak) > 0) { ?>
        <h2>Bilaketaren emaitzak</h2>
        <?php foreach ($emaitzak as $filma) { ?>
            <p>
                <?php echo seguru($filma->getIzena()); ?>
                (<?php echo seguru($filma->getUrtea()); ?>)
            </p>
        <?php } ?>
    <?php } ?>

    <h2>Filma gehitu, bilatu, eguneratu edo ezabatu</h2>

    <form method="POST" action="main.php">
        <!-- Erabiltzailearen izena hurrengo eskaeran mantendu. -->
        <input type="hidden" name="erabiltzailea"
            value="<?php echo seguru($erabiltzailea); ?>">

        <!-- Film guztien datuak hurrengo eskaeran mantendu. -->
        <?php foreach ($filmak->getFilmak() as $filma) { ?>
            <input type="hidden"
                name="filmak[<?php echo seguru($filma->getISAN()); ?>][izena]"
                value="<?php echo seguru($filma->getIzena()); ?>">
            <input type="hidden"
                name="filmak[<?php echo seguru($filma->getISAN()); ?>][ISAN]"
                value="<?php echo seguru($filma->getISAN()); ?>">
            <input type="hidden"
                name="filmak[<?php echo seguru($filma->getISAN()); ?>][urtea]"
                value="<?php echo seguru($filma->getUrtea()); ?>">
            <input type="hidden"
                name="filmak[<?php echo seguru($filma->getISAN()); ?>][puntuazioa]"
                value="<?php echo seguru($filma->getPuntuazioa()); ?>">
        <?php } ?>

        <label for="izena">Izena:</label>
        <input type="text" id="izena" name="izena"
            value="<?php echo seguru($izena); ?>">

        <br><br>

        <label for="ISAN">ISAN:</label>
        <input type="text" id="ISAN" name="ISAN"
            value="<?php echo seguru($ISAN); ?>">

        <br><br>

        <label for="urtea">Urtea:</label>
        <input type="text" id="urtea" name="urtea"
            value="<?php echo seguru($urtea); ?>">

        <br><br>

        <label for="puntuazioa">Puntuazioa:</label>
        <select id="puntuazioa" name="puntuazioa">
            <?php for ($i = 0; $i <= 5; $i++) { ?>
                <option value="<?php echo $i; ?>"
                    <?php if ((string)$puntuazioa === (string)$i) echo "selected"; ?>>
                    <?php echo $i; ?>
                </option>
            <?php } ?>
        </select>

        <br><br>

        <input type="submit" value="Bidali">
    </form>

</body>
</html>