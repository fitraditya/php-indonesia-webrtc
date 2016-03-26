<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>PHP-WebRTC :: Peer Connection (Alice)</title>
  <link rel="stylesheet" href="assets/css/bootstrap.css">
  <link rel="stylesheet" href="assets/css/bootstrap-theme.css">
  <style>
    body {
      padding-top: 50px;
    }
    #local-video, #remote-video {
      width: 100%;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <a class="navbar-brand" href="#">Peer Connection :: Alice</a>
      </div>
    </div>
  </nav>
  <div class="container">
    <h3>Peer Connnection Demo</h3>
    <div class="row">
      <div class="col-xs-6">
        <video id="local-video" autoplay muted></video>
        <p><button id="call-button" style="display: none;">Call Bob</button></p>
      </div>
      <div class="col-xs-6">
        <video id="remote-video" autoplay muted></video>
      </div>
    </div>
  </div>
  <script src="assets/js/jquery.js"></script>
  <script src="assets/js/bootstrap.js"></script>
  <script>
    $(function() {
      var servers = null;
      var localStream = null;
      var localVideoTrack = null;
      var peerConnection = null;

      var localVideo = document.querySelector('#local-video');
      var remoteVideo = document.querySelector('#remote-video');

      navigator.mediaDevices.getUserMedia({ video: true })
      .then(function(stream) {
        localStream = stream;
        localVideo.srcObject = localStream;
        $('#call-button').show();
        $('#call-button').attr('disabled', false);
      })
      .catch(function(error) {
        alert('Error: ', error);
      });

      var constraints = {
        offerToReceiveAudio: 1,
        offerToReceiveVideo: 1
      };

      // Call button clicked
      $('#call-button').on('click', function() {
        $(this).attr('disabled', true);

        localVideoTrack = localStream.getVideoTracks();

        // Gathering network info
        peerConnection = new RTCPeerConnection(servers);
        // Set callback
        peerConnection.onicecandidate = onIceCandidate;
        peerConnection.onaddstream = onRemoteStreamAdded;
        peerConnection.onremovestream = onRemoteStreamRemoved;
        // Attach video
        peerConnection.addStream(localStream);
        // Send offer
        peerConnection.createOffer(setLocalAndSaveMessage, onCreateOfferError, constraints);
      });

      // Got network info
      function onIceCandidate(event) {
        console.log('onIceCandidate event: ', event);
        if (event.candidate) {
          // Send candidate
          var candidate = {
            type: 'candidate',
            label: event.candidate.sdpMLineIndex,
            id: event.candidate.sdpMid,
            candidate: event.candidate.candidate
          };
          saveMessage("from=alice&to=bob&type=candidate&message=" + JSON.stringify(candidate));
        } else {
          console.log('End of candidates.');
        }
      }

      function onRemoteStreamAdded(event) {
        remoteVideo.srcObject = event.stream;
      }

      function onRemoteStreamRemoved(event) {
        //
      }

      function setLocalAndSaveMessage(sessionDescription) {
        console.log('Got session description: ' , sessionDescription);
        peerConnection.setLocalDescription(sessionDescription);
        saveMessage("from=alice&to=bob&type=offer&message=" + JSON.stringify(sessionDescription));
      }

      function onCreateOfferError(event) {
        //
      }

      var looper;

      function saveMessage(message) {
        var xhr = new XMLHttpRequest;
        var host = location.protocol + '//' + location.host;
        var path = host + '/saveMessage.php';

        xhr.onreadystatechange = function() {
          if (xhr.readyState == 4 && xhr.status == 200) {
            looper = setInterval(checkMessage, 3000);
          }
        };
        xhr.open('POST', path, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.setRequestHeader("Content-length", message.length);
        xhr.setRequestHeader("Connection", "close");
        xhr.send(message);
      }

      function checkMessage() {
        var xhr = new XMLHttpRequest;
        var host = location.protocol + '//' + location.host;
        var path = host + '/checkMessage.php' + '?to=alice';
        var response = null;

        xhr.onreadystatechange = function() {
          if (xhr.readyState == 4 && xhr.status == 200) {
            response = JSON.parse(xhr.responseText);
            if (response.result) {
              $.each(response.data, function(i, value) {
                var sdp = JSON.parse(value.messages);
                if (sdp.type == 'candidate') {
                  // Attach network info
                  var candidate = new RTCIceCandidate({
                    sdpMLineIndex: sdp.label,
                    candidate: sdp.candidate
                  });
                  //alert(JSON.stringify(candidate));
                  peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                }
                if (sdp.type == 'answer') {
                  // Set remote description
                  //alert(JSON.stringify(sdp));
                  peerConnection.setRemoteDescription(new RTCSessionDescription(sdp));
                }
              });
            }
          }
        };
        xhr.open('GET', path, true);
        xhr.send();
      }

    })
  </script>
</body>

</html>
