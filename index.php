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
    <link rel="stylesheet" href="css/foundation.min.css" />
    <link rel="stylesheet" href="css/app.css" />
  </head>
  <body>
    <div class="row">
      <h1> Welcome to League Skype </h1>
      <p> When playing League of Legends with your friends, stop dealing with the pains of Skype. Use League Skype! </p>
        
      <!-- Form for joining a room -->
      <div class="panel">
        <form action="index.php" method="POST">
          <label for="room"> Name your Call </label>
          <input name="room" id="room" type="text" value="" />
          <input class="button" type="submit" name="submit" />
        </form>
      </div>
    </div>
    <footer class="row"> Created by Billy Lynch and Vaibhav Verma </footer>
  </body>
</html>
