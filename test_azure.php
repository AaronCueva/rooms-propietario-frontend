<?php
/**
 * Script de diagnóstico para Azure Blob Storage.
 * Abrir en el navegador: http://localhost/rooms-propietario-frontend/test_azure.php
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico Azure Blob Storage</title>
    <style>
        body { font-family: 'Consolas', monospace; background: #1e1e2e; color: #cdd6f4; padding: 2rem; }
        .ok { color: #a6e3a1; font-weight: bold; }
        .fail { color: #f38ba8; font-weight: bold; }
        .warn { color: #fab387; }
        h1 { color: #89b4fa; }
        h2 { color: #b4befe; margin-top: 2rem; }
        pre { background: #313244; padding: 1rem; border-radius: 8px; overflow-x: auto; white-space: pre-wrap; word-break: break-all; }
        .section { margin: 1.5rem 0; padding: 1rem; background: #313244; border-radius: 8px; }
    </style>
</head>
<body>
<h1>🔍 Diagnóstico Azure Blob Storage</h1>
<?php

// ── 1) .env ──
$envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';
echo '<div class="section">';
echo '<h2>1. Archivo .env</h2>';
if (file_exists($envFile)) {
    echo '<p class="ok">✓ ENCONTRADO: ' . htmlspecialchars($envFile) . '</p>';
} else {
    echo '<p class="fail">✗ NO ENCONTRADO: ' . htmlspecialchars($envFile) . '</p>';
    echo '<p class="fail">Abortando.</p></div></body></html>';
    exit;
}
echo '</div>';

// ── 2) Parsear variables ──
$env = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        if ($name !== '') {
            $env[$name] = trim($value, "\"'");
        }
    }
}

$baseUrl = $env['AZURE_BLOB_BASE_URL'] ?? '';
$sasToken = $env['AZURE_BLOB_SAS_TOKEN'] ?? '';

echo '<div class="section">';
echo '<h2>2. Variables del .env</h2>';
echo '<p>AZURE_BLOB_BASE_URL = <code>' . htmlspecialchars($baseUrl ?: '(VACÍO)') . '</code></p>';
echo '<p>AZURE_BLOB_SAS_TOKEN = <code>' . htmlspecialchars($sasToken ? substr($sasToken, 0, 40) . '...' : '(VACÍO)') . '</code></p>';

if (empty($baseUrl) || empty($sasToken)) {
    echo '<p class="fail">✗ Una o ambas variables están vacías. Verifica tu .env</p>';
    echo '</div></body></html>';
    exit;
}

// Verificar que el SAS token empiece con ?
if (strpos($sasToken, '?') !== 0) {
    echo '<p class="fail">✗ PROBLEMA: El SAS token NO empieza con "?". Debería ser algo como "?sp=racwdli&st=..."</p>';
} else {
    echo '<p class="ok">✓ SAS token empieza con "?" correctamente.</p>';
}
echo '</div>';

// ── 3) Extensiones PHP ──
echo '<div class="section">';
echo '<h2>3. Extensiones PHP</h2>';

$curlOk = extension_loaded('curl');
$opensslOk = extension_loaded('openssl');
$fopenOk = ini_get('allow_url_fopen');

echo '<p>' . ($curlOk ? '<span class="ok">✓</span>' : '<span class="fail">✗</span>') . ' cURL: ' . ($curlOk ? 'DISPONIBLE' : '<strong>NO DISPONIBLE — Este es probablemente tu problema!</strong>') . '</p>';
echo '<p>' . ($opensslOk ? '<span class="ok">✓</span>' : '<span class="fail">✗</span>') . ' OpenSSL: ' . ($opensslOk ? 'DISPONIBLE' : 'NO DISPONIBLE') . '</p>';
echo '<p>' . ($fopenOk ? '<span class="ok">✓</span>' : '<span class="fail">✗</span>') . ' allow_url_fopen: ' . ($fopenOk ? 'ACTIVADO' : 'DESACTIVADO') . '</p>';
echo '<p>PHP Version: ' . phpversion() . '</p>';
echo '</div>';

// ── 4) Test de subida ──
echo '<div class="section">';
echo '<h2>4. Prueba de subida</h2>';

// Crear un PNG mínimo de 1x1 pixel
$testContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
$timestamp = time();
$uploadUrl = $baseUrl . '/test/diagnostico_' . $timestamp . '.png' . $sasToken;

echo '<p>URL de subida: <code>' . htmlspecialchars(substr($uploadUrl, 0, 90)) . '...</code></p>';
echo '<p>Tamaño del contenido: ' . strlen($testContent) . ' bytes</p>';

// ── Método A: cURL ──
if ($curlOk) {
    echo '<h3 style="color:#89dceb;">Método A: cURL (Recomendado)</h3>';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $uploadUrl,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => $testContent,
        CURLOPT_HTTPHEADER     => [
            'x-ms-blob-type: BlockBlob',
            'Content-Type: image/png',
            'Content-Length: ' . strlen($testContent),
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HEADER         => true,
        CURLOPT_VERBOSE        => true,
    ]);

    // Capturar verbose output
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $response = curl_exec($ch);
    $curlStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);

    // Leer verbose
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    fclose($verbose);

    echo '<p>HTTP Status Code: <strong>' . $curlStatusCode . '</strong></p>';

    if ($curlStatusCode === 201) {
        echo '<p class="ok">✓ ¡ÉXITO! La subida a Azure Blob Storage funciona correctamente con cURL.</p>';
        $publicUrl = $baseUrl . '/test/diagnostico_' . $timestamp . '.png';
        echo '<p>URL pública del archivo: <a href="' . htmlspecialchars($publicUrl) . '" target="_blank" style="color:#89b4fa;">' . htmlspecialchars($publicUrl) . '</a></p>';
    } else {
        echo '<p class="fail">✗ FALLÓ con cURL.</p>';
        if ($curlError) {
            echo '<p class="fail">Error cURL (#' . $curlErrno . '): ' . htmlspecialchars($curlError) . '</p>';
        }
        echo '<h4>Respuesta del servidor:</h4>';
        echo '<pre>' . htmlspecialchars($response) . '</pre>';
        echo '<h4>Verbose log:</h4>';
        echo '<pre>' . htmlspecialchars($verboseLog) . '</pre>';
    }
} else {
    echo '<p class="fail">⚠ cURL NO disponible — no se puede probar este método.</p>';
}

// ── Método B: file_get_contents ──
echo '<h3 style="color:#89dceb;">Método B: file_get_contents (Fallback)</h3>';

$uploadUrl2 = $baseUrl . '/test/diagnostico_fgc_' . $timestamp . '.png' . $sasToken;

$options = [
    'http' => [
        'method'        => 'PUT',
        'header'        => [
            "x-ms-blob-type: BlockBlob",
            "Content-Type: image/png",
            "Content-Length: " . strlen($testContent)
        ],
        'content'       => $testContent,
        'ignore_errors' => true,
        'timeout'       => 30,
    ],
    'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ]
];

$context = stream_context_create($options);
$result = @file_get_contents($uploadUrl2, false, $context);

$statusCode = 0;
$responseHeaders = '';
if (isset($http_response_header) && is_array($http_response_header)) {
    $responseHeaders = implode("\n", $http_response_header);
    if (preg_match('#^HTTP/\d(?:\.\d)?\s+(\d{3})#', $http_response_header[0], $matches)) {
        $statusCode = intval($matches[1]);
    }
}

echo '<p>HTTP Status Code: <strong>' . $statusCode . '</strong></p>';

if ($statusCode === 201) {
    echo '<p class="ok">✓ ¡ÉXITO con file_get_contents!</p>';
} else {
    echo '<p class="fail">✗ FALLÓ con file_get_contents.</p>';
    $lastError = error_get_last();
    if ($lastError) {
        echo '<p class="fail">Error PHP: ' . htmlspecialchars($lastError['message']) . '</p>';
    }
    if ($responseHeaders) {
        echo '<h4>Headers de respuesta:</h4>';
        echo '<pre>' . htmlspecialchars($responseHeaders) . '</pre>';
    }
    if ($result !== false) {
        echo '<h4>Body de respuesta (XML de Azure):</h4>';
        echo '<pre>' . htmlspecialchars(substr($result, 0, 1000)) . '</pre>';
    }
}

echo '</div>';

// ── 5) Resumen ──
echo '<div class="section">';
echo '<h2>5. Resumen y Diagnóstico</h2>';

if (($curlOk && $curlStatusCode === 201) || $statusCode === 201) {
    echo '<p class="ok">✓ Al menos un método funciona. El código actualizado de AzureStorage.php debería subir las imágenes correctamente.</p>';
    echo '<p>Si aún no funciona en la app, verifica que el archivo <code>app/core/AzureStorage.php</code> se haya guardado correctamente.</p>';
} else {
    echo '<p class="fail">✗ Ningún método funcionó. Posibles causas:</p>';
    echo '<ul>';
    echo '<li>El SAS token ha expirado o tiene permisos insuficientes (necesita al menos: <code>racw</code> - read, add, create, write)</li>';
    echo '<li>El contenedor "desarrollo-software" no existe en la cuenta de storage</li>';
    echo '<li>La cuenta de storage "veladerostorage" no existe o no es accesible</li>';
    echo '<li>Firewall de red o proxy bloqueando la conexión HTTPS a Azure</li>';
    echo '<li>PHP no tiene acceso a internet desde este servidor</li>';
    echo '</ul>';
}
echo '</div>';

?>
</body>
</html>
