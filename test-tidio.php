<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tidio Test Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #667eea; }
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        .code-block {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="test-box">
        <h1>🤖 Tidio Chatbot Test Page</h1>

        <div class="status">
            <strong>Status Check:</strong>
            <p>Look in the bottom-right corner of this page. Do you see a chat bubble?</p>
            <ul>
                <li>✅ <strong>YES</strong> - Tidio is working! The chatbot is live.</li>
                <li>❌ <strong>NO</strong> - This is likely because:
                    <ul>
                        <li>You're on localhost (Tidio may block localhost domains)</li>
                        <li>The code needs to be deployed to your live site</li>
                    </ul>
                </li>
            </ul>
        </div>

        <h2>Installation Details</h2>
        <p><strong>Your Tidio Code:</strong></p>
        <div class="code-block">
            scdn5ijwydeixzd5ygevrtbbjpi3fre1
        </div>

        <p><strong>Script Location:</strong></p>
        <div class="code-block">
            /var/www/html/izendestudioweb/assets/includes/tidio-widget.php
        </div>

        <h2>Next Steps</h2>
        <ol>
            <li><strong>If you see the chat bubble:</strong> Test it! Click it and send a message.</li>
            <li><strong>If you DON'T see the chat bubble:</strong>
                <ul>
                    <li>Deploy this to your live site (izendestudioweb.com)</li>
                    <li>Go to your Tidio dashboard and add your domain</li>
                    <li>The widget will work on the live domain</li>
                </ul>
            </li>
        </ol>

        <h2>Domain Configuration</h2>
        <p>In your Tidio dashboard, make sure your domain is configured:</p>
        <ol>
            <li>Go to Settings → Channels → Live Chat</li>
            <li>Under "Installation" check the allowed domains</li>
            <li>Add: <code>izendestudioweb.com</code> and <code>www.izendestudioweb.com</code></li>
        </ol>

        <div class="status" style="background: #fff3cd; border-left-color: #ffc107;">
            <strong>⚠️ Localhost Limitation:</strong>
            <p>Tidio typically doesn't work on localhost for security reasons. You'll need to test this on your actual domain.</p>
        </div>
    </div>

    <!-- Include Tidio Widget -->
    <?php include __DIR__ . '/assets/includes/tidio-widget.php'; ?>

    <script>
        // Check if Tidio loaded
        setTimeout(function() {
            if (typeof window.tidioChatApi !== 'undefined') {
                console.log('✅ Tidio loaded successfully!');
                alert('✅ Tidio is loaded! Check the bottom-right corner for the chat widget.');
            } else {
                console.log('❌ Tidio did not load. This is expected on localhost.');
                alert('❌ Tidio did not load. This is normal on localhost. Deploy to your live site to test.');
            }
        }, 3000);
    </script>
</body>
</html>
