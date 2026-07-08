<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Download App</title>
</head>
<body>

<script>
// Function to detect if the user is using an Android device
function isAndroid() {
    return /Android/i.test(navigator.userAgent);
}

// Function to create the appropriate download link based on the device
function createDownloadLink() {
    if (isAndroid()) {
        // If user is on Android, provide Play Store link
        window.location.href = "https://play.google.com/store/apps/details?id=com.buzzify";
    } else {
        // Otherwise, provide App Store link
        window.location.href = "https://apps.apple.com/il/app/buzzify/id6478440886";
    }
}

// Call the function when the page loads
createDownloadLink();
</script>

</body>
</html>
