document.addEventListener("DOMContentLoaded", function() {
    const chatToggle = document.getElementById("chatbot-toggle");
    const chatbot = document.getElementById("chatbot");
    const chatInput = document.getElementById("chat-input");
    const chatMessages = document.getElementById("chat-messages");
    const sendMessage = document.getElementById("send-message");

    chatToggle.addEventListener("click", function() {
        chatbot.style.display = chatbot.style.display === "none" ? "block" : "none";
    });

    sendMessage.addEventListener("click", function() {
        let userMessage = chatInput.value.trim();
        if (userMessage === "") return;

        chatMessages.innerHTML += `<p><strong>You:</strong> ${userMessage}</p>`;
        chatInput.value = "";

        fetch("../chatbot.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `message=${encodeURIComponent(userMessage)}`
        })
        .then(response => response.json())
        .then(data => {
            chatMessages.innerHTML += `<p><strong>Bot:</strong> ${data.response}</p>`;
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    });
});
