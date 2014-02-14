/*
 * sudo npm install amqp pubsub-js
 *
 */

var amqp = require('amqp');
var config = require('./config');
var connection = amqp.createConnection({ host: config.host, port: config.port, login: config.login, password: config.password });
var io = require('socket.io').listen(8080);
var pubsub = require('pubsub-js');
var querystring = require('querystring');
var http = require('http');
var count = 1;

var users = {};
var rooms = {};

connection.on('ready', function () {
	connection.exchange("updates", options={type:'fanout',durable:true}, function(exchange) {   
		var sendMessage = function(exchange, payload) {
			console.log('about to publish')
			var encoded_payload = JSON.stringify(payload);
			exchange.publish('', encoded_payload, {})
		}

		var broadcastMessage = function(msg,socket){}
		
		// Recieve messages
		connection.queue("my_queue_name", function(queue){
			queue.bind(exchange, ''); 
			queue.subscribe(function (message) {
				var encoded_payload = unescape(message.data)
				var payload = JSON.parse(encoded_payload)
				pubsub.publish('broadcast_message', payload);
			})
		})

		io.sockets.on('connection', function(socket){
			pubsub.subscribe('broadcast_message', function(pubsub_name, msg){
				console.log('trying to broadcast');
				var message = msg;
				socket.emit(message.type, message.data);
			})
			
			//var updatePresence = function(users, rooms){
			pubsub.subscribe('updatePresence', function(pubsub_name, params){
				console.log('in updatePresence')
				var post_data = querystring.stringify({
					'rooms' : JSON.stringify(params.rooms),
					'users' : JSON.stringify(params.users)
				});
				console.log(post_data);
				console.log(params.message)
				
				// An object of options to indicate where to post to
				var post_options = {
					host: 'localhost',
					port: '80',
					path: '/presence',
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
						'Content-Length': post_data.length
					}
				};
				// Set up the request
				
				var post_req = http.request(post_options, function(res) {
					res.setEncoding('utf8');
					res.on('data', function (chunk) {
						console.log('Response: ' + chunk);
					});
				});
				post_req.write(post_data);
				post_req.end();
			});
			
			socket.on('presence', function(data){
				var socket_id = socket.id;
				var room_id = data.room;
				var user_id = data.user;
				
				if ((room_id in rooms) == false){ rooms[room_id] = new Array(); }
				if ((user_id in rooms[room_id]) == false)
				{
					var tmp = {}
					tmp[user_id] = socket_id;
					rooms[room_id].push(tmp);
				}
				if ((socket_id in users) == false) { users[socket_id] = new Array(); }
				if ((room_id in users[socket_id]) == false)
				{
					var tmp = {}
					tmp[room_id] = user_id
					users[socket_id].push(tmp);
				}
				
				//updatePresence(users, rooms);
				pubsub.publish('updatePresence', { 'users':users, 'rooms':rooms});
				
				console.log(users);
				console.log(rooms);
				console.log(data);
			})
			
			socket.on('disconnect', function(data){
				var socket_id = socket.id;
				console.log('closing socket for: '+socket_id)
				// Get the user information
				if ((socket_id in users) == false) { return;}
				user_rooms = users[socket_id];
				
				// empty all presence in rooms
				for (i=0; i<user_rooms.length; i++) {
					var room_id = '';
					for (var key in user_rooms[i]) {
						room_id = key;
						user_id = user_rooms[i].room_id
						
						// delete the presence in rooms
						for (j=0; j<rooms[room_id].length; j++) {
							if (user_id in rooms[room_id][j]){
								//rooms[room_id].splice(j,1)
								delete rooms[room_id][j];
								break;
							}
						}
					}
				}
				// delete the element
				delete users[socket_id];
				pubsub.publish('updatePresence', { 'users':users, 'rooms':rooms, 'message':'publish presence after closing socket'});
			})
		})
		
	})
})
