<?php
// /public/users/generar_prompt_gemini.php

// MODO DE DEPURACIÓN ACTIVADO (temporalmente para desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluimos el archivo de inicialización.
require_once dirname(__DIR__, 2) . '/config/init.php';
// Incluimos los helpers de depuración y log.
require_once ROOT_PATH . '/utils/debug_helper.php'; 
require_once ROOT_PATH . '/utils/log_api_event.php'; // ¡Asegúrate de que este archivo exista!

// Establecemos la cabecera para que la respuesta sea JSON.
header('Content-Type: application/json');

syslr_debug_log("Diario de generar_prompt_gemini.php - " . date('Y-m-d H:i:s'));

try {
    // 1. Verificación básica de la solicitud.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método de solicitud no permitido. Solo POST.");
    }

    global $pdo, $api_keys; // Aseguramos acceso a las variables globales
// La variable $data ya está definida por ajax-handler.php
    // --- SANITIZACIÓN DEL INPUT DE USUARIO ---
    $prompt_usuario_base = htmlspecialchars(strip_tags(trim($data['prompt_ia'] ?? ''))); 
    // --- FIN SANITIZACIÓN ---

    $estilo_ia = htmlspecialchars(strip_tags(trim($data['estilo_ia'] ?? '')));
    $tipo_sujeto_ia = htmlspecialchars(strip_tags(trim($data['tipo_sujeto_ia'] ?? '')));
    $accion_ia = htmlspecialchars(strip_tags(trim($data['accion_ia'] ?? '')));
    $entorno_ia = htmlspecialchars(strip_tags(trim($data['entorno_ia'] ?? '')));
    $iluminacion_ia = htmlspecialchars(strip_tags(trim($data['iluminacion_ia'] ?? '')));
    $paleta_color_ia = htmlspecialchars(strip_tags(trim($data['paleta_color_ia'] ?? '')));
    $composicion_angulo_ia = htmlspecialchars(strip_tags(trim($data['composicion_angulo_ia'] ?? '')));
    $rendering_details_ia = htmlspecialchars(strip_tags(trim($data['rendering_details_ia'] ?? '')));
    $emotional_tone_ia = htmlspecialchars(strip_tags(trim($data['emotional_tone_ia'] ?? '')));

    // Validar longitud del prompt base
    if (mb_strlen($prompt_usuario_base) > 700) {
        throw new Exception("La descripción base es demasiado larga. Máximo 700 caracteres.");
    }

    // Construir el prompt para Gemini con todos los detalles
    $full_user_prompt = $prompt_usuario_base;
    if (!empty($tipo_sujeto_ia)) $full_user_prompt .= ", {$tipo_sujeto_ia}";
    if (!empty($accion_ia)) $full_user_prompt .= ", {$accion_ia}";
    if (!empty($entorno_ia)) $full_user_prompt .= ", {$entorno_ia}";
    if (!empty($iluminacion_ia)) $full_user_prompt .= ", {$iluminacion_ia}";
    if (!empty($paleta_color_ia)) $full_user_prompt .= ", {$paleta_color_ia}";
    if (!empty($estilo_ia)) $full_user_prompt .= ", {$estilo_ia}";
    if (!empty($composicion_angulo_ia)) $full_user_prompt .= ", {$composicion_angulo_ia}";
    if (!empty($rendering_details_ia)) $full_user_prompt .= ", {$rendering_details_ia}";
    if (!empty($emotional_tone_ia)) $full_user_prompt .= ", {$emotional_tone_ia}";

    if (empty($full_user_prompt)) {
        throw new Exception("Por favor, proporciona una descripción base o selecciona al menos una opción para generar el prompt.");
    }
    syslr_debug_log("Prompt base y opciones recibidas: '{$full_user_prompt}'");

    // 2. Obtener las claves de la API de Google Gemini (ahora un array).
    $google_ai_keys_array = $api_keys['google_ai_key'] ?? [];
    $google_project_id = $api_keys['google_project_id'] ?? '';

    if (empty($google_ai_keys_array) || !is_array($google_ai_keys_array) || empty($google_project_id)) {
        throw new Exception("ERROR DE CONFIGURACIÓN: Las claves de Google AI no están configuradas correctamente como un array en /config/api_keys.php.");
    }

    $prompt_mejorado = '';
    $last_error_message = '';
    $gemini_model = "gemini-1.5-flash"; 

    foreach ($google_ai_keys_array as $index => $current_google_ai_key) {
        $log_details = [
            'user_id' => $_SESSION['usuario_id'] ?? 'invitado',
            'api_key_index' => $index,
            'model' => $gemini_model,
            'input_prompt_fragment' => substr($full_user_prompt, 0, 100) . '...'
        ];

        $gemini_endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$gemini_model}:generateContent?key=" . $current_google_ai_key;

        // --- VERSIÓN CORTA Y PLANA DE INSTRUCCIÓN "LYRA" PARA TEST ---
        $instruction_prompt = "You are an expert AI Named Lyra prompt optimization specialist. Enhance the following user input into a highly detailed and visually stunning prompt for AI image generation. The output must be ONLY the enhanced prompt, without any introductions, and IT MUST BE IN ENGLISH: \"{$full_user_prompt}\"";
        // --- FIN VERSIÓN CORTA ---

        $request_body = json_encode([
            'contents' => [
                ['parts' => [['text' => $instruction_prompt]]]
            ]
        ]);

        $ch = curl_init($gemini_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        $log_details['http_code'] = $http_code;
        $log_details['curl_error'] = $curl_error;
        $log_details['response'] = $response;

        if ($http_code === 200 && !$curl_error) {
            $gemini_response_data = json_decode($response, true);
            $prompt_mejorado = $gemini_response_data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            if (!empty($prompt_mejorado)) {
                $log_details['status'] = 'OK';
                $log_details['output_prompt_fragment'] = substr($prompt_mejorado, 0, 100) . '...';
                log_api_event('gemini_prompt_attempt', $log_details, $instruction_prompt, $prompt_mejorado);
                break; // Clave encontrada y prompt generado, salir del bucle
            } else {
                $log_details['status'] = 'Error: Prompt vacío';
                log_api_event('gemini_prompt_attempt', $log_details, $instruction_prompt, null);
                $last_error_message = "Gemini no devolvió un prompt mejorado válido con clave [{$index}]. Respuesta: {$response}";
                continue; // Intentar con la siguiente clave
            }

        } elseif ($http_code === 429) { // Error de cuota agotada
            $log_details['status'] = 'Error: Cuota agotada';
            log_api_event('gemini_prompt_attempt', $log_details, $instruction_prompt, null);
            $last_error_message = "Clave [{$index}] agotada o sobrecargada. Intentando con la siguiente...";
            continue; // Intentar con la siguiente clave
        } else { // Otro tipo de error
            $log_details['status'] = 'Error: API inesperado';
            log_api_event('gemini_prompt_attempt', $log_details, $instruction_prompt, null);
            $last_error_message = "Error inesperado con clave [{$index}]: HTTP {$http_code}, cURL Error: {$curl_error}, Respuesta: {$response}";
            continue; 
        }
    } // Fin del bucle foreach

    if (empty($prompt_mejorado)) {
        throw new Exception("Gemini no pudo generar un prompt mejorado válido con ninguna de las claves proporcionadas. Último error: " . $last_error_message);
    }

    // 6. Devolver el prompt mejorado al frontend.
    echo json_encode([
        'success' => true,
        'prompt_mejorado' => $prompt_mejorado
    ]);

} catch (Exception $e) {
    syslr_debug_log("ERROR en generar_prompt_gemini.php: " . $e->getMessage(), true);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el prompt mejorado: ' . $e->getMessage()
    ]);
}
?>