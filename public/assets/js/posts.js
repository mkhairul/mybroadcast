(function($){
    var content = window.content = {};
    content.Post = (function(){
        var postConstructor = function Post(options){
            if (false === (this instanceof Post)) {
                return new Post();
            }
            var self = this;
            var defaultOptions = {
                inputElem: '',
                url: '',
                displayElem: '',
                username: '',
                topics: []
            }
            this.options = options = $.extend(defaultOptions, options)
            if (options.username === '') { options.username = username }
            if (options.topics.length === 0) { options.topics.push(options.username); }
            // Automatically add topic of yourself on your posts
            if (options.topics.indexOf(options.username) < 0) { options.topics.push(options.username); }
            
            // Bind the "submit" button of the input/textarea element
            $('button', $(options.inputElem).parent()).on('click', function(){
                self.save();
            })
            
            this.getOptions = function(){ return options; }
            this.setOptions = function(param){ options = $.extend(options, param) }
        }
        
        postConstructor.prototype.save = function(){
            var message = $(this.options.inputElem).val();
            if (message === '') { return false; }
            if (this.options.url) {
                // Extract the hashtag and combine it with the topics
                var hashtags = twttr.txt.extractHashtags(message)
                if (hashtags) {
                    for(var i=0; i<hashtags.length; i++)
                    {
                        if (this.options.topics.indexOf(hashtags[i]) >= 0) {
                            continue;
                        }
                        this.options.topics.push(hashtags[i])
                        // Remove the hashtag from message
                        message = message.replace('#'+hashtags[i], '', 'g').trim();
                    }
                }
                $.post(this.options.url, { 'message': message, topics: this.options.topics }, function(data){
                    console.log('posting data to topic')
                    console.log(data)
                },'json');
            }
            this.display(message);
        }
        
        postConstructor.prototype.display = function(param){
            var username, topics;
            if (typeof param === 'object') {
                username = param.username
                topics = param.topics
                message = param.message
            }
            else
            {
                message = param
            }
            if (typeof username === 'undefined') { username = this.options.username; }
            if (typeof topics === 'undefined') { topics = this.options.topics; }
            
            // Create the post row
            var row = $('<div>').addClass('post-row').append(
                $('<div>').addClass('user-icon col-md-1').html($('<i>').addClass('fa fa-user'))
            ).append(
                $('<div>').addClass('col-md-11').append($('<div>').addClass('user').html(username)).append($('<div>').addClass('message').html(message)).append($('<div>').addClass('topics').html($('<ul>')))
            )
            $(this.options.inputElem).val('')
            
            if (topics.length > 0) {
                for (var i=0; i < topics.length; i++) {
                    $('ul', row).append($('<li>').html($('<a>').attr('href', '#').html('#'+topics[i])))
                }
            }
            $(this.options.displayElem).prepend(row);
        }
        
        postConstructor.prototype.load = function(url){
            var self = this;
            $.get(url, function(data){
                $.each(data, function(k,v){
                    self.display({
                            username: v.name,
                            topics: JSON.parse(v.topics),
                            message: v.message
                        });
                })
                $('.loading', self.options.displayElem).hide();
            },'json')
        }
        
        return postConstructor;
        
    }());
})(jQuery)

