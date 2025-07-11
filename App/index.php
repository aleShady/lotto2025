<!DOCTYPE html>
<html>
<head>
    <title>LOTTO</title>
    <style type="text/css">
        @font-face {
            font-family: Lato;
            src: url(css/Lato/Lato-Regular.ttf);
        }
        @font-face {
            font-family: Lato;
            font-weight: bold;
            src: url(css/Lato/Lato-Bold.ttf);
        }
        body {
            font-family: Lato;
            background: #fff;
            margin: 0;
        }
        .header {
            width: 100%;
            background: #ffffff;
            padding: 15px 0;
            border-bottom: 2px solid #FF5C01;
            margin-bottom: 25px;
        }
        .item {
            display: inline-block;
            background: #ffffff;
            width: 200px;
            height: 150px;
            margin: 0 20px;
            border-radius: 5px;
            box-shadow: 1px 1px 3px 1px rgba(0,0,0,.2);
            vertical-align: top;
            cursor: pointer;
            transition: opacity .4s ease-in-out;
        }
        .item:hover {
            opacity: 1;
        }
        .title {
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 0;
            width: 90%;
            color: #555;
            border-bottom: 1px solid rgba(255,92,1,.3);
        }
        .content {
            width: 90%;
            text-align: center;
            padding-top: 20px;
            color: #777;
        }
    </style>
</head>
<body>

<?php
// Abilita errori in sviluppo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Carica configurazione
$config = include(__DIR__ . '/../config/config.php');
$env = $config['env'];
$apiBase = $config['urls'][$env];

include_once '../Classes/DBM.php';

$myYear = date("Y");
$dbm = new DBM();

// Pulisci le tabelle
$dbm->write("DELETE FROM year$myYear");
$dbm->write("DELETE FROM quad$myYear");

// Recupera dati dal microservizio proxy
$values = getFileMrk($myYear);
if (!$values || !is_array($values)) {
    die("Errore: impossibile leggere i dati dal proxy.");
}

// Estrai nomi ruote
$ruote = $values[0]['th'];
$arrayruote = array_slice($ruote, 1);

$estrazioni_Arr = $values;
$estrazioni = count($estrazioni_Arr) - 1; // Salta header
$f = 0;

foreach ($estrazioni_Arr as $row) {
    if ($f++ == 0) continue;

    $tmpEst = --$estrazioni;
    $tmpData = getData($row['data']) . $myYear;
    $aux = [];

    foreach ($arrayruote as $index => $ruota) {
        $estraz = $row['valori'][$index] ?? null;
        $cells = preg_replace('/\s+/', '.', trim($estraz));

        if (!in_array($cells, ["00.00.00.00.00", "00.00.00.00.00 "]) && strlen($cells) > 1) {
            $aux[$ruota] = str_replace(' ', '', $cells);
        }
    }

    if (!empty($aux)) {
        $sql = "INSERT INTO year$myYear VALUES($tmpEst, '$tmpData', '" . json_encode($aux) . "')";
        $dbm->write($sql);
    }

    foreach ($aux as $ruota => $valori) {
        $estrat = explode('.', $valori);
        for ($i = 0; $i < 5; $i++) {
            for ($j = $i + 1; $j < 5; $j++) {
                if (getDistance($estrat[$i], $estrat[$j]) === 'x') {
                    $tripla = getTripla($estrat[$i]);
                    $val1 = (int)$estrat[$i];
                    $val2 = (int)$estrat[$j];
                    $distanza = ($i + 1) . '-' . ($j + 1);
                    $dbm->write("INSERT INTO quad$myYear VALUES('$tmpData','$ruota',$tmpEst,'$distanza','$tripla',$val1,$val2)");
                }
            }
        }
    }
}

function getData($str) {
    $space = strpos($str, ' ');
    $day = substr($str, 0, $space);
    $mon = substr($str, $space + 1);
    if (strlen($day) == 1) $day = '0' . $day;
    return $day . ' ' . $mon . ' ';
}

function getFileMrk($year) {
    $urlBase = $config['urls'][$config['env']];
    $url = $urlBase . '?year=' . $year;

    $response = file_get_contents($url);
    if (!$response) {
        die("Errore nel recupero dati JSON");
    }

    return json_decode($response, true);
}

function getDistance($x, $y) {
    $tmp = abs($x - $y);
    $aux = ($tmp > 45) ? 90 - $tmp : $tmp;
    return ($aux == 3) ? 'x' : $aux;
}

function getTripla($val) {
    $value = (isset($val[1])) ? $val[1] + $val[0] : $val[0];
    if ($value > 9) $value -= 9;

    if (in_array($value, [1, 4, 7])) return '1-4-7';
    if (in_array($value, [2, 5, 8])) return '2-5-8';
    if (in_array($value, [3, 6, 9])) return '3-6-9';
    return '';
}
?>

<center>
    <div class="header">
        <img src="img/logo.png" width="30%" />
    </div>
    <div class="item" url="estrazioni"><div class="title">estrazioni</div><div class="content">Elenco di tutte le estrazioni dal 1871 ad oggi.</div></div>
    <div class="item" url="quadrature"><div class="title">quadrature</div><div class="content">Domenico</div></div>
    <div class="item" url="modulo_uno"><div class="title">modulo 1</div><div class="content">Calcolo di uscite in base a configurazione</div></div>
    <div class="item" url="modulo_due"><div class="title">modulo 2</div><div class="content">Calcolo delle sestine</div></div>
    <div class="item" url="modulo_tre"><div class="title">modulo 3</div><div class="content">Tabelloni ambi, terne e quaterne</div></div>
    <div class="item" style="margin-top: 30px;" url="TotaliSestine"><div class="title">Sestine</div><div class="content">Elenco sestine</div></div>
    <div class="item" style="margin-top: 30px;" url="elencoSestine"><div class="title">Trova pagine sestine</div><div class="content">Trova pagine sestine</div></div>
    <div class="item" style="margin-top: 30px;" url="nSestine"><div class="title">Trova terni e quaterne</div><div class="content">Trova pagine sestine</div></div>
</center>

<script src="js/jquery-2.1.4.min.js"></script>
<script>
$(document).ready(function() {
    $('.item').click(function(){
        window.location.href = $(this).attr('url');
    });
});
</script>

</body>
</html>
