                    <div id="<?php echo $room_id; ?>" class="chat-window">
						<h2>#lobby</h2>
						<div class="chat">
                            <!--
							<div class="col-md-12 chat-row">
                                <div class="col-md-2 col-xs-12 col-sm-6 name"><div class="datetime">[2:15PM]</div> Test</div>
                                <div class="col-md-10 col-xs-12 col-sm-6 message">Waaaattt?</div>
                            </div>
                            -->
						</div>
						<div class="message">
							<input type="text" class="form-control" value="" placeholder="Type your message">
						</div>
					</div>
                    
                    <script>
                        $('.chat-window').height($('#page-content').height()-$('#page-content .container').position().top)
                        var room_id = '<?php echo $room_id; ?>';
                        $('#'+room_id).on('keypress', '.message input', function(e){
                            var p = e.which;
                            if(p == 13)
                            {
                                console.log('Do something');
                                PubSub.publish('sendMessage', $(this).val());
                                $(this).val('');
                            }
                        })
						
						var display_message = function(user, msg)
						{
							// create row
                            var row = $('<div>').addClass('col-md-12 chat-row');
                            var name = $('<div>').addClass('col-md-2 col-xs-12 col-sm-6 name').html(user);
                            $(name).prepend($('<div>').addClass('datetime').html('['+get_time()+']'))
                            var message = $('<div>').addClass('col-md-10 col-xs-12 col-sm-6 message').html(msg);
                            
                            $(row).append(name).append(message);
                            $('#'+room_id+' .chat').append(row);
							return row;
						}
                        
                        var send_message = function(event_name, msg)
                        {
                            var row = display_message(username, msg)
							
							// Sends message to server
							$.post('<?php echo action("RoomController@sendMessage"); ?>', {'message':msg, id:"<?php echo $room_id; ?>" }, function(data){
								if (data.status == 'ok') {
									$('.message', row).append('<i class="glyphicon glyphicon-ok"></i>');
								}
							},'json')
                        }
                        PubSub.subscribe('sendMessage', send_message);
                        
                        var new_join = function()
                        {
                            // create row
                            var row = $('<div>').addClass('col-md-12 chat-row');
                            var name = $('<div>').addClass('col-md-2 col-xs-12 col-sm-6 name');
                            $(name).prepend($('<div>').addClass('datetime').html('['+get_time()+']'))
                            var message = $('<div>').addClass('col-md-10 col-xs-12 col-sm-6 message').html('<strong>'+username+' has joined the room</strong>');
                            
                            $(row).append(name).append(message);
                            $('#'+room_id+' .chat').append(row);
							
							// highlight the room on the sidebar
							$('#rooms [data-room="'+room_id+'"]').addClass('active');
                        }
                        PubSub.subscribe('newJoin', new_join);
                        
                        var get_time = function(){
                            return moment().format('h:mmA');
                        }
						
						//socket.on('update', function (data) {
						socket.on('<?php echo $room_id; ?>', function (data) {
							if (data.user != username) {
								display_message(data.user, data.message)
							}
						});
						socket.emit('presence', { user: username, room: room_id });
                        PubSub.publish('newJoin');
                    </script>