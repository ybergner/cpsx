$(function() {
    getMessages = function() {
        var self = this;
        var _sRandom = Math.random();  

        $.getJSON('index.php?action=get_last_messages' + '&_r=' + _sRandom, function(data){
            if(data.messages) {
                $('.chat_main').html(data.messages);
            }

            // start it again;
            setTimeout(function(){
               getMessages();
            }, 4000);
        });
    }
    getMessages();
});
