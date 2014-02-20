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
            this.display();
        }
        
        postConstructor.prototype.display = function(){
            // Create the post row
            var row = $('<div>').addClass('post-row').append(
                $('<div>').addClass('user-icon col-md-1').html($('<i>').addClass('fa fa-user'))
            ).append(
                $('<div>').addClass('col-md-11').append($('<div>').addClass('user').html(this.options.username)).append($('<div>').addClass('message').html($(this.options.inputElem).val())).append($('<div>').addClass('topics').html($('<ul>')))
            )
            $(this.options.inputElem).val('')
            
            if (this.options.topics.length > 0) {
                for (var i=0; i < this.options.topics.length; i++) {
                    $('ul', row).append($('<li>').html($('<a>').attr('href', '#').html('#'+this.options.topics[i])))
                }
            }
            $(this.options.displayElem).prepend(row);
        }
        
        return postConstructor;
        
    }());
})(jQuery)

