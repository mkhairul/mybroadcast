(function($){
    
    var Post = window.Post = function(options){
        var self = this
        var defaultOptions = {
            input: $('.post'),
            url: '',
            displayElem: $('.posts'),
            username: '',
            topics: []
        }
        
        $('button', $(options.input).parent()).on('click', function(){
            save();
        })
        
        var set = function(options){
            if (typeof options == 'object') {
                self.options = $.extend(defaultOptions, options);
            } else {
                self.options = defaultOptions;
            }
        }
        set(options);
        
        var save = function(){
            var message = $(options.input).val();
            if (message == '') {
                return;
            }
            $(options.input).val('')
            /*
            $.post(options.url, {'message':message}, function(data){
                
            },'json')
            */
            display(message);
        }
        
        var display = function(param){
            if (typeof(param) !== 'object') { message = param }
            if (param.message) { message = param.message; }
            if (param.username) { options.username = username; }
            if (param.topics) { options.topics = topics; }
            if (!options.topics) {
                options.topics = new Array();
                options.topics.push(options.username);
            }
            var row = $('<div>').addClass('post-row')
            var user = $('<div>').addClass('user-icon col-md-1').html(
                $('<i>').addClass('fa fa-user')
            )
            var content = $('<div>').addClass('col-md-11').append(
                $('<div>').addClass('user').html(options.username)
            ).append(
                $('<div>').addClass('message').html(message)
            )
            var topic = $('<div>').addClass('topics').html($('<ul>'));
            for (var i=0; i < options.topics.length; i++) {
                $('ul', topic).append($('<li>').html($('<a>').attr('href', '#').html('#'+options.topics[i])))
            }
            
            // AVENGERS ASSEMBLEEEEE
            $(row).append(user).append($(content).append(topic))
            $('.posts-container').prepend(row);
        }
        
        return {
            save: save,
            display: display,
            set: set
        }
    }
    
})(jQuery)

