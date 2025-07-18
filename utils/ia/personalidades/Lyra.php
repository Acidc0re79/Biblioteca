<?php
/**
 * Librería de "Personalidades" de la IA.
 * Centraliza la construcción de los prompts de sistema para asegurar
 * consistencia y facilitar el mantenimiento.
 */
class Lyra {

    /**
     * Devuelve el prompt de sistema para actuar como filtro de seguridad.
     * Lee las reglas y el prompt base desde la configuración de la BD.
     *
     * @return string El prompt de sistema completo para Lyra Guardiana.
     */
    public static function getGuardianaPrompt() {
        // CONFIG_SITIO es la constante global cargada en init.php
        $promptBase = CONFIG_SITIO['ia_prompt_lyra_guardiana'] ?? '';
        $reglasCore = file_get_contents(ROOT_PATH . '/config/ia_negative_prompts_core.txt');
        $reglasMod = CONFIG_SITIO['ia_negative_prompts_mod'] ?? ''; // Leído desde la BD

        // Reemplazamos los placeholders en el prompt base con las reglas
        $promptFinal = str_replace(['{{reglas_core}}', '{{reglas_mod}}'], [$reglasCore, $reglasMod], $promptBase);

        return $promptFinal;
    }

    /**
     * Devuelve el prompt de sistema para mejorar la creatividad.
     *
     * @param string $promptOriginal El prompt ya validado por la Guardiana.
     * @return string El prompt de sistema completo para Lyra Creativa.
     */
    public static function getCreativaPrompt($promptOriginal) {
        $promptBase = CONFIG_SITIO['ia_prompt_lyra_creativa'] ?? '';
        
        // Reemplazamos el placeholder con el prompt del usuario
        $promptFinal = str_replace('{{prompt_usuario}}', $promptOriginal, $promptBase);

        return $promptFinal;
    }
}