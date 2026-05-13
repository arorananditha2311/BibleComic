<?php
$conn = new mysqli("sql307.infinityfree.com", "if0_39849714", "nandu2311", "if0_39849714_bible_comic");
if ($conn->connect_error) die(json_encode(['success'=>false]));

$msg_id = intval($_POST['msg_id']);
$email = $conn->real_escape_string($_POST['email']);
$reply = $conn->real_escape_string($_POST['reply']);
$name = $conn->real_escape_string($_POST['name'] ?? $email); // if you want, normal users can provide name

// Admin check (replace with your admin email)
$is_admin = strtolower($email) === 'onlyworshipvoice@gmail.com' ? 1 : 0;

$sql = "INSERT INTO contact_replies (msg_id, name, email, reply, is_admin, created_at) VALUES ($msg_id, '$name', '$email', '$reply', $is_admin, NOW())";

if($conn->query($sql)){
    $label = $is_admin ? 'Admin' : htmlspecialchars($name);
    echo json_encode(['success'=>true, 'label'=>$label, 'reply'=>$reply]);
}else{
    echo json_encode(['success'=>false]);
}
$conn->close();
?>
