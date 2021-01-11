# Web-Centric Computing CSCI 3172.03
## Lab Exercise 12
### Matt Ward B00671544
#### Autumn 2020



##### The code for this assignment has been borrowed from the book: 'PHP and MySQL Web Development' 5th Edition by Luke Welling and Laura Thomson Chapter 23 'Integrating JavaScript and PHP'
##### 'Bubbler' CSS tool by John Clifford was used for chat bubble styling. Found at: https://www.ilikepixels.co.uk/bubbler-css-speech-bubble-generator/

##### The program below does two things: (1) accepts messages to send and (2) returns a list of messages that have not been seen by the user. This is an AJAX application and the PHP will function using JSON for output. 

## Instructions for setup:

##### Download and configure/set up MAMP local server environment. 
##### Within phpMyAdmin, create a database named 'chat' and a table/columns with the following parameters:
'
CREATE DATABASE chat;
USE chat;
CREATE TABLE chatlog (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    message TEXT,
    sent_by VARCHAR(50),
    date_created INT(11)
);
'
##### Modify lines 20-24 of chat.php to suit your own environment