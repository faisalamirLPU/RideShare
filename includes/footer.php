 <!-- Chatbot -->
 <div class="fixed bottom-6 right-6">
    <div id="chat-box" class="hidden bg-white/90 text-black rounded-xl w-80 h-96 p-4 shadow-xl flex flex-col justify-between">
      <div class="overflow-y-auto h-full space-y-2 text-sm" id="messages">
        <div class="text-gray-600">🤖 Hello! How can I help you today?</div>
      </div>
      <div class="flex mt-2">
        <input id="chat-input" type="text" placeholder="Type your message..." class="flex-1 p-2 rounded-l-lg border border-gray-300">
        <button onclick="sendMessage()" class="bg-yellow-400 px-4 rounded-r-lg">Send</button>
      </div>
    </div>
    <button onclick="toggleChat()" class="bg-yellow-400 text-black p-4 rounded-full shadow-md hover:bg-yellow-500">
      💬 Chat
    </button>
  </div>

  <!-- Footer -->
  <footer class="text-center text-sm text-white py-6 bg-black/60">
    &copy; 2025 RideShare. All rights reserved.
  </footer>

  <!-- <script>
    function toggleChat() {
      const chatBox = document.getElementById('chat-box');
      chatBox.classList.toggle('hidden');
    }

    function sendMessage() {
      const input = document.getElementById('chat-input');
      const messages = document.getElementById('messages');
      if (input.value.trim() !== '') {
        const div = document.createElement('div');
        div.textContent = '🧑‍💻 ' + input.value;
        messages.appendChild(div);
        input.value = '';
        messages.scrollTop = messages.scrollHeight;
      }
    }
  </script> -->
  
  <script>
  function toggleChat() {
    const chatBox = document.getElementById('chat-box');
    chatBox.classList.toggle('hidden');
  }

  function sendMessage() {
    const input = document.getElementById('chat-input');
    const messages = document.getElementById('messages');
    const userMsg = input.value.trim();

    if (userMsg !== '') {
      // Display user message
      const userDiv = document.createElement('div');
      userDiv.className = 'text-right text-blue-600';
      userDiv.textContent = '🧑‍💻 ' + userMsg;
      messages.appendChild(userDiv);

      // Send to PHP backend
      fetch('chatbot.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'message=' + encodeURIComponent(userMsg)
      })
      .then(response => response.text())
      .then(reply => {
        const botDiv = document.createElement('div');
        botDiv.className = 'text-left text-gray-700';
        botDiv.textContent = '🤖 ' + reply;
        messages.appendChild(botDiv);
        messages.scrollTop = messages.scrollHeight;
      });

      input.value = '';
    }
  }

  // Enable Enter key to send message
  document.getElementById("chat-input").addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      sendMessage();
    }
  });
</script>


