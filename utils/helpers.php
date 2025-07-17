<?php
// /utils/helpers.php (Versión Confirmada)

/**
 * Obtiene la URL completa y segura de la imagen de avatar de un usuario.
 *
 * @param string|null $avatar_filename El nombre del archivo del avatar desde la BD.
 * @param string $email El email del usuario, necesario para el fallback a Gravatar.
 * @return string La URL completa y segura de la imagen del avatar.
 */
function get_avatar_url($avatar_filename, $email)
{
    // 1. Si el usuario tiene un avatar personalizado subido/generado.
    if (!empty($avatar_filename)) {
        // AVATARS_URL es una constante definida en init.php.
        return AVATARS_URL . '/' . htmlspecialchars($avatar_filename);
    }

    // 2. Si no, usamos Gravatar como respaldo.
    $email_limpio = strtolower(trim($email));
    $gravatar_hash = md5($email_limpio);
    // d=mp significa "mystery person". s=150 pide una imagen de 150px.
    return "https://www.gravatar.com/avatar/" . $gravatar_hash . "?d=mp&s=150";
}

// Aquí se pueden añadir más funciones globales en el futuro.