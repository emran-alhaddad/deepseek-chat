<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brackets DeepSeek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x/dist/cdn.min.js"></script>
    <style>
        .loading-spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #ffffff;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-gray-900 text-white h-screen flex flex-col justify-center items-center">
    <div class="w-full max-w-2xl bg-gray-800 shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-center mb-4">Brackets DeepSeek</h1>

        <!-- CSRF Token (Hidden) -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Chat Container -->
        <div id="chat-box" class="h-96 overflow-y-auto border p-4 bg-gray-700 rounded space-y-3">
            <div class="text-gray-400 text-sm text-center">Start a conversation...</div>
        </div>

        <!-- Input Box -->
        <div class="mt-4 flex items-center">
            <input type="text" id="user-input" class="w-full p-2 border bg-gray-600 text-white rounded-l focus:outline-none" placeholder="Type a message..." x-data @keydown.enter="sendMessage()">
            <button id="send-btn" onclick="sendMessage()" class="bg-blue-500 text-white px-4 py-2 rounded-r flex items-center justify-center">
                <span id="send-text">Send</span>
                <div id="loading-spinner" class="loading-spinner hidden ml-2"></div>
            </button>
        </div>
    </div>

    <script>
        function sendMessage() {
            let inputField = document.getElementById("user-input");
            let message = inputField.value.trim();
            if (!message) return;

            let chatBox = document.getElementById("chat-box");
            let sendBtn = document.getElementById("send-btn");
            let sendText = document.getElementById("send-text");
            let loadingSpinner = document.getElementById("loading-spinner");

            // Show loading spinner & disable button
            sendText.style.display = "none";
            loadingSpinner.classList.remove("hidden");
            sendBtn.disabled = true;

            // Append User Message
            chatBox.innerHTML += `
        <div class="flex justify-end items-center">
            <div class="bg-blue-500 text-white px-4 py-2 rounded-lg max-w-xs text-right">
                ${message}
            </div>
            <img src="/user.jpeg" class="w-8 h-8 rounded-full ml-2">
        </div>
    `;

            inputField.value = "";
            chatBox.scrollTop = chatBox.scrollHeight;

            // Append an Empty AI Response Box for Streaming
            let aiMessageDiv = document.createElement("div");
            aiMessageDiv.classList.add("flex", "items-center");
            aiMessageDiv.innerHTML = `
            <img src="/deepseek.png" class="w-8 h-8 rounded-full mr-2">
        <div class="bg-gray-600 text-white px-4 py-2 rounded-lg max-w-xs">
            <span id='typing-${Date.now()}'></span>
        </div>
    `;
            chatBox.appendChild(aiMessageDiv);
            chatBox.scrollTop = chatBox.scrollHeight;

            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
            let typingElement = aiMessageDiv.querySelector("span");

            // Streaming Response from Server using Fetch
            fetch("/stream-chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({
                        message
                    })
                })
                .then(response => {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();

                    function readStream() {
                        reader.read().then(({
                            done,
                            value
                        }) => {
                            if (done) {
                                sendText.style.display = "inline";
                                loadingSpinner.classList.add("hidden");
                                sendBtn.disabled = false;
                                return;
                            }

                            // Decode the received chunk
                            let decodedChunk = decoder.decode(value);
                            let lines = decodedChunk.split("\n");
                            lines.forEach(line => {
                                if (line.startsWith("data: ")) {
                                    let text = line.replace("data: ", "").trim();
                                    typingElement.innerHTML += text + " ";
                                }
                            });

                            chatBox.scrollTop = chatBox.scrollHeight;
                            readStream();
                        });
                    }
                    readStream();
                })
                .catch(error => {
                    console.error("Error:", error);
                    sendText.style.display = "inline";
                    loadingSpinner.classList.add("hidden");
                    sendBtn.disabled = false;
                });
        }
    </script>
</body>

</html>