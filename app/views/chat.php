                    <div id="<?php echo $room_id; ?>" class="chat-window">
						<h2>#lobby</h2>
						<div class="chat-options">
							<a href="#" class="users active"><i class="fa fa-user"></i></a>
							<div class="users-list">
								<ul></ul>
							</div>
						</div>
						<div class="chat">
							<div class="col-md-12 chat-row loading">
								<img src="assets/img/ajax-loader.gif" /> Loading history..
                            </div>
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
						var user_list = [];
                        $('#'+room_id).on('keypress', '.message input', function(e){
                            var p = e.which;
                            if(p == 13)
                            {
                                console.log('Do something');
                                PubSub.publish('sendMessage', $(this).val());
                                $(this).val('');
                            }
                        })
						
						var display_history = function()
						{
							$.get('<?php echo action("RoomController@getHistory"); ?>', {room_id:'<?php echo $room_id; ?>'}, function(data){
								$('.chat-row.loading').remove();
								if (data.message.length > 0) {
									$.each(data.message, function(k,v){
										var local_date = moment(v.created_at.date + ' ' + v.created_at.timezone).zone(moment().zone()).format('YYYY-MM-DD h:mmA')
										var row = $('<div>').addClass('col-md-12 chat-row');
										var name = $('<div>').addClass('col-md-2 col-xs-12 col-sm-6 name').html(v.user);
										$(name).prepend($('<div>').addClass('datetime').html('['+local_date+']'))
										var message = $('<div>').addClass('col-md-10 col-xs-12 col-sm-6 message').html(v.message);
										
										$(row).append(name).append(message);
										$('#<?php echo $room_id; ?> .chat').prepend(row);
									})
								}
								// scroll to the bottom
								var myDiv = $("#<?php echo $room_id; ?> .chat");
								$(myDiv).scrollTop($('.chat-row', myDiv).length * 20);
								
								PubSub.publish('end-loadHistory', '<?php echo $room_id; ?>');
							})
						}
						PubSub.subscribe('loadHistory', display_history);
						
						var display_message = function(user, msg)
						{
							// create row
                            var row = $('<div>').addClass('col-md-12 chat-row');
                            var name = $('<div>').addClass('col-md-2 col-xs-12 col-sm-6 name').html(user);
                            $(name).prepend($('<div>').addClass('datetime').html('['+get_time()+']'))
                            var message = $('<div>').addClass('col-md-10 col-xs-12 col-sm-6 message').html(msg);
                            
                            $(row).append(name).append(message);
                            $('#<?php echo $room_id; ?> .chat').append(row);
							
							// scroll to the bottom
							var myDiv = $("#<?php echo $room_id; ?> .chat");
							$(myDiv).scrollTop($('.chat-row', myDiv).length * 20);
							
							return row;
						}
                        
                        var send_message = function(event_name, msg)
                        {
							if (msg == '') {
								return;
							}
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
						
						var diff = function(A, B) {
							return A.filter(function (a) {
								return B.indexOf(a) == -1;
							});
						}
						
						var update_presence = function(event_name, users)
						{
							var tmp_users = []
							$.each(users, function(index, user)
							{
								for(var key in user)
								{						
									// set in a list to compare with existing users
									tmp_users.push(key)
								}	
							})
							
							// get the existing users
							// refresh the user list
							user_list = []
							$('.users-list [data-username]').each(function(){
								user_list.push($(this).attr('data-username'));
							})
							
							// remove users who are not in the latest presence
							var missing = diff(user_list, tmp_users);
							for(var i=0; i<missing.length; i++)
							{
								tmp_users.splice(user_list.indexOf(missing[i]), 1);
								$('.users-list ul li a[data-username="'+missing[i]+'"]').parent().remove();
							}
							
							var new_users = diff(tmp_users, user_list);
							for(var i=0; i<new_users.length; i++)
							{
								if ($('.users-list ul li a[data-username="'+new_users[i]+'"]').length > 0) {
									continue;
								}
								var list = $('<li/>');
								var names = $('<a/>').attr('href', '#');
								$(names).attr('data-username', new_users[i]).html(new_users[i]);
								$(list).html(names);
								$('.users-list ul').append(list);
							}
						}
						PubSub.subscribe('<?php echo $room_id; ?>', update_presence);
						
						//socket.on('update', function (data) {
						socket.on('<?php echo $room_id; ?>', function (data) {
							if (data.user != username) {
								display_message(data.user, data.message)
							}
						});
						socket.emit('presence', { user: username, room: room_id });
                        //PubSub.publish('newJoin');
						PubSub.publish('loadHistory');
                    </script>