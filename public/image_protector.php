<?php
require_once __DIR__ . '/../config/init.php';

// --- 1. Validar la Entrada ---
$requested_image = basename($_GET['img'] ?? '');
$contexto = $_GET['contexto'] ?? 'general'; // Obtenemos el contexto

if (empty($requested_image)) {
    http_response_code(400);
    exit;
}

// --- 2. Definir Rutas y Recursos ---
$image_path = ROOT_PATH . '/public/assets/img/insignias/' . $requested_image;
$font_path = ROOT_PATH . '/public/assets/fonts/Teko-SemiBold.ttf';
$watermark_path = ROOT_PATH . '/public/assets/img/watermarks/lock_overlay.png';
$phrases_path = ROOT_PATH . '/config/insignias_frases.txt';

if (!file_exists($image_path) || !file_exists($watermark_path)) {
    http_response_code(404);
    exit;
}

// --- 3. Procesamiento de la Imagen (Librería GD de PHP) ---
$image = imagecreatefrompng($image_path);
$watermark = imagecreatefrompng($watermark_path);
list($wm_width, $wm_height) = getimagesize($watermark_path);
imagecopy($image, $watermark, 0, 0, 0, 0, $wm_width, $wm_height);

// --- 4. Añadir el Texto Aleatorio (SÓLO si el contexto es 'insignias') ---
if ($contexto === 'insignias' && file_exists($phrases_path) && file_exists($font_path)) {
    $phrases = file($phrases_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!empty($phrases)) {
        $random_phrase = $phrases[array_rand($phrases)];
        
        $font_size = 20;
        $angle = -15;
        $text_color = imagecolorallocate($image, 255, 255, 255);
        $shadow_color = imagecolorallocatealpha($image, 0, 0, 0, 60);

        list($width, $height) = getimagesize($image_path);
        $text_box = imagettfbbox($font_size, $angle, $font_path, $random_phrase);
        $text_width = $text_box[2] - $text_box[0];
        $x = ($width - $text_width) / 2;
        $y = ($height / 2) + ($font_size / 2); // Centrado verticalmente

        imagettftext($image, $font_size, $angle, $x + 2, $y + 2, $shadow_color, $font_path, $random_phrase);
        imagettftext($image, $font_size, $angle, $x, $y, $text_color, $font_path, $random_phrase);
    }
}

// --- 5. Enviar la Imagen Final al Navegador ---
header('Content-Type: image/png');
imagepng($image);

// Liberamos la memoria.
imagedestroy($image);
imagedestroy($watermark);
exit;