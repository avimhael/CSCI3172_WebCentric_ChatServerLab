<?php # sets PHP

// This code has been borrowed from the book: 'PHP and MySQL Web Development' 5th Edition by Luke Welling and Laura Thomson
// Chapter 23 'Integrating JavaScript and PHP' pg 505-507
// Given permission to use by Professor Pat Crysdale for Dalhousie University course Web-Centric Computing CSCI 3172.03 Autumn 2020
// For use in Laboratory Exercise 12 submission. Instructions are to comment every line and understand the code provided.
// The program below does two things: (1) accepts messages to send and (2) returns a list of messages that have not been seen by the user
// This is an AJAX application and the PHP will function using JSON for output. A table will also need to be created in MySQL for
// persistent storage of messages. This CHATLOG  table will have four columns - Unique ID, PHP Session ID, The Message, Timestamp. 
//
// Lab completed by Matt Ward B00671544

session_start();  # enable sessions
ob_start();       # enable output buffing
header("Content-type: application/json"); # set response header to ensure the client knows JSON will come as the response

date_default_timezone_set('UTC');         # match timezone for the server - will make sure timestamps of chat messages will be the same

# connect to database
$db_hostname = "localhost";
$db_username = "root";
$db_password = "root";
$db_database = "chat";
$db_port = '3307';

$db = mysqli_connect($db_hostname, $db_username, $db_password, $db_database, $db_port);              # open database connection to MySQL

if (mysqli_connect_errno()) {                                                         # if there is an error in connecting to MySQL
    echo '<p>Error: Could not connect to database.<br/> Please try again later.</p>'; # print out a message saying it could not connect and the user should try again later
    exit;                                                                             # exit the program
}

try {                                   # try statement - the program will try the following lines 
    $currentTime = time();              # set current time into $currentTime variable
    $session_id = session_id();         # set current session_id() into $session_id variable.

    $lastPoll = isset($_SESSION['last_poll']) ? $_SESSION['last_poll'] : $currentTime;                          # This ternary is telling us that the $lastpoll variable will be set to the value of $_SESSION['last_poll'] if isset($_SESSION['last_poll']) is TRUE and $currentTime if 
                                                                                                                # isset($_SESSION['last_poll']) is FALSE. This is telling us that if there hasn't been a previous poll, simply use the current time. If there has been, set it to $lastPoll
                                                                                                                # $_SESSION contains session variables available to the current script. 
    $action = isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST') ? 'send' : 'poll';    # The ternary here will resolve to 'send' if ($_SERVER['REQUEST_METHOD'] == 'POST') is TRUE and 'poll' if ($_SERVER['REQUEST_METHOD'] == 'POST') is FALSE. $action variable will then be set. 
                                                                                                        # This is telling us that if the server request method is set AND it isn't POST (probably a GET), use the 'poll' case below. if the server request method is set AND it is POST, use 'send' case below. 
                                                                                                                # $_SERVER contains server and execution environment information, and REQUEST_METHOD is an element of it that can get set to POST, GET, etc
    switch($action) {        # switch statement depending on the $action variable set above. The two conditions here are 'poll' or 'send'. 

        case 'poll':         # If the $action variable is set to 'poll', use the code block below. This will retrieve a list of messages that have yet to be seen by the user and show them on the screen

            $query = "SELECT * FROM chatlog WHERE date_created >= ?";      # select all from the chatlog table created where the date they were created on is. There is a ? where a value should be as it will be brought in later.
            $stmt = $db->prepare($query);                                  # Set the created query into a prepared statement $stmt. The statement template will be sent to the db. The db will then store the result without executing it. 
            $stmt->bind_param('s', $lastPoll);                             # The bind_param function binds the parameters to the query and tells the db what they are - in this case the singular 's' stands for one string parameter which will be the date $lastPoll.
            $stmt->execute();                                              # execute() in PHP returns a boolean. This will be TRUE or FALSE depending on if the pass of information to the db worked or not.
            $stmt->bind_result($id, $message, $session_id, $date_created); # The bind_result function will bind the $id, $message, $session_id, and $date_created of a message to the prepared statement for storage of the poll.
            $result = $stmt->get_result();                                 # The $result variable will hold the value of the query results based on the get_result function.
            $newChats = [];                                                # create an array entitled $newChats , which will hold any new messages since the last poll.
            while($chat = $result->fetch_assoc()) {                        # fetch_assoc() function is used to return a result row as an associative array. While there are results rows of chat messages, execute the follow code.
                if($session_id == $chat['sent_by']) {                      # If the PHP session_ID is equal to the value under the 'sent by' column in the db table, then ...
                    $chat['sent_by'] = 'self';                             # Label these chats as being from yourself.     
                } else {                                                    
                    $chat['sent_by'] = 'other';                            # Else, the chats are not from you and from another person. Label them as being from 'other'.
                }
                $newChats[] = $chat;                                       # Store all these newly labeled processed chats into the newChats array
            }

            $_SESSION['last_poll'] = $currentTime;
            print json_encode([               # Return a JSON object with two keys - success and messages. Remember we set the response header above to expect a JSON 
                'success' => true,            # A Boolean TRUE or FALSE key depending on the success of the operation. If FALSE, proceed to catch statement to show an error message
                'messages' => $newChats       # Since the REQUEST_METHOD is GET and not POST, the client should render the messages inside newChats for the user to view
            ]);
            exit;                             # output a message and exit the current script

        case 'send':        # If the $action variable is set to 'send', use the code block below. This will accept a new message to be broadcast to all other users.

            $message = isset($_POST['message']) ? $_POST['message'] : '';  
            # This ternary is telling us that the $message variable will be set to $_POST['message'] if isset($_POST['message']) is TRUE and '' if it is FALSE. It is saying that it should send a message since there is one to send. If there is nothing to send, do nothing.
            $message = strip_tags($message);                               # Take the message to be posted and run strip_tags() function on it. This will strip the message of HTML, XML, and PHP tags.
            
            $query = "INSERT INTO chatlog (message, sent_by, date_created) VALUES(?, ?, ?)";         # Now that the message to send is sanitized, prepare a query to be sent to the db. The ?'s are placeholders for variables.
            $stmt = $db->prepare($query);                                                            # Same as line 42 above - see that comment.         
            $stmt->bind_param('ssi', $message, $session_id, $currentTime);  # The bind_param function binds the parameters to the query and tells the db what they are - in this case the 'ssi' tells us there will be three parameters - two strings (#message, #session_id) and one integer (#currentTime)
            $stmt->execute();                                                                        # execute() in PHP returns a boolean. This will be TRUE or FALSE depending on if the pass of information to the db worked or not.
            print json_encode(['success' => true]);                                                  # Return a JSON object with one keys - success. Remember we set the response header above to expect a JSON. A Boolean TRUE or FALSE key depending on the success of the operation.
            exit;                                                                                    # output a message and exit the current script

    }

} catch(\Exception $e) {                # catch statement - if the program cannot successfully execute the try block above, it will execute the following code
    print json_encode([                 # Return a JSON object with two keys - success and error. Remember we set the response header above to expect a JSON
        'success' => false,             # If we are in this block, our 'success' key in either case ended up as FALSE
        'error' => $e->getMessage()     # Show the error to the user so they can hopefully understand what went wrong
    ]);
}