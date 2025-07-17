<?php

// /config/api_keys.php (Versión depurada y segura)

return [
    /**
     * Claves para la API de Google Gemini.
     * El sistema las rotará automáticamente si una falla.
     */
    'gemini' => [
        'Key_1',
        'Key_2',
    ],

    /**
     * Claves para la API de Hugging Face (o Stable Diffusion).
     * El sistema también las rotará.
     */
    'huggingface' => [
        'Key_1',
        'Key_2',
    ],
];