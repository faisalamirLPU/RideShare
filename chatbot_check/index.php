<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>RideShare Assistant</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex flex-col items-center justify-center min-h-screen p-4">
  <div class="w-full max-w-xl bg-gray-800 p-6 rounded-xl shadow-xl">
    <h2 class="text-2xl font-bold mb-4 text-yellow-400">💬 RideShare Assistant</h2>
    <div id="chatbox" class="h-96 overflow-y-auto bg-gray-700 p-4 rounded-md mb-4 space-y-3">
      <!-- Chat messages go here -->
    </div>
    <form id="chatForm" class="flex gap-2">
      <input type="text" id="message" placeholder="Ask something..." class="flex-1 p-3 rounded bg-gray-600 text-white" required>
      <button type="submit" class="bg-yellow-400 text-black px-4 py-2 rounded">Send</button>
    </form>
  </div>

  <script>
    const form = document.getElementById("chatForm");
    const messageInput = document.getElementById("message");
    const chatbox = document.getElementById("chatbox");

    form.onsubmit = async (e) => {
      e.preventDefault();
      const userMsg = messageInput.value.trim();
      if (!userMsg) return;

      // Display user message
      chatbox.innerHTML += `<div><strong>You:</strong> ${userMsg}</div>`;
      messageInput.value = "";

      // Send to server
      const res = await fetch("chatbot.php", {
        method: "POST",
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ message: userMsg })
      });

      const data = await res.json();
      chatbox.innerHTML += `<div><strong>Bot:</strong> ${data.response}</div>`;
      chatbox.scrollTop = chatbox.scrollHeight;
    };
  </script>
</body>
</html>
