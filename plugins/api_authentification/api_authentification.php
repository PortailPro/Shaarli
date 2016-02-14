<?php

/**
 * Plugin API Authentification.
 */

// don't raise unnecessary warnings
if (is_file(PluginManager::$PLUGINS_PATH . '/api_authentification/config.php')) {
    include PluginManager::$PLUGINS_PATH . '/api_authentification/config.php';
}

if (empty($GLOBALS['plugins']['API_AUTHENTIFICATION_TOKEN'])) {
    $GLOBALS['plugin_errors'][] = 'Wallabag plugin error: '.
        'Please define "$GLOBALS[\'plugins\'][\'API_AUTHENTIFICATION_TOKEN\']" '.
        'in "plugins/api_authentification/config.php" or in your Shaarli config.php file.';
}

if (empty($GLOBALS['plugins']['API_AUTHENTIFICATION_KEY'])) {
    $GLOBALS['plugin_errors'][] = 'Wallabag plugin error: '.
        'Please define "$GLOBALS[\'plugins\'][\'API_AUTHENTIFICATION_KEY\']" '.
        'in "plugins/api_authentification/config.php" or in your Shaarli config.php file.';
}

if (true === isset($_SERVER['HTTP_X_SHAARLI_TOKEN'])) {
    // API Authentification is only available for : add link
    if (isset($_POST['save_edit']) === false || empty($_POST['save_edit']) === true) {
        header('HTTP/1.0 400 Bad Request');
        die('Token authentification is only available for create link');
    }

    $auth_token = hash_hmac(
        'sha256',
        $GLOBALS['plugins']['API_AUTHENTIFICATION_KEY'],
        $GLOBALS['plugins']['API_AUTHENTIFICATION_TOKEN']
    );

    if ($_SERVER['HTTP_X_SHAARLI_TOKEN'] !== $auth_token) {
        header('HTTP/1.0 403 Forbidden');
        die('Token verification failed');
    }

    // set authenticate to true
    $GLOBALS['config']['OPEN_SHAARLI'] = true;

    // set a valid token
    $token = sha1(uniqid('api_', true));
    $_POST['token'] = $token;
    $_SESSION['tokens'][$token] = 1;
}
