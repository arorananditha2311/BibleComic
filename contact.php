<?php
// contact.php
header('Content-Type: text/plain; charset=UTF-8');

// ---------- CONFIG: Update these ----------
$dbHost = 'sql307.infinityfree.com';
$dbUser = 'if0_39849714';
$dbPass = 'nandu2311';            // <-- your DB password
$dbName = 'if0_39849714_bible_comic'; // <-- your DB name
$recipientEmail = 'your_email@example.com'; // optional: for admin notification
// -----------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "❌ Invalid request method.";
    exit;
}

// get & sanitize
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// server-side validation
if ($name === '' || $email === '' || $message === '') {
    echo "⚠️ All fields are required.";
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "⚠️ Invalid email address.";
    exit;
}

// connect
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_errno) {
    http_response_code(500);
    echo "❌ Database connection failed: " . $conn->connect_error;
    exit;
}

// insert safely
$stmt = $conn->prepare("INSERT INTO contact_messages (`name`, `email`, `message`) VALUES (?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo "❌ DB error: " . $conn->error;
    $conn->close();
    exit;
}
$stmt->bind_param('sss', $name, $email, $message);
if ($stmt->execute()) {
    // optional: send admin email notification (comment out if not needed)
    /*
    $subject = "New contact from $name";
    $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
    @mail($recipientEmail, $subject, $body, "From: $email");
    */
    echo "✅ Thank you $name! Your message has been saved.";
} else {
    http_response_code(500);
    echo "❌ Could not save message. Try again later.";
}
$stmt->close();
$conn->close();
?>
