<?php
$message = '';
$response_data = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = 'https://buzzify.24livehost.com/api/send-story';
    
    // basic fields
    $data = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'story' => $_POST['story'] ?? '',
        'user_id' => $_POST['user_id'] ?? '',
    ];

    // Handle File Uploads
    if (!empty($_FILES['files']['name'][0])) {
        $count = count($_FILES['files']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['files']['tmp_name'][$i];
                $name = $_FILES['files']['name'][$i];
                $type = $_FILES['files']['type'][$i];
                
                // key must be files[$i] to simulate files[] array in multipart/form-data
                $data["files[$i]"] = new CURLFile($tmp_name, $type, $name);
            }
        }
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Cookie from User Request
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Cookie: lang_code=en'
    ]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $message = '<div class="alert alert-danger">Error: ' . curl_error($ch) . '</div>';
    } else {
        $message = '<div class="alert alert-success">Response Received (HTTP ' . $http_code . ')</div>';
        $response_data = $response;
    }

    curl_close($ch);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test Page</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; line-height: 1.6; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input[type="text"], input[type="email"], textarea { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 100px; }
        button { background: #007bff; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #0056b3; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        pre { background: #f8f9fa; padding: 1rem; border: 1px solid #ddd; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>

    <h1>Send Story API Test</h1>
    
    <?php echo $message; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="dssddfvfcffd" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="sgh@hdf.fjfd" required>
        </div>

        <div class="form-group">
            <label for="user_id">User ID</label>
            <input type="text" id="user_id" name="user_id" value="919" required>
        </div>

        <div class="form-group">
            <label for="story">Story</label>
            <textarea id="story" name="story" required>testdddfdffdf</textarea>
        </div>

        <div class="form-group">
            <label for="files">Files (Select multiple)</label>
            <input type="file" id="files" name="files[]" multiple>
        </div>

        <button type="submit">Send Data</button>
    </form>

    <?php if ($response_data): ?>
        <h2>API Response</h2>
        <pre><?php echo htmlspecialchars(print_r(json_decode($response_data, true) ?? $response_data, true)); ?></pre>
    <?php endif; ?>

</body>
</html>
