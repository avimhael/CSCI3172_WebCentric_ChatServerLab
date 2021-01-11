// This code has been borrowed from the book: 'PHP and MySQL Web Development' 5th Edition by Luke Welling and Laura Thomson
// Chapter 23 'Integrating JavaScript and PHP' pg 515
// This script's function is to perform an async GEt request to poll chat.php at regular intervals (five seconds) to retrieve messages, rendering them in a chat bubble. 
// Additionally, it will grab a message that was sent, send it to the server, and render it for viewing. It then calls setTimeout() to schedule pollServer() again


var pollServer = function() {
    $.get('chat.php', function(result) {

        if(!result.success) {                             
            console.log("Error polling server for new messages!");            // If polling was not successful, print out an error message
            return;
        }
        $.each(result.messages, function(idx) {
            var chatBubble;
            if(this.sent_by == 'self') {
                chatBubble = $('<div class="row bubble-sent pull-right">' + this.message + '</div><div class="clearfix"></div>');         // If message was sent by you, format a chat bubble so
            } else {
                chatBubble = $('<div class="row bubble-recv">' + this.message + '</div><div class="clearfix"></div>');                    // Else the message was sent by another, and format it differently
            }
            $('#chatPanel').append(chatBubble);    // append the chat bubble to the UI element
        });
        setTimeout(pollServer, 5000);  // JavaScript timeout here delays execution for a determined amount of time (here, 5 seconds)
    });
    }

    $(document).on('ready', function() {   // This is a handler attached to the ready event to trigger the polling process
        pollServer();
        
        $('button').click(function() {           // This is a handler added to the click event of every button. It will toggle the active class on or off
            $(this).toggleClass('active');
        });
    });


    $('#sendMessageBtn').on('click', function(event) {          // This block is responsible for sending messages to chat.php via POST method 
        event.preventDefault();
        var message = $('#chatMessage').val();
        $.post('chat.php', {
            'message' : message
        }, function(result) {
            $('#sendMessageBtn').toggleClass('active');
        if(!result.success) {
            alert("There was an error sending your message");
        } else {
            console.log("Message sent!");
            $('#chatMessage').val('');
        }
    });
});