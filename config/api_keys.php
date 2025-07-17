<?php
// /config/api_keys.php

return [
    // --- CLAVE PARA EL GENERADOR DE IMÁGENES (Hugging Face) ---
    'hugging_face_token' => [
		'api1',
		'api2',
	],
    // --- Clave de OpenAI (la dejamos por si se usa en el futuro) ---
    'openai_api_key' => '...',

    // --- Claves para la API de Google AI (ahora un array de claves) ---
    'google_ai_key' => [
        'Key1', // Tu clave actual
        'Key2', // Segunda clave
        'Key3', // Tercera clave
        // ... y así sucesivamente con más claves de tus otras cuentas
    ],
    'google_project_id' => 'biblioteca-sofia' // El Project ID suele ser el mismo, o si tienes varios proyectos, deberíamos adaptar esto. Por ahora, asumimos uno.
];
?>