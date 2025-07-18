<?php
/**
 * Clase trabajadora para interactuar con la API de Google Gemini.
 * Contiene la lógica para la comunicación, rotación de claves y manejo de errores.
 */
class GeminiAPI {
    private $config;
    private $apiKeys;
    private $currentKeyIndex = 0;

    /**
     * El constructor carga la configuración de la BD (via CONFIG_SITIO)
     * y las claves de API del .env (via GEMINI_API_KEYS).
     */
    public function __construct() {
        $this->config = [
            'modelo'  => CONFIG_SITIO['ia_modelo_gemini'] ?? 'gemini-1.5-flash',
            'timeout' => (int)(CONFIG_SITIO['ia_timeout_gemini'] ?? 30)
        ];
        $this->apiKeys = GEMINI_API_KEYS;
    }

    /**
     * Método principal para procesar un prompt.
     * Intenta la solicitud con las claves de API disponibles hasta que una funcione.
     *
     * @param string $promptDeSistema El prompt que define la "personalidad" (ej. Lyra Guardiana).
     * @param string $promptUsuario El prompt proporcionado por el usuario.
     * @return array ['success' => bool, 'data' => 'respuesta_de_la_api' o 'mensaje_de_error']
     */
    public function procesarPrompt($promptDeSistema, $promptUsuario) {
        $last_error_message = 'No API keys available.';

        // Bucle para intentar con cada clave de API disponible.
        foreach ($this->apiKeys as $index => $apiKey) {
            log_ia_event('Iniciando llamada a Gemini.', [
                'modelo' => $this->config['modelo'],
                'key_index' => $index,
                'prompt_usuario' => substr($promptUsuario, 0, 100) . '...'
            ]);

            // Construimos la petición cURL
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->config['modelo']}:generateContent?key=" . $apiKey;
            
            // Unimos el prompt de sistema con el del usuario
            $full_prompt = $promptDeSistema . " " . $promptUsuario;

            $request_body = json_encode([
                'contents' => [
                    ['parts' => [['text' => $full_prompt]]]
                ]
            ]);

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout']);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            // Analizamos la respuesta
            if ($http_code === 200 && !$curl_error) {
                $gemini_response = json_decode($response, true);
                $text_response = $gemini_response['candidates'][0]['content']['parts'][0]['text'] ?? '';

                if (!empty($text_response)) {
                    log_ia_event('Llamada a Gemini exitosa.', ['key_index' => $index]);
                    return ['success' => true, 'data' => trim($text_response)];
                } else {
                    $last_error_message = "Respuesta vacía de Gemini con clave índice {$index}.";
                    log_ia_event('Error en llamada a Gemini: Respuesta vacía.', ['key_index' => $index, 'response' => $response]);
                    continue; // Intentar con la siguiente clave
                }
            } elseif ($http_code === 429) { // Error de cuota
                $last_error_message = "Cuota excedida para la clave índice {$index}.";
                log_ia_event('Error en llamada a Gemini: Cuota excedida.', ['key_index' => $index]);
                continue; // Intentar con la siguiente clave
            } else { // Otro tipo de error
                $last_error_message = "Error de API (HTTP {$http_code}) con clave índice {$index}.";
                log_ia_event('Error en llamada a Gemini: Error de API.', ['key_index' => $index, 'http_code' => $http_code, 'curl_error' => $curl_error, 'response' => $response]);
                continue; // Intentar con la siguiente clave
            }
        }

        // Si el bucle termina y no hubo éxito
        return ['success' => false, 'data' => "Gemini no pudo procesar la solicitud. Último error: " . $last_error_message];
    }
}