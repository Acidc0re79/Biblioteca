<?php
// NO MÁS REQUIRE O INCLUDE AQUÍ.
// Este script asume que un manejador (ej. ajax-handler.php) ya ha:
// 1. Iniciado la sesión y cargado la configuración con init.php.
// 2. Creado la conexión $pdo a la base de datos.
// 3. Cargado las claves de API.
// 4. Verificado que el usuario está logueado.

// --- Verificación de Seguridad Adicional ---
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(403); // Prohibido
    echo json_encode(['success' => false, 'message' => 'Acceso denegado: debes iniciar sesión para generar un avatar.']);
    exit;
}

// Validamos la entrada del usuario.
if (!isset($_POST['prompt']) || empty(trim($_POST['prompt']))) {
    header('Content-Type: application/json');
    http_response_code(400); // Petición incorrecta
    echo json_encode(['success' => false, 'message' => 'El prompt no puede estar vacío.']);
    exit;
}

// Preparamos los datos para la API.
$userId = $_SESSION['user_id'];
$prompt = "epic avatar, " . trim($_POST['prompt']) . ", profile picture, high quality, detailed";
$modelo_ia = "SG161222/Realistic_Vision_V5.1_noVAE";
$apiUrl = "https://api-inference.huggingface.co/models/" . $modelo_ia;

// Realizamos la llamada a la API usando cURL.
$ch = curl_init();
$data = json_encode(['inputs' => $prompt]);

curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . HUGGINGFACE_API_KEY, // Asume que HUGGINGFACE_API_KEY ya está definida.
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// --- Manejo de la Respuesta de la API ---
header('Content-Type: application/json'); // Preparamos la respuesta JSON desde el principio.

if ($curl_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión con el servicio de IA: ' . $curl_error]);
    exit;
}

if ($httpcode !== 200) {
    $error_response = json_decode($result, true);
    $error_message = $error_response['error'] ?? 'Error desconocido de la API.';
    if (isset($error_response['estimated_time'])) {
        $tiempo_estimado = round($error_response['estimated_time']);
        $error_message = "El modelo de IA está ocupado. Por