<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Messages - Daily Bible Comic</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-yellow-400 via-red-500 to-blue-600 text-black border-black">

<header class="top-0 bg-red-600 text-white shadow-2xl border-b-4 border-black py-6 text-center font-extrabold text-3xl tracking-widest animate-pulse">
📬 Messages & Replies
</header>

<section class="max-w-6xl mx-auto py-12 px-6">

<div class="text-center mb-10">
<a href="index.html" class="inline-block bg-gradient-to-r from-yellow-300 via-pink-300 to-purple-400 hover:from-yellow-400 hover:via-pink-400 hover:to-purple-500 text-black font-bold px-6 py-3 rounded-2xl border-4 border-black shadow-lg hover:scale-105 transition transform">
🏠 Back to Main Page
</a>
</div>

<div id="messages-container">
<?php
$conn = new mysqli("sql307.infinityfree.com", "if0_39849714", "nandu2311", "if0_39849714_bible_comic");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch messages
$result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<div class="bg-yellow-100 text-black border-4 border-black rounded-3xl p-6 mb-8 shadow-lg" id="msg-'.$row['id'].'">';
        echo '<p class="font-bold text-xl mb-2">📌 '.htmlspecialchars($row['name']).' ('.htmlspecialchars($row['email']).')</p>';
        echo '<p class="mb-4">'.nl2br(htmlspecialchars($row['message'])).'</p>';

        // Fetch replies
        $replyQuery = $conn->query("SELECT * FROM contact_replies WHERE msg_id=".$row['id']." ORDER BY created_at ASC");
        if($replyQuery->num_rows > 0) {
            while($r = $replyQuery->fetch_assoc()) {
$label = $r['is_admin'] ? 'Admin' : htmlspecialchars($r['name'])." (".htmlspecialchars($r['email']).")";
echo '<div class="p-3 bg-green-200 rounded-xl border-2 border-black mb-2">';
echo "<strong>$label:</strong> " . nl2br(htmlspecialchars($r['reply']));
echo '</div>';

            }
        }

        // Reply form
echo '<form class="reply-form mt-3 flex flex-col gap-2 bg-white p-4 rounded-xl border-2 border-black text-black" data-msgid="'.$row['id'].'">';
echo '<div>';
echo '<input type="text" name="name" placeholder="Your name" class="w-full px-3 py-2 border-2 border-black rounded-xl text-black" >';
echo '<p class="error text-red-600 text-sm mt-1 hidden"></p>';
echo '</div>';
echo '<div>';
echo '<input type="email" name="email" placeholder="Your email" class="w-full px-3 py-2 border-2 border-black rounded-xl text-black" >';
echo '<p class="error text-red-600 text-sm mt-1 hidden"></p>';
echo '</div>';
echo '<div>';
echo '<textarea name="reply" placeholder="Write your reply..." rows="2" class="w-full px-3 py-2 border-2 border-black rounded-xl text-black" ></textarea>';
echo '<p class="error text-red-600 text-sm mt-1 hidden"></p>';
echo '</div>';
echo '<button type="submit" class="w-full bg-yellow-400 text-black font-bold py-2 rounded-xl border-2 border-black hover:bg-yellow-500">Reply</button>';
echo '</form>';


        echo '</div>';
    }
} else {
    echo '<p class="text-center text-red-700 font-bold">No messages found.</p>';
}

$conn->close();
?>
</div>

<script>
document.querySelectorAll('.reply-form').forEach(form => {
    let nameInput = form.querySelector('input[name="name"]');
    let emailInput = form.querySelector('input[name="email"]');
    let replyInput = form.querySelector('textarea[name="reply"]');

    // Validation function
    function validateField(input, type) {
        let value = input.value.trim();
        let error = input.nextElementSibling;
        let valid = true;

        if (type === "name") {
            if (!value) {
                error.textContent = "⚠️ Name is required.";
                error.classList.remove("hidden");
                input.classList.add("border-red-500");
                valid = false;
            } else {
                error.classList.add("hidden");
                input.classList.remove("border-red-500");
            }
        }

        if (type === "email") {
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!value) {
                error.textContent = "⚠️ Email is required.";
                error.classList.remove("hidden");
                input.classList.add("border-red-500");
                valid = false;
            } else if (!emailPattern.test(value)) {
                error.textContent = "⚠️ Please enter a valid email.";
                error.classList.remove("hidden");
                input.classList.add("border-red-500");
                valid = false;
            } else {
                error.classList.add("hidden");
                input.classList.remove("border-red-500");
            }
        }

        if (type === "reply") {
            if (!value) {
                error.textContent = "⚠️ Reply cannot be empty.";
                error.classList.remove("hidden");
                input.classList.add("border-red-500");
                valid = false;
            } else {
                error.classList.add("hidden");
                input.classList.remove("border-red-500");
            }
        }

        return valid;
    }

    // Real-time validation
    nameInput.addEventListener("input", () => validateField(nameInput, "name"));
    emailInput.addEventListener("input", () => validateField(emailInput, "email"));
    replyInput.addEventListener("input", () => validateField(replyInput, "reply"));

    // Submit handler
    form.addEventListener("submit", function(e){
        e.preventDefault();

        let validName = validateField(nameInput, "name");
        let validEmail = validateField(emailInput, "email");
        let validReply = validateField(replyInput, "reply");

        if (!(validName && validEmail && validReply)) return;

        let msgId = this.dataset.msgid;
        let name = nameInput.value.trim();
        let email = emailInput.value.trim();
        let reply = replyInput.value.trim();

        fetch("reply_handler.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `msg_id=${encodeURIComponent(msgId)}&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&reply=${encodeURIComponent(reply)}`
        })
        .then(res => res.json())
        .then(res => {
            if(res.success){
                let container = document.getElementById("msg-"+msgId);
                let div = document.createElement("div");
                div.className = "p-3 bg-green-200 rounded-xl border-2 border-black mb-2";
                div.innerHTML = `<strong>${res.label}:</strong> ${res.reply}`;
                container.appendChild(div);

                // Clear form
                nameInput.value = "";
                emailInput.value = "";
                replyInput.value = "";
            } else {
                alert("❌ Failed to submit reply.");
            }
        });
    });
});

</script>

</section>
</body>
</html>
