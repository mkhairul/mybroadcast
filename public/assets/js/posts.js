(function($){
    
    
    var Post = window.Post = function(options){
        var defaultOptions = {
            input: $('.post'),
            url: '',
            displayElem: $('.posts')
        }
        if (typeof options == 'object') {
            options = $.extend(defaultOptions, options);
        } else {
            options = defaultOptions;
        }
        
        return {
            save: function(){
                $.post(options.url, {message:options.input}, function(data){
                    
                },'json')
            },
            create: function(){
                
            }
        }
    }
    
})(jQuery)

