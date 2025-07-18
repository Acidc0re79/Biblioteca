<?php
/**
 * MotorGeneradorIA - El Orquestador Central
 * Orquesta las llamadas a las APIs para completar tareas complejas de IA.
 */

// Incluimos todos los componentes de nuestro motor.
require_once ROOT_PATH . '/utils/ia/ia_debug_helper.php';
require_once ROOT_PATH . '/utils/ia/personalidades/Lyra.php';
require_once ROOT_PATH . '/utils/ia/apis/Api-Gemini.php';
require_once ROOT_PATH . '/utils/ia/apis/Api-HuggingFace.php';

class MotorGeneradorIA {

    private $procesadorTexto;
    private $generadorImagen;

    public function __construct() {
        // En el futuro, aquí podríamos tener un switch para instanciar
        // diferentes APIs según la elección del usuario.
        $this->procesadorTexto = new GeminiAPI();
        $this->generadorImagen = new HuggingFaceAPI();
    }

    /**
     * Receta completa para generar, procesar y guardar un avatar de usuario.
     *
     * @param array $datosFormulario Datos del formulario de creación.
     * @param int $idUsuario El ID del usuario que genera el avatar.
     * @return array Resultado final ['success' => bool, 'data' => 'url_nueva_imagen' o 'mensaje_error']
     */
    public function generarAvatarUsuario(array $datosFormulario, $idUsuario) {
        log_ia_event('Inicio de la receta: generarAvatarUsuario.', ['id_usuario' => $idUsuario]);

        // --- Fase 0: Comprobación de Circuit Breaker ---
        if ((CONFIG_SITIO['ia_estado_gemini'] ?? 'offline') !== 'online' || (CONFIG_SITIO['ia_estado_huggingface'] ?? 'offline') !== 'online') {
            log_ia_event('Proceso abortado por Circuit Breaker.', [
                'estado_gemini' => CONFIG_SITIO['ia_estado_gemini'],
                'estado_huggingface' => CONFIG_SITIO['ia_estado_huggingface']
            ]);
            return ['success' => false, 'data' => 'Uno de los servicios de IA está en mantenimiento. Inténtalo más tarde.'];
        }

        // --- Fase 1: Construcción y Validación del Prompt ---
        $promptUsuarioOriginal = $this->construirPromptDesdeFormulario($datosFormulario);
        $promptSistemaGuardiana = Lyra::getGuardianaPrompt();
        $resultadoValidacion = $this->procesadorTexto->procesarPrompt($promptSistemaGuardiana, $promptUsuarioOriginal);

        if (!$resultadoValidacion['success'] || trim($resultadoValidacion['data']) === 'UNSAFE') {
            log_ia_event('Prompt rechazado por Lyra Guardiana.', ['prompt' => $promptUsuarioOriginal]);
            return ['success' => false, 'data' => 'Tu solicitud no cumple con nuestras políticas de contenido y seguridad.'];
        }
        
        $promptFinal = $promptUsuarioOriginal; // Por defecto, usamos el prompt original si no se mejora.

        // --- Fase 2: Mejora Opcional del Prompt ---
        if (isset($datosFormulario['mejorar_con_ia']) && $datosFormulario['mejorar_con_ia']) {
            $promptSistemaCreativa = Lyra::getCreativaPrompt($promptUsuarioOriginal);
            $resultadoMejora = $this->procesadorTexto->procesarPrompt($promptSistemaCreativa, ""); // No se pasa prompt de usuario aquí
            
            if ($resultadoMejora['success']) {
                $promptFinal = $resultadoMejora['data'];
                log_ia_event('Prompt mejorado por Lyra Creativa.', ['prompt_original' => $promptUsuarioOriginal, 'prompt_mejorado' => $promptFinal]);
            }
        }
        
        // --- Fase 3: Generación de la Imagen ---
        $resultadoImagen = $this->generadorImagen->generarImagen($promptFinal);

        if (!$resultadoImagen['success']) {
            return ['success' => false, 'data' => $resultadoImagen['data']];
        }

        // --- Fase 4: Post-procesamiento (Guardado y Thumbnail) ---
        $nombreArchivo = "user_{$idUsuario}_" . time() . ".png";
        $resultadoGuardado = guardarYCrearThumbnail($resultadoImagen['data'], $nombreArchivo);

        if (!$resultadoGuardado['success']) {
            return ['success' => false, 'data' => $resultadoGuardado['data']];
        }
        
        // Aquí iría la lógica para registrar el nuevo avatar en la BD (lo haremos al conectar con el frontend).

        log_ia_event('Receta completada con éxito. Avatar generado y guardado.', ['archivo' => $nombreArchivo]);
        return ['success' => true, 'data' => $resultadoGuardado['url_completa']];
    }

    /**
     * Método privado para ensamblar el prompt a partir de las opciones del formulario.
     */
    private function construirPromptDesdeFormulario(array $datos): string {
        // Esta lógica concatena el prompt base con las keywords de los selectores.
        $partes = [$datos['prompt_ia'] ?? ''];
        $campos_opcionales = [
            'tipo_sujeto_ia', 'accion_ia', 'entorno_ia', 'iluminacion_ia', 
            'paleta_color_ia', 'estilo_ia', 'composicion_angulo_ia', 
            'rendering_details_ia', 'emotional_tone_ia'
        ];
        foreach ($campos_opcionales as $campo) {
            if (!empty($datos[$campo])) {
                $partes[] = $datos[$campo];
            }
        }
        return implode(', ', array_filter($partes));
    }
}