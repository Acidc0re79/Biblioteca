<?php
// /public/users/generar_avatar_ia.php

require_once dirname(__DIR__, 2) . '/config/init.php';
require_once ROOT_PATH . '/utils/debug_helper.php'; // Incluimos la función syslr_debug_log
require_once ROOT_PATH . '/utils/log_api_event.php'; // Incluimos la función log_api_event

header('Content-Type: application/json');

syslr_debug_log("Diario de generar_avatar_ia.php - " . date('Y-m-d H:i:s'));

try {
    // 1. Verificación básica de la solicitud.
    if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Acceso no autorizado.");
    }
    $id_usuario = $_SESSION['usuario_id'];
    syslr_debug_log("PASO 1: Script iniciado. Usuario ID: {$id_usuario}.");

    // Inicializamos $data decodificando el JSON de la entrada.
    global $pdo, $api_keys; // Aseguramos acceso a las variables globales
// La variable $data ya está definida por ajax-handler.php
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar JSON de la entrada: " . json_last_error_msg());
    }
    syslr_debug_log("DEBUG: Datos POST recibidos para Hugging Face: " . print_r($data, true));

    $prompt_para_hugging_face = trim($data['prompt_ia'] ?? ''); 

    if (empty($prompt_para_hugging_face)) {
        throw new Exception("El prompt para la IA de imagen está vacío. No se puede generar la imagen.");
    }
    syslr_debug_log("PASO 2: Prompt recibido para Hugging Face: '{$prompt_para_hugging_face}'");

    // 2. Obtener datos del usuario.
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?"); 
    $stmt->execute([$id_usuario]); 
    if (!$stmt->fetch()) { throw new Exception("ERROR INTERNO: No se encontró el usuario con ID {$id_usuario} en la BD."); }
    syslr_debug_log("PASO 3: Usuario encontrado en la BD.");

    // 3. Obtener las claves de Hugging Face (ahora un array).
    $hugging_face_tokens_array = $api_keys['hugging_face_token'] ?? [];
    if (empty($hugging_face_tokens_array) || !is_array($hugging_face_tokens_array)) {
        throw new Exception("ERROR DE CONFIGURACIÓN: Las claves de Hugging Face no están configuradas correctamente como un array en /config/api_keys.php.");
    }

    // 4. Cargar prompts negativos desde los archivos de configuración.
    $negative_prompts = [];
    $core_negative_path = ROOT_PATH . '/config/ia_negative_prompts_core.txt';
    $mod_negative_path = ROOT_PATH . '/public/assets/text/ia_negative_prompts_mod.txt';

    if (file_exists($core_negative_path)) {
        $negative_prompts = array_merge($negative_prompts, array_map('trim', file($core_negative_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
    }
    if (file_exists($mod_negative_path)) {
        $negative_prompts = array_merge($negative_prompts, array_map('trim', file($mod_negative_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
    }
    $negative_prompts = array_filter($negative_prompts);
    $negative_prompt_string = implode(', ', $negative_prompts);

    syslr_debug_log("PASO 4: Prompts negativos cargados: '{$negative_prompt_string}'");

    // *** INICIO DE LA LÓGICA DE ROTACIÓN Y REINTENTO DE CLAVES DE HUGGING FACE ***
    $response_hugging_face = null;
    $http_code_hugging_face = 0;
    $curl_error_hugging_face = '';
    $last_error_message_hf = '';

    foreach ($hugging_face_tokens_array as $index => $current_hugging_face_token) {
        syslr_debug_log("Intentando con clave API Hugging Face [{$index}]");
        $hugging_face_endpoint = "https://api-inference.huggingface.co/models/stabilityai/stable-diffusion-xl-base-1.0";

        $payload = json_encode([
            "inputs" => $prompt_para_hugging_face,
            "parameters" => [
                "negative_prompt" => $negative_prompt_string
            ]
        ]);
        $headers = [
            "Authorization: Bearer {$current_hugging_face_token}",
            "Content-Type: application/json"
        ];

        syslr_debug_log("PASO 5: Llamando a la API de Hugging Face con clave [{$index}]...");
        $ch = curl_init($hugging_face_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response_hugging_face = curl_exec($ch);
        $http_code_hugging_face = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error_hugging_face = curl_error($ch);
        curl_close($ch);

        $log_details = [
            'user_id' => $id_usuario ?? 'invitado',
            'api_key_index' => $index,
            'input_prompt_fragment' => substr($prompt_para_hugging_face, 0, 100) . '...'
        ];

        if ($http_code_hugging_face === 200 && !$curl_error_hugging_face) {
            $log_details['status'] = 'OK';
            $log_details['response_size'] = strlen($response_hugging_face);
            log_api_event('huggingface_avatar_attempt', $log_details);
            syslr_debug_log("PASO 6: Imagen recibida con éxito de la API de Hugging Face con clave [{$index}]!");
            break; // Éxito, salir del bucle de claves
        } elseif ($http_code_hugging_face === 429 || $http_code_hugging_face === 402) { // Cuota agotada o pago requerido
            $log_details['status'] = 'Error: Cuota agotada';
            $log_details['http_code'] = $http_code_hugging_face;
            $log_details['curl_error'] = $curl_error_hugging_face;
            $log_details['response'] = $response_hugging_face;
            log_api_event('huggingface_avatar_attempt', $log_details);
            $last_error_message_hf = "Clave Hugging Face [{$index}] agotada o pago requerido. Intentando con la siguiente...";
            syslr_debug_log($last_error_message_hf);
            continue; // Intentar con la siguiente clave
        } else { // Otro tipo de error
            $log_details['status'] = 'Error: API inesperado';
            $log_details['http_code'] = $http_code_hugging_face;
            $log_details['curl_error'] = $curl_error_hugging_face;
            $log_details['response'] = $response_hugging_face;
            log_api_event('huggingface_avatar_attempt', $log_details);
            $last_error_message_hf = "Error inesperado con clave Hugging Face [{$index}]: HTTP {$http_code_hugging_face}, cURL Error: {$curl_error_hugging_face}";
            syslr_debug_log($last_error_message_hf);
            continue;
        }
    } // Fin del bucle foreach de Hugging Face

    if ($http_code_hugging_face !== 200 || $curl_error_hugging_face) {
        throw new Exception("Hugging Face no pudo generar la imagen con ninguna de las claves proporcionadas. Último error: " . $last_error_message_hf);
    }
    // *** FIN DE LA LÓGICA DE ROTACIÓN Y REINTENTO DE CLAVES DE HUGGING FACE ***

    // 6. Guardar la imagen temporal. (SIN CONTEO DE INTENTOS AQUÍ)
    $filename = "temp_avatar_" . $id_usuario . '_' . time() . ".png";
    $temp_dir = ROOT_PATH . '/public/assets/img/avatars/temp/';
    $filepath = $temp_dir . $filename;

    if (!is_dir($temp_dir)) {
        if (!mkdir($temp_dir, 0775, true)) {
            throw new Exception("No se pudo crear el directorio temporal: " . $temp_dir);
        }
    }

    $bytes_written = file_put_contents($filepath, $response_hugging_face);
    if ($bytes_written === false || $bytes_written === 0) {
        syslr_debug_log("Error al escribir el archivo temporal o se escribió con 0 bytes. Verifique permisos o espacio en disco en: " . $filepath, true);
        throw new Exception("Error al escribir el archivo temporal o se escribió con 0 bytes. Verifique permisos o espacio en disco en: " . $filepath);
    }
    if (!file_exists($filepath) || !is_readable($filepath)) {
        syslr_debug_log("Después de escribir, el archivo temporal NO existe o NO es legible: " . $filepath, true);
        throw new Exception("Después de escribir, el archivo temporal NO existe o NO es legible: " . $filepath);
    }
    syslr_debug_log("PASO 7: Imagen temporal guardada en: " . $filepath . " (" . $bytes_written . " bytes)");

    // Devolver la URL temporal y NO los intentos restantes (ya que se descuentan al guardar).
    echo json_encode([
        'success' => true,
        'image_url' => '/assets/img/avatars/temp/' . $filename,
        'message' => 'Avatar generado temporalmente.'
    ]);
    exit;

} catch (Exception $e) {
    syslr_debug_log("ERROR FATAL en generar_avatar_ia.php: " . $e->getMessage(), true);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el avatar: ' . $e->getMessage()
    ]);
    exit;
}
?>