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
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once '../Classes/DBM.php';

$myYear = date("Y");
$dbm = new DBM();

$dbm->write("DELETE FROM year$myYear");
$dbm->write("DELETE FROM quad$myYear");

$dataJson = getFileMrk($myYear);
if (!$dataJson || !isset($dataJson['estrazioni']) || !isset($dataJson['ruote'])) {
    die("Errore: dati non validi dal microservizio");
}

$ruote = $dataJson['ruote'];
$estrazioni = $dataJson['estrazioni'];

foreach ($estrazioni as $idx => $estrazione) {
    $tmpEst = $idx + 1;
    $data = trim($estrazione['data']) . " " . $myYear;
    $valori = $estrazione['valori'];

    $dbm->write("INSERT INTO year$myYear VALUES($tmpEst, '$data', '" . json_encode($valori) . "')");

    foreach ($valori as $ruota => $val) {
        $estrat = explode('.', $val);
        for ($i = 0; $i < 5; $i++) {
            for ($j = $i + 1; $j < 5; $j++) {
                if (getDistance($estrat[$i], $estrat[$j]) === 'x') {
                    $tripla = getTripla($estrat[$i]);
                    $val1 = (int)$estrat[$i];
                    $val2 = (int)$estrat[$j];
                    $distanza = ($i + 1) . '-' . ($j + 1);
                    $dbm->write("INSERT INTO quad$myYear VALUES('$data','$ruota',$tmpEst,'$distanza','$tripla',$val1,$val2)");
                }
            }
        }
    }
}

function getFileMrk($year) {
    $url = 'https://lotto2025.vercel.app/api/estrazioni?year=' . $year;
    $response = file_get_contents($url);
    if (!$response) return null;
    return json_decode($response, true);
}

function getDistance($x, $y) {
    $tmp = abs($x - $y);
    $aux = ($tmp > 45) ? 90 - $tmp : $tmp;
    return ($aux == 3) ? 'x' : $aux;
}

function getTripla($val) {
    $val = (string)$val;
    $sum = (int)$val[0] + (int)$val[1];
    $value = ($sum > 9) ? $sum - 9 : $sum;

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
    <div class="item" url="TotaliSestine"><div class="title">Sestine</div><div class="content">Elenco sestine</div></div>
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
