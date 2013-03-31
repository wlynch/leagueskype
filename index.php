<?php
    require_once 'API_Config.php';
    require_once 'OpenTokSDK.php';

    $apiObj = new OpenTokSDK(API_Config::API_KEY, API_Config::API_SECRET);
    $session = $apiObj->create_session();
    echo $session->getSessionId();
    header("Location: client.php?s=".$session->getSessionId());
?>
