<?php
// /utils/acciones/auth/google_callback.php (Versión con verificación de preferencia)

require_once ROOT_PATH . '/config/init.php';
global $pdo;
$google_config = require ROOT_PATH . '/config/google_config.php';

if (empty($_GET['code'])) {
    header('Location: /index.php?pagina=login_form&error=google_code');
    exit;
}

function establecerSesionCompleta($usuario_info) {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    session_unset();
    $_SESSION['usuario_id']    = $usuario_info['id_usuario'];
    $_SESSION['nombre']        = $usuario_info['nombre'];
    $_SESSION['apellido']      = $usuario_info['apellido'];
    $_SESSION['email']         = $usuario_info['email'];
    $_SESSION['estado_cuenta'] = $usuario_info['estado_cuenta'];
    $_SESSION['rango']         = $usuario_info['rango'];
    $_SESSION['tema']          = $usuario_info['tema'];
    session_write_close();
}

try {
    // ... (El código para obtener el token y el perfil de Google no cambia)
    $token_endpoint = 'https://oauth2.googleapis.com/token';
    $token_params = [ 'code' => $_GET['code'], 'client_id' => $google_config['client_id'], 'client_secret' => $google_config['client_secret'], 'redirect_uri' => $google_config['redirect_uri'], 'grant_type' => 'authorization_code' ];
    $curl = curl_init($token_endpoint);
    curl_setopt_array($curl, [ CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($token_params), CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => 30 ]);
    $token_response = curl_exec($curl);
    if (curl_errno($curl)) { throw new Exception('Error de cURL: ' . curl_error($curl)); }
    curl_close($curl);
    $token_data = json_decode($token_response, true);
    if (empty($token_data['access_token'])) { throw new Exception('Google no devolvió un token de acceso.'); }
    $access_token = $token_data['access_token'];

    $userinfo_endpoint = 'https://www.googleapis.com/oauth2/v3/userinfo';
    $curl_user = curl_init($userinfo_endpoint);
    curl_setopt_array($curl_user, [ CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $access_token] ]);
    $userinfo_response = curl_exec($curl_user);
    curl_close($curl_user);
    $perfil = json_decode($userinfo_response, true);
    if (empty($perfil['email'])) { throw new Exception('Google no devolvió un perfil válido.'); }

    // --- Lógica de usuario (sin cambios) ---
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $perfil['email']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$usuario) {
        $stmt_insert = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, proveedor_oauth, oauth_id, avatar_google, estado_cuenta, rango) VALUES (:nombre, :apellido, :email, 'google', :oauth_id, :avatar_google, 'activo', 'lector')");
        $stmt_insert->execute(['nombre' => $perfil['given_name'], 'apellido' => $perfil['family_name'] ?? '', 'email' => $perfil['email'], 'oauth_id' => $perfil['sub'], 'avatar_google' => $perfil['picture']]);
        $id_nuevo_usuario = $pdo->lastInsertId();
        $stmt_new = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
        $stmt_new->execute([$id_nuevo_usuario]);
        $usuario = $stmt_new->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt_update = $pdo->prepare("UPDATE usuarios SET avatar_google = ? WHERE id_usuario = ?");
        $stmt_update->execute([$perfil['picture'], $usuario['id_usuario']]);
    }

    // --- LÓGICA DE UNIFICACIÓN FINAL ---
    establecerSesionCompleta($usuario);
    
    if (empty($usuario['hash_password']) && $usuario['ignorar_unificacion_pwd'] == 0) {
        session_start();
        $_SESSION['password_creation_required'] = true;
        session_write_close();
        header("Location: /index.php?pagina=perfil");
        exit;
    } else {
        header("Location: /index.php");
        exit;
    }

} catch (Exception $e) {
    die('Error fatal durante la autenticación con Google: ' . $e->getMessage());
}
?>