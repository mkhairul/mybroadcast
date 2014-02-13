/*
 * sudo npm install amqp pubsub-js
 *
 */

var amqp = require('amqp');
var config = require('./config');
var connection = amqp.createConnection({ host: config.host, port: config.port, login: config.login, password: config.password });
var io = require('socket.io').listen(8080);
var pubsub = require('pubsub-js');
var count = 1;

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
			console.log('Created queue')
			queue.bind(exchange, ''); 
			queue.subscribe(function (message) {
				console.log('subscribed to queue')
				var encoded_payload = unescape(message.data)
				var payload = JSON.parse(encoded_payload)
				console.log('Recieved a message:')
				console.log(payload);
				pubsub.publish('broadcast_message', payload);
			})
		})

		io.sockets.on('connection', function(socket){
			pubsub.subscribe('broadcast_message', function(pubsub_name, msg){
				console.log('trying to broadcast');
            
				var message = msg;
				socket.emit(message.type, message.data);
			})
		})

		/*
		setInterval( function() {    
		  var test_message = 'TEST '+count
		  sendMessage(exchange, test_message)  
		  count += 1;
		}, 2000) */
	})
})
