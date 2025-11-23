<!DOCTYPE html>
<html>
<head>
    <title>Test Notification Endpoint</title>
</head>
<body>
    <h1>Test Notification Endpoint</h1>
    <button onclick="testNotification()">Send Test Notification</button>
    <div id="result"></div>

    <script>
        const csrfToken = '{{ csrf_token() }}';

        function testNotification() {
            console.log('Sending test notification...');
            document.getElementById('result').innerHTML = 'Sending...';

            fetch('/admin/test-notification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    notification_type: 'email'
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                document.getElementById('result').innerHTML = 
                    '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('result').innerHTML = 
                    '<pre style="color: red;">Error: ' + error.message + '</pre>';
            });
        }
    </script>
</body>
</html>
