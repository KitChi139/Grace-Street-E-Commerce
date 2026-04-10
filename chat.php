<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>chat</title>
    <style>
       *{
        padding: 0;
        margin: 0;
        box-sizing: border-box;
       }
       body{
        width: 100%;
        height: 100vh;
       }
       .chat-container{
        position: fixed;
        bottom: 0;
        right: 0;
       }
       .message-img{
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        margin: 40px;
        background-color: #489DEC;
        border-radius: 100px;
       }
       .message-img i{
        font-size: 20px; 
       }

       
       .message-input {
        margin-top: 10px; /* Add some space between message list and input */
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px 0px;
    }

    .message-input input[type="text"] {
        width: calc(100% - 70px); /* Adjust width of input field */
        padding: 11px; /* Add some padding */
        border: 1px solid #ccc; /* Add border */
    }

    .message-input button {
        width: 60px; /* Set width of send button */
        padding: 8px; /* Add some padding */
        border: none; /* Remove border */
        background-color: #007bff; /* Set background color */
        color: #fff; /* Set text color */
        border-radius: 5px; /* Add border-radius */
        cursor: pointer; /* Change cursor to pointer */
    }

    .message-input button:hover {
        background-color: #0056b3; /* Change background color on hover */
    }
    .chat-box {
        width: 350px; /* Adjust as needed */
        background-color: #489DEC; /* Background color of the chat box */
        border-radius: 10px; /* Rounded corners */
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5); /* Shadow effect */
    }

    .message-container {
        padding: 20px; /* Padding around the message box */
    }

    .message-box {
        background-color: #fff; /* Background color of message box */
        border-radius: 10px; /* Rounded corners */
    }

    .message-us {
        background-color: #489DEC; /* Background color of user message */
        color: #fff; /* Text color of user message */
        padding: 10px; /* Padding around user message */
        border-top-left-radius: 10px; /* Rounded corners */
        border-top-right-radius: 10px; /* Rounded corners */
        display: flex; /* Flex container */
        justify-content: space-between; /* Align items evenly */
    }

    .message-us h1 {
        margin: 0; /* Remove default margin */
        font-size: 18px; /* Adjust font size */
    }

    .message-list {
        padding: 20px; /* Padding around message list */
        height: 300px; /* Maximum height of message list */
        overflow-y: auto; /* Enable vertical scrolling */
    }

    .fa-circle-xmark {
        font-size: 20px; /* Adjust font size */
        cursor: pointer; /* Change cursor to pointer */
    }
    .send button{
        background-color: #489DEC;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 43px;
        width: 43px;
        cursor: pointer;
    }
    .container-box{
        z-index: 100;
        position: fixed;
        bottom: 100px;
        right: 100px;
        display: none; /* Initially hide */
    }
    .message-list {
        padding: 20px;
        height: 300px;
        overflow-y: auto;
    }

    .message-item {
        max-width: 70%;
        margin-bottom: 10px;
        padding: 10px;
        border-radius: 10px;
        word-wrap: break-word;
    }

    .message-item.sent {
        background-color: #489DEC;
        color: white;
        align-self: flex-end;
    }

    .message-item.received {
        background-color: #f0f0f0;
        color: #333;
    }

    .message-item small {
        color: #999;
        font-size: 12px;
    }

    .alert-info {
        background-color: #f0f0f0;
        color: #333;
        padding: 10px;
        border-radius: 10px;
    }
    </style>
</head>
<body>
    <div class="chat-container" id="chatContainer">
        <div class="message-img" id="toggleChatBox">
            <i class="fa-solid fa-message" style="color: #ffffff;"></i>
        </div>
    </div>


<div class="container-box" id="containerBox">
<div class="chat-box">
    <div class="message-container">
        <div class="message-box">
            <div class="message-us">
                <h1>MESSAGE US</h1>
                <i class="fa-solid fa-circle-xmark" style="color: #ffffff;" id="closeChatBox"></i>
            </div>
            <div class="message-list">
                <?php if (!empty($chats)) { ?>
                    <?php foreach($chats as $chat) { ?>
                        <?php if ($chat['from_id'] == $_SESSION['user_id']) { ?>
                            <div class="message-item sent">
                                <?= $chat['message'] ?>
                                <small><?= date('M d, Y H:i', strtotime($chat['created_at'])) ?></small>
                            </div>
                        <?php } else { ?>
                            <div class="message-item received">
                                <?= $chat['message'] ?>
                                <small><?= date('M d, Y H:i', strtotime($chat['created_at'])) ?></small>
                            </div>
                        <?php } ?>
                    <?php } ?>
                <?php } else { ?>
                    <div class="alert alert-info text-center">
                        <i class="fa fa-comments d-block fs-big"></i>
                        No messages yet. Start the conversation.
                    </div>
                <?php } ?>
            </div>
            <div class="message-input">
                <input type="text" placeholder="Type your message...">
                <div class="send">
                    <button><i class="fa-solid fa-paper-plane" style="color: #ffffff;"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    const chatContainer = document.getElementById('chatContainer');
    const containerBox = document.getElementById('containerBox');
    const toggleChatBox = document.getElementById('toggleChatBox');
    const closeChatBox = document.getElementById('closeChatBox');
    const messageInput = document.querySelector('.message-input input[type="text"]');
    const sendButton = document.querySelector('.message-input button');
    const messageList = document.querySelector('.message-list');

    toggleChatBox.addEventListener('click', () => {
        containerBox.style.display = 'block';
    });

    closeChatBox.addEventListener('click', () => {
        containerBox.style.display = 'none';
    });

    // Function to send message
    function sendMessage() {
        const message = messageInput.value.trim(); // Trim whitespace from the input
        if (message === '') return; // If the message is empty, do nothing
        // Create a new paragraph element to display the message
        const messageElement = document.createElement('p');
        messageElement.textContent = message; // Set the text content of the paragraph
        messageElement.classList.add('rtext', 'align-self-end', 'border', 'rounded', 'p-2', 'mb-1');
        // Append the message element to the message list
        messageList.appendChild(messageElement);
        // Clear the input field after sending the message
        messageInput.value = '';
        // Scroll to the bottom of the message list to show the latest message
        messageList.scrollTop = messageList.scrollHeight;
        // Here you can send the message to your server using AJAX or fetch API to handle database insertion
        // Example:
        // fetch('your_insert_endpoint.php', {
        //     method: 'POST',
        //     body: JSON.stringify({ message: message }),
        //     headers: {
        //         'Content-Type': 'application/json'
        //     }
        // })
        // .then(response => response.json())
        // .then(data => console.log(data))
        // .catch(error => console.error('Error:', error));
    }

    // Add click event listener to the send button
    sendButton.addEventListener('click', sendMessage);

    // Add keyup event listener to the input field to allow sending message by pressing Enter key
    messageInput.addEventListener('keyup', event => {
        if (event.key === 'Enter') {
            sendMessage();
        }
    });
</script>
</body>
</html>
