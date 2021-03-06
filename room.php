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
	<script src="http://static.opentok.com/v1.1/js/TB.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="jquery.js" type="text/javascript"> </script>
  <link rel="stylesheet" href="css/foundation.min.css" />
  <link rel="stylesheet" href="css/app.css" />
	<script type="text/javascript" charset="utf-8">
    TB.setLogLevel(TB.DEBUG);
    var apiKey = "<?php echo API_Config::API_KEY; ?>"; // Replace with your API key. See https://dashboard.tokbox.com/projects
    var sessionId = '<?php echo $session; ?>'; // Replace with your own session ID. See https://dashboard.tokbox.com/projects 
    var token = '<?php echo $token; ?>'; // Replace with a generated token. See https://dashboard.tokbox.com/projects


    var subscribers = {};
    var publisher;
		var session;

		// Un-comment either of the following to set automatic logging and exception handling.
		// See the exceptionHandler() method below.
		// TB.setLogLevel(TB.DEBUG);
		TB.addEventListener("exception", exceptionHandler);

		if (TB.checkSystemRequirements() != TB.HAS_REQUIREMENTS) {
			alert("You don't have the minimum requirements to run this application."
				  + "Please upgrade to the latest version of Flash.");
		} else {
			session = TB.initSession(sessionId);

			// Add event listeners to the session
      session.addEventListener("sessionConnected", function(event) {
        sessionConnectedHandler(event);
        startclock();
        startPublishing();
        turnOffMyVideo();
        hide('connectLink');
        show('disconnectLink');
      });
			session.addEventListener("streamCreated", streamCreatedHandler);
			session.addEventListener("streamDestroyed", streamDestroyedHandler);
			session.addEventListener("streamPropertyChanged", streamPropertyChangedHandler);
		}

		//--------------------------------------
		//  OPENTOK EVENT HANDLERS
		//--------------------------------------
    function sessionConnectedHandler(event) {
      subscribeToStreams(event.streams);

			show('disconnectLink');
			hide('connectLink');
    }

    function streamCreatedHandler(event) {
        subscribeToStreams(event.streams);
    }

    function streamDestroyedHandler(event) {
			var publisherContainer = document.getElementById("opentok_publisher");
			var videoPanel = document.getElementById("videoPanel");
			for (i = 0; i < event.streams.length; i++) {
				var stream = event.streams[i];
				if (stream.connection.connectionId == session.connection.connectionId) {
					videoPanel.removeChild(publisherContainer);
				} else {
					var streamContainerDiv = document.getElementById("streamContainer" + stream.streamId);
					if(streamContainerDiv) { videoPanel = document.getElementById("videoPanel")
						videoPanel.removeChild(streamContainerDiv);
					}
				}
			}
    }
		
		function streamPropertyChangedHandler(event)
		{
			var stream = event.stream;
			var audioControls = document.getElementById(stream.streamId + "-audioControls");
			if (audioControls && event.changedProperty == "hasAudio") {
				if (event.newValue == true) {
					audioControls.style.display = "block";
				} else {
					audioControls.style.display = "none";
				}
			} else if (audioControls && event.changedProperty == "hasVideo") {
				if (event.newValue == true) {
					audioControls.style.display = "block";
				} else {
					audioControls.style.display = "none";
				}
			}
		}

		function exceptionHandler(event) {
			alert("Exception: " + event.code + "::" + event.message);
		}

		//--------------------------------------
		//  LINK CLICK HANDLERS
		//--------------------------------------

		/*
		If testing the app from the desktop, be sure to check the Flash Player Global Security setting
		to allow the page from communicating with SWF content loaded from the web. For more information,
		see http://www.tokbox.com/opentok/build/tutorials/helloworld.html#localTest
		*/
		function connect() {
			session.connect(apiKey, token);
		}

		function disconnect() {
      stopclock();
			session.disconnect();
			hide('disconnectLink');
      show('connectLink');
		}

		// Called when user wants to start publishing to the session
		function startPublishing() {
			if (!publisher) {
				var containerDiv = document.createElement('div');
				containerDiv.className = "subscriberContainer";
				containerDiv.setAttribute('id', 'opentok_publisher');
				containerDiv.style.float = "left";
				var videoPanel = document.getElementById("videoPanel");
				//videoPanel.appendChild(containerDiv);
				
				var publisherDiv = document.createElement('div'); // Create a div for the publisher to replace
				publisherDiv.setAttribute('id', 'replacement_div')
				containerDiv.appendChild(publisherDiv);
				
				var publisherProperties = new Object();
				
				publisher = TB.initPublisher(apiKey, 'fuckyou', publisherProperties);
				session.publish(publisher); 
													// Pass the replacement div id to the publish method
				var publisherControlsDiv = getPublisherControls();
				publisherControlsDiv.style.display = "block";
				containerDiv.appendChild(publisherControlsDiv);
			}
		}

		function stopPublishing() {
			if (publisher) {
				session.unpublish(publisher);
			}
			publisher = null; 
		}


		//--------------------------------------
		//  HELPER METHODS
		//--------------------------------------
    function subscribeToStreams(streams) {
        for (i = 0; i < streams.length; i++) {
            var stream = streams[i];

            var containerDiv = document.createElement('div'); // Create a container for the subscriber and its controls
            containerDiv.className = "subscriberContainer";
            var divId = stream.streamId;    // Give the div the id of the stream as its id
            containerDiv.setAttribute('id', 'streamContainer' + divId);
            var videoPanel = document.getElementById("videoPanel");
            videoPanel.appendChild(containerDiv);

            var subscriberDiv = document.createElement('div'); // Create a replacement div for the subscriber
            subscriberDiv.setAttribute('id', divId);
            subscriberDiv.style.cssFloat = "top";
            containerDiv.appendChild(subscriberDiv);
            subscribers[stream.streamId] = session.subscribe(stream, divId);

            var actionDiv = document.createElement('div');
            var streamId = stream.streamId
              actionDiv.setAttribute('id', 'action-'+streamId);
            actionDiv.style.float = "bottom";
            actionDiv.style.borderStyle = "solid 1px black";

            var audioControlsDisplay;
            if (stream.hasAudio) {
              audioControlsDisplay = "block";
            } else {
              audioControlsDisplay = "none";
            }
            var videoControlsDisplay;
            if (stream.hasVideo) {
              videoControlsDisplay = "block";
            } else {
              videoControlsDisplay = "none";
            }
                actionDiv.innerHTML = 
					'<span id="' + streamId +'-audioControls" style="display:' + audioControlsDisplay + '"> \
					<a href="#" id="'+streamId+'-audioOff" onclick="turnOffHerAudio(\''+streamId+'\');" style="display:block">Turn off audio<\/a>\
				   <a href="#" id="'+streamId+'-audioOn" onclick="turnOnHerAudio(\''+streamId+'\')" style="display:none">Turn on audio<\/a>\
				   <\/span> \
					<span id="' + streamId +'-videoControls" style="display:' + videoControlsDisplay + '"> \
				   <a href="#" id="'+streamId+'-videoOff" onclick="turnOffHerVideo(\''+streamId+'\')" style="display:block">Turn off video<\/a>\
				   <a href="#" id="'+streamId+'-videoOn" onclick="turnOnHerVideo(\''+streamId+'\')" style="display:none">Turn on video<\/a>\
				   <\/span>';

                containerDiv.appendChild(actionDiv);
            }
        }

		function getPublisherControls() {
			sessionControlsDiv = document.createElement('div');
        	sessionControlsDiv.innerHTML = 
				'<a href="#" id="audioOff" onClick="turnOffMyAudio(); return false;" style="display:none;">Turn off my audio<\/a>' +
        		'<a href="#" id="audioOn" onClick="turnOnMyAudio(); return false;" style="display:none;">Turn on my audio<\/a>' +
        		'<a href="#" id="videoOff" onClick="turnOffMyVideo(); return false;" style="display:none;">Turn off my video<\/a>' +
        		'<a href="#" id="videoOn" onClick="turnOnMyVideo(); return false;" style="display:none;">Turn on my video<\/a>'
			return sessionControlsDiv;
		}
        function turnOffHerVideo(streamId) {
                var subscriber = subscribers[streamId];
                subscriber.subscribeToVideo(false);

                hide(streamId+"-videoOff");
                show(streamId+"-videoOn");
        }

        function turnOnHerVideo(streamId) {
                var subscriber = subscribers[streamId];
                subscriber.subscribeToVideo(true);

                hide(streamId+"-videoOn");
                show(streamId+"-videoOff");
        }

        function turnOffHerAudio(streamId) {
                var subscriber = subscribers[streamId];
                subscriber.subscribeToAudio(false);

                hide(streamId+"-audioOff");
                show(streamId+"-audioOn");
        }

        function turnOnHerAudio(streamId) {
                var subscriber = subscribers[streamId];
                subscriber.subscribeToAudio(true);

                hide(streamId+"-audioOn");
                show(streamId+"-audioOff");
        }

        function turnOffMyVideo() {
            publisher.publishVideo(false);
        }

        function turnOnMyVideo() {
            publisher.publishVideo(true);

            hide("videoOn");
            show("videoOff");
        }

        function turnOffMyAudio() {
            publisher.publishAudio(false);

            hide("audioOff");
            show("audioOn");
        }

        function turnOnMyAudio() {
            publisher.publishAudio(true);

            hide("audioOn");
            show("audioOff");
        }

		function toggleMicSettings() {
			deviceManager.showMicSettings = !deviceManager.showMicSettings;
		}

		function toggleCamSettings() {
			deviceManager.showCamSettings = !deviceManager.showCamSettings;
		}

		//--------------------------------------
		//  UTILITY METHODS
		//--------------------------------------
		function show(id) {
			document.getElementById(id).style.display = 'block';
		}

		function hide(id) {
			document.getElementById(id).style.display = 'none';
		}

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
       	<input class="button success" type="button" value="Connect to the Call" id ="connectLink" onClick="connect()" style="display:block" />
       	<input class="button alert" type="button" value="Leave" id ="disconnectLink" onClick="disconnect()" style="display:none" />
	</div>

  <div class="twelve columns" id="clock" style="display:none">
  </div>
  
  <div id="videoPanel" style="visibility:hidden"></div>
  <div>
  <div id="fuckyou" style="display:none"> </div>
  </div>
</body>
</html>
