<?php
/**
 * Clase trabajadora para interactuar con la API de Hugging Face.
 * Responsable de la generación de imágenes y del manejo de sus errores específicos.
 */
class HuggingFaceAPI {
    private $config;
    private $apiKey;

    /**
     * El constructor carga la configuración de la BD y la clave de API del .env.
     */
    public function __construct() {
        $this->config = [
            'modelo'  => CONFIG_SITIO['ia_modelo_huggingface'] ?? 'SG161222/Realistic_Vision_V5.1_noVAE',
            'timeout' => (int)(CONFIG_SITIO['ia_timeout_huggingface'] ?? 120)
        ];
        $this->apiKey = HUGGINGFACE_API_KEY;
    }

    /**
     * Método principal para generar una imagen a partir de un prompt.
     * Incluye una lógica de reintento para el error de "modelo cargando".
     *
     * @param string $prompt El prompt final, ya procesado y validado.
     * @return array ['success' => bool, 'data' => 'datos_binarios_de_la_imagen' o 'mensaje_de_error']
     */
    public function generarImagen($prompt) {
        log_ia_event('Iniciando llamada a Hugging Face.', [
            'modelo' => $this->config['modelo'],
            'prompt' => substr($prompt, 0, 100) . '...'
        ]);

        $endpoint = "https://api-inference.huggingface.co/models/" . $this->config['modelo'];
        
        // Añadimos los prompts negativos desde la configuración
        $negative_prompt_core = file_get_contents(ROOT_PATH . '/config/ia_negative_prompts_core.txt');
        $negative_prompt_mod = CONFIG_SITIO['ia_negative_prompts_mod'] ?? '';
        $full_negative_prompt = $negative_prompt_core . ", " . $negative_prompt_mod;

        $request_body = json_encode([
            'inputs' => $prompt,
            'parameters' => [
                'negative_prompt' => $full_negative_prompt
            ]
        ]);

        // Realizamos el primer intento
        $response = $this->hacerPeticion($endpoint, $request_body);

        // Si el modelo se está cargando, lo reintentamos una vez más.
        if ($response['http_code'] === 503) {
            log_ia_event('Hugging Face está cargando el modelo. Esperando para reintentar...', [
                'tiempo_espera' => 20 // segundos
            ]);
            sleep(20); // Esperamos 20 segundos antes de reintentar
            $response = $this->hacerPeticion($endpoint, $request_body);
        }

        // Analizamos la respuesta final
        if ($response['http_code'] === 200 && empty($response['curl_error'])) {
            log_ia_event('Llamada a Hugging Face exitosa. Imagen recibida.');
            return ['success' => true, 'data' => $response['body']];
        } else {
            $error_message = "Error en la API de Hugging Face (HTTP {$response['http_code']}).";
            log_ia_event('Error en llamada a Hugging Face.', [
                'http_code' => $response['http_code'],
                'curl_error' => $response['curl_error'],
                'response_body' => $response['body']
            ]);
            return ['success' => false, 'data' => $error_message];
        }
    }

    /**
     * Método privado que encapsula la lógica de la llamada cURL.
     *
     * @param string $endpoint La URL de la API.
     * @param string $request_body El cuerpo de la solicitud en formato JSON.
     * @return array Un array con el cuerpo de la respuesta, el código HTTP y el error de cURL.
     */
    private function hacerPeticion($endpoint, $request_body) {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout']);

        $response_body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        return [
            'body' => $response_body,
            'http_code' => $http_code,
            'curl_error' => $curl_error
        ];
    }
}