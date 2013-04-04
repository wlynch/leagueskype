<?php 
  require 'db.php';
  
  if(!empty($_GET['id']) && db_get($_GET['id'])) {
    $room = $_GET['id'];
    $session = db_get($room);
  }
  else {
    exit(" Get a room");
  }

  require_once 'API_Config.php';
  require_once 'OpenTokSDK.php';

  $apiObj = new OpenTokSDK(API_Config::API_KEY, API_Config::API_SECRET);

  $token = $apiObj->generateToken($session);

?>
<!DOCTYPE html>
<html>
<head>
  <title> League Skype | Room - <?php echo $room; ?></title>
	<script src="http://static.opentok.com/webrtc/v2.0/js/TB.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="jquery.js" type="text/javascript"> </script>
  <link rel="stylesheet" href="css/foundation.min.css" />
  <link rel="stylesheet" href="css/app.css" />
  <script type="text/javascript">
    $(function() {

      function subscribeToStreams(session, streams) {
        $.each(streams, function(index, stream) {
          if(stream.connection.connectionId != session.connection.connectionId) {
            session.subscribe(stream);
          }
        });
      }

      var session = TB.initSession("<?= $session ?>"); 
      var apiKey = "<?= API_Config::API_KEY ?>";
      var token = "<?= $token ?>";
      session.addEventListener('sessionConnected', function(event) {
        subscribeToStreams(session, event.streams);
        session.publish();
      });

      session.addEventListener('streamCreated', function(event) {
        subscribeToStreams(session, event.streams);
      });

      session.connect(apiKey, token);
    });

    
  </script>  
</head>
<body>
  <div class="row">
    <h1> You are in Room <?php echo $room; ?> </h1>
    <p> Anyone can join the call by going to this URL </p>
    <input type="text" width="200" value="http://wlyn.ch/leagueskype/room.php?id=<?php echo $room; ?>" />
	<div id="sessionControls">
       	<input class="button success" type="button" value="Connect to the Call" id ="connectLink" style="display:block" />
       	<input class="button alert" type="button" value="Leave" id ="disconnectLink" style="display:none" />
	</div>

  <div class="twelve columns" id="clock" style="display:none">
  </div>
</body>
</html>
