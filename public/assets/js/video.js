(function($, Erizo){
    
    if ((typeof window.content) !== 'object') {
        var content = window.content = {};
    }else{
        var content = window.content;
    }
    
    content.Video = (function(){
        
        var videoConstructor = function Video(options){
            if (false === (this instanceof Video)) {
                return new Video();
            }
            
            var self = this;
            var defaultOptions = {
                username: 'user',
                role: 'presenter',
                serverUrl: '',
                localStream: '',
                room: '',
                recording: false,
                audio: true,
                video: true,
                data: true,
                screen: '',
                videoSize: [640, 480, 640, 480]
            };
            this.options = options = $.extend(defaultOptions, options)
            this.getOptions = function(){ return options; }
            this.setOptions = function(param){ options = $.extend(options, param) }
            this.options.localStream = Erizo.Stream({
                                            audio: this.options.audio,
                                            video: this.options.video,
                                            data: this.options.data,
                                            screen: this.options.screen,
                                            videoSize: this.options.videoSize
                                        });
        }
        
        videoConstructor.prototype.createToken = function(callback){
            var self = this;
            var role = "presenter";
            var req = new XMLHttpRequest();
            var url = self.options.serverUrl + '/createToken/';
            var body = { username: self.options.username, role: role };
            
            req.onreadystatechange = function(){
                if (req.readyState === 4) {
                    console.log('callback');
                    callback(req.responseText)
                }
            }
            console.log('is it started?');
            console.log(url);
            req.open('POST', url, true);
            req.setRequestHeader('Content-Type', 'application/json');
            req.send(JSON.stringify(body));
        }
        
        videoConstructor.prototype.start = function(){
            var self = this;
            console.log('start');
            this.createToken(function(response){
                var token = response;
                console.log('video token: ', + token)
                room = Erizo.Room({token:token});
                
                self.options.localStream.addEventListener("access-accepted", function(){
                    var subscribeToStreams = function(streams){
                        for (var index in streams){
                            var stream = streams[index];
                            if (self.options.localStream.getID() !== stream.getID()) {
                                room.subscribe(stream);
                            }
                        }
                    }
                    
                    room.addEventListener("room-connected", function(roomEvent){
                        room.publish(self.options.localStream, {maxVideoBW: 300});
                        subscribeToStreams(roomEvent.streams);
                    })
                    
                    room.addEventListener("stream-subscribed", function(streamEvent){
                        var stream = streamEvent.stream;
                        var div = document.createElement('div');
                        div.setAttribute("style", "width: 320px; height: 240px;");
                        div.setAttribute("id", "test" + stream.getID());
                
                        $('.posts').append(div);
                        stream.show("test" + stream.getID());
                    })
                    
                    room.addEventListener("stream-added", function(streamEvent){
                        var streams = [];
                        streams.push(streamEvent.stream);
                        subscribeToStreams(streams);
                    })
                    
                    room.addEventListener("stream-removed", function(streamEvent){
                        var stream = streamEvent.stream;
                        if (stream.elementID !== undefined) {
                          $('#'+stream.elementID).remove();
                        }
                    })
                    
                    room.connect();
                    self.options.localStream.show("myVideo");
                })
            })
        }
        
        return videoConstructor;
    }());
})(jQuery, Erizo);