<?php

require "vendor/autoload.php";

use eftec\bladeone\bladeone;

$Views = __DIR__ . '\Views';
$Cache = __DIR__ . '\Cache';

$Blade = new BladeOne($Views, $Cache);

session_start();

$clientID = '197838027073-bhbjbvqsl9iu59029bf3pinp269fpr9g.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'http://localhost:8000';

// Si ya existe $_SESSION['name'] significa que ya has iniciado sesión por lo que entra aqui
if (isset($_SESSION['name'])) {
    // Si pulsamos en el botón de cerrar sesión entra aqui
    if (isset($_GET['logout'])) {
        // Se borra la sesión para no conservar ningún dato
        session_unset();
        setcookie(session_name(), '', 0, '/');
        $client = new Google_Client();
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("profile");

        $url = $client->createAuthUrl();
        // Se vuelve a enviar a index.blade.php la url de redireccion a la pagina de autorización de Google
        echo $Blade->run('index', compact('url'));
    } else {
        $name = $_SESSION['name'];
        echo $Blade->run('catastro', compact('name'));
    }
} else if (empty($_POST)) {
    // Una vez iniciada sesión, entra aqui
    if (isset($_GET['code'])) {
        $client = new Google_Client();
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $code = filter_input(INPUT_GET, 'code');
        $token = $client->fetchAccessTokenWithAuthCode($code);

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        // Recoge los datos asociados a la cuente Google
        $name = $google_account_info->givenName;
        $_SESSION['name'] = $name;
        $revoked = $client->revokeToken($token);

        // Mandamos esos datos a catastro.blade.php
        echo $Blade->run('catastro', compact('name'));
    // Aqui entra la primera vez que iniciamos el programa
    } else {
        $client = new Google_Client();
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("profile");

        $url = $client->createAuthUrl();

        // Envia al index.blade.php la url que redirige a la pantalla de autorización
        echo $Blade->run('index', compact('url'));
    }
}    