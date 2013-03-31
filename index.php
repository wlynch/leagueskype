<?php
    require 'db.php';
    if (!empty($_POST['room'])) {
      $room = $_POST['room'];

      //check our file db here.

      $session = db_get($room);

      if($session) {
        
      }
      else if (ctype_alpha($room)) {
        require_once 'API_Config.php';
        require_once 'OpenTokSDK.php';

        $apiObj = new OpenTokSDK(API_Config::API_KEY, API_Config::API_SECRET);
        $sessionObj = $apiObj->create_session();
        $session = $sessionObj->getSessionId();
        db_put($room, $session);
      }
      header("Location: room.php?id=".$room); 
    }
?>
<!DOCTYPE html>
<html>
  <head>
    <title> League Skype </title>
  </head>
  <body>
    <!-- Form for joining a room -->
    <form action="index.php" method="POST">
      <label for="room"> Name your Call </label>
      <input name="room" id="room" type="text" value="" />
      <input type="submit" name="submit" />
    </form>
  </body>
</html>
