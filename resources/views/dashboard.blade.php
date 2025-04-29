<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <livewire:chat>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <livewire:users-list>
                <livewire:connection-actions>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('storage/adapter-latest.js') }}" defer></script>
    <script>
        window.addEventListener('join_chat', event => {
            if (event.detail.has_unread == false) {
                var objDiv = document.getElementById('chat-' + event.detail.id);
                objDiv.scrollTop = objDiv.scrollHeight;
            }
            console.log(event);
            Echo.private('chat.' + event.detail.room_id)
                .listen('ChatUpdated', (e) => {
                if (e.type == 'message') {
                    var p = document.createElement("p");
                    p.className = "w-full";
                    var text = document.createTextNode(event.detail.name + ': ' + e.message);
                    p.appendChild(text);
                    document.getElementById('chat-' + event.detail.id).appendChild(p);

                    var objDiv = document.getElementById('chat-' + event.detail.id);
                    objDiv.scrollTop = objDiv.scrollHeight;
                    console.log(e.message + " on channel " + event.detail.room_id);
                } else if (e.type == 'call') {
                      console.log('Received message:', e.message);

                      if ( e.message.sdp) {
                        // This is called after receiving an offer or answer from another peer
                        pc.setRemoteDescription(new RTCSessionDescription( e.message.sdp), () => {
                          // When receiving an offer lets answer it
                          if (pc.remoteDescription.type === 'offer') {
                            pc.createAnswer().then(localDescCreated).catch(onError);
                          }
                        }, onError);
                      } else if ( e.message.candidate) {
                        // Add the new ICE candidate to our connections remote description
                        pc.addIceCandidate(
                          new RTCIceCandidate( e.message.candidate), onSuccess, onError
                        );
                      }

                }
            });
        })

        window.addEventListener('leave_chat', event => {
            Echo.leave('chat.' + event.detail.room_id);
            console.log('Left channel ' + event.detail.room_id);
        })

        window.addEventListener('message_sent', event => {
            var p = document.createElement("p");
            p.className = "w-full";
            var text = document.createTextNode('Me: ' + event.detail.message);
            p.appendChild(text);
            document.getElementById("chat-" + event.detail.id).appendChild(p);
            var objDiv = document.getElementById("chat-" + event.detail.id);
            objDiv.scrollTop = objDiv.scrollHeight;
            console.log('Message:  ' + event.detail.message);
        })

        window.addEventListener('start_call', event => {
          console.log(event);
            startCall(event.detail[0].constraints, event.detail[0].offerer);
            console.log(event.detail.constraints);
            console.log(event.detail.offerer);
            console.log('Started call');
        })

        window.addEventListener('close_call', event => {
            console.log('Ending call');
            if (localStream) {
                localTracks = localStream.getTracks();
                localTracks.forEach(function(track) {
                    track.stop();
                });
                localTracks = null;
                localStream = null;
            }

            if (remoteStream) {
                remoteTracks = remoteStream.getTracks();
                remoteTracks.forEach(function(track) {
                    track.stop();
                });
                remoteTracks = null;
                remoteStream = null;
            }

            if (pc) {
                pc.close();
                pc = null;
            }
        })

        function toggleMute() {
            var remote = document.getElementById("remote");
            remote.muted = !remote.muted;

            if (remote.muted) {
                document.getElementById("mute").style.visibility = "hidden";
                document.getElementById("unmute").style.visibility = "visible";
            } else {
                document.getElementById("mute").style.visibility = "visible";
                document.getElementById("unmute").style.visibility = "hidden";
            }
            console.log(remote.muted);
        }



        var sdpConstraints = {
            offerToReceiveAudio: true,
            offerToReceiveVideo: true
        };

        var pc_config = {
            'iceServers': [
              {'urls': 'stun:stun.l.google.com:19302'},
                // {'urls': 'stun:stun.' + window.location.hostname + ':3478'},
                // {'urls': 'turn:turn.' + window.location.hostname + ':3479', 'username' : 'test', 'credential': 'test123'}
            ]
        };



        var pc;
        var localStream;
        var remoteStream;

        function onSuccess() {};
        function onError(error) {
          console.error(error);
        };

        function localDescCreated(desc) {
          pc.setLocalDescription(
            desc,
            () => sendMessage({'sdp': pc.localDescription}),
            onError
          );
        }

        function sendMessage(message) {
          Livewire.dispatch('sentLocal', {message: message});
        }

        function startCall(constraints, offerer) {
            var local;
            var remote;

            pc = new RTCPeerConnection(pc_config);

            // 'onicecandidate' notifies us whenever an ICE agent needs to deliver a
            // message to the other peer through the signaling server
            pc.onicecandidate = event => {
              if (event.candidate) {
                sendMessage({'candidate': event.candidate});
              }
              console.log(event);
            };

            // If user is offerer let the 'negotiationneeded' event create the offer
            if (offerer) {
              pc.onnegotiationneeded = () => {
                pc.createOffer().then(localDescCreated).catch(onError);
              }
            }

            // When a remote stream arrives display it in the #remoteVideo element
            pc.ontrack = event => {
              remoteStream = event.streams[0];
              remote = document.querySelector('#remote');
              if (!remote.srcObject || remote.srcObject.id !== remoteStream.id) {
                remote.srcObject = remoteStream;
              }
            };

            navigator.mediaDevices.getUserMedia(constraints).then(stream => {
              // Display your local video in #localVideo element
              localStream = stream;
              local = document.querySelector('#local');
              local.srcObject = localStream;
              // Add your stream to be sent to the conneting peer
              localStream.getTracks().forEach(track => pc.addTrack(track, stream));
              console.log(stream);
            }, onError);

            pc.oniceconnectionstatechange = function() {
                if(pc.iceConnectionState == 'disconnected') {
                    Livewire.emitTo('activities', 'closeCall');
                }
            }
        }
    </script>
@endpush

</x-app-layout>
