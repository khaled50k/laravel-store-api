<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusher Test</title>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Enable Pusher logging for debugging
        Pusher.logToConsole = true;

        // Initialize Pusher
        const pusher = new Pusher('e41fb2b49c009e8f7371', {
            cluster: 'us3',
            forceTLS: true,
        });

        // Subscribe to the test channel
        const channel = pusher.subscribe('payments');

        // Listen for the event
        channel.bind('NewOrderPaid', function(data) {
            alert(`Received: ${data.message}`);
            console.log('Received event:', data);

            // Update the page
            document.getElementById('messages').innerHTML = `
                <p><strong>Message:</strong> ${data.message}</p>
            `;
        });
    </script>
</head>
<body>
    <h1>Pusher Test</h1>
    <p>Listening on <code>test-channel</code>...</p>

    <!-- Display received messages -->
    <div id="messages" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;"></div>
</body>
</html>
