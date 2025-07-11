<?php
header('Content-Type: application/json');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$url = "https://www.lottologia.com/lotto/?do=past-draws-archive&table_view_type=default&year=$year&numbers=";

// Inizializza cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode !== 200) {
    echo json_encode([
        'error' => 'Errore durante il parsing',
        'details' => $error ?: "Codice HTTP: $httpCode"
    ]);
    exit;
}

// Parsing HTML
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($response);
$xpath = new DOMXPath($dom);
$tables = $xpath->query('//table');

if ($tables->length === 0) {
    echo json_encode([
        'error' => 'Tabella non trovata nella pagina',
        'year' => $year
    ]);
    exit;
}

$tableHTML = $dom->saveHTML($tables[0]);

echo json_encode([
    'message' => 'OK',
    'year' => $year,
    'table' => $tableHTML
]);
