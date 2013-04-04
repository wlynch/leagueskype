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
            //add a div to .videos

            var div = $('<div></div>');
            var id = 'stream-'+stream.connection.connectionId;
            div.attr('id', id);

            $('.videos').append(div);

            session.subscribe(stream, id, {});
          }
        });
      }

      var session = TB.initSession("<?= $session ?>"); 
      var apiKey = "<?= API_Config::API_KEY ?>";
      var token = "<?= $token ?>";

      var publisher = null;

      session.addEventListener('sessionConnected', function(event) {
        subscribeToStreams(session, event.streams);
        session.publish(publisher);
        startclock();
      });

      session.addEventListener('streamCreated', function(event) {
        subscribeToStreams(session, event.streams);
      });

      $('#connectLink').click(function() {
        var div = $('<div></div>');
        div.attr('id', 'publisher');

        $('.videos').append(div);

        publisher = TB.initPublisher(apiKey, 'publisher', {});
        session.connect(apiKey, token);
        $('#connectLink').hide();
        $('#disconnectLink').show();
      });

      $('#disconnectLink').click(function() {
        stopclock();
        session.disconnect();
        $('#connectLink').show();
        $('#disconnectLink').hide();
      });
    });

    var now = 0;
    var interval = null;

    function startclock() {
      $('#clock').show();
      interval = setInterval(tick, 1000);
    }

    function stopclock() {
      clearInterval(interval);
      $('#clock').hide();
    }

    function tick() {
      now += 1;
      mins = Math.floor(now / 60);
      secs = now % 60;

      if(secs >= 10)
        str = mins + ':' + secs;
      else
        str = mins + ':0' + secs;
      $('#clock').html(str);
    }


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
    <div class="videos">
    </div>
  </div>
</body>
</html>
