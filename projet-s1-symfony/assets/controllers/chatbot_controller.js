import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["messages", "input", "button"];

    connect() {
        this.threadId = null;
    }

    async sendMessage(event) {
        event.preventDefault();

        const message = this.inputTarget.value.trim();
        if (!message) return;

        this.addMessage(message, "user");
        this.inputTarget.value = "";

        const originalButtonContent = this.buttonTarget.innerHTML;
        this.buttonTarget.disabled = true;
        this.buttonTarget.innerHTML = "<span>Envoi...</span>";

        try {
            const response = await fetch("/api/chatbot/message", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    message: message,
                    thread_id: this.threadId,
                }),
            });

            if (!response.ok) {
                this.addMessage(`Erreur serveur: ${response.status}`, "error");
                return;
            }

            const messageDiv = this.createBotMessageContainer();
            const contentDiv = messageDiv.querySelector(".message-content");
            const timeDiv = messageDiv.querySelector(".message-time");

            this.messagesTarget.appendChild(messageDiv);

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = "";
            let currentEvent = null;
            let firstToken = true;

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                const chunk = decoder.decode(value, { stream: true });
                buffer += chunk;

                const lines = buffer.split("\n");
                buffer = lines.pop() || "";

                for (const line of lines) {
                    const trimmedLine = line.trim();

                    if (trimmedLine === "") {
                        continue;
                    }

                    if (line.startsWith("event:")) {
                        currentEvent = line.substring(6).trim();
                    } else if (line.startsWith("data:")) {
                        const currentData = line.substring(5).trim();

                        if (currentEvent === "stream_token" && currentData) {
                            try {
                                const jsonData = JSON.parse(currentData);
                                if (jsonData.token) {
                                    if (firstToken) {
                                        contentDiv.textContent = "";
                                        firstToken = false;
                                    }
                                    contentDiv.textContent += jsonData.token;
                                }
                            } catch (e) {
                                console.error(
                                    "Erreur parsing token:",
                                    currentData,
                                    e,
                                );
                            }
                        } else if (
                            currentEvent === "stream_end" &&
                            currentData
                        ) {
                            try {
                                const jsonData = JSON.parse(currentData);
                                if (jsonData.thread_id) {
                                    this.threadId = jsonData.thread_id;
                                }
                            } catch (e) {
                                console.error(
                                    "Erreur parsing stream_end:",
                                    currentData,
                                    e,
                                );
                            }
                        }
                    }
                }

                this.messagesTarget.scrollTop =
                    this.messagesTarget.scrollHeight;
            }

            timeDiv.textContent = new Date().toLocaleTimeString("fr-FR", {
                hour: "2-digit",
                minute: "2-digit",
            });
        } catch (error) {
            console.error("Erreur:", error);
            this.addMessage(
                "Erreur de connexion au chatbot: " + error.message,
                "error",
            );
        } finally {
            this.buttonTarget.disabled = false;
            this.buttonTarget.innerHTML = originalButtonContent;
        }
    }

    createBotMessageContainer() {
        const messageDiv = document.createElement("div");
        messageDiv.className = "flex flex-col max-w-[80%] self-start";

        const contentDiv = document.createElement("div");
        contentDiv.className =
            "message-content py-3 px-4 rounded-2xl text-sm leading-6 break-words bg-white text-gray-800 border border-gray-200 shadow-sm rounded-bl-sm";

        contentDiv.innerHTML = `
            <div class="flex items-center gap-2">
                <div class="flex gap-1">
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                </div>
                <span class="text-gray-500 text-xs">L'assistant réfléchit...</span>
            </div>
        `;

        const timeDiv = document.createElement("div");
        timeDiv.className = "message-time text-xs text-gray-400 mt-1.5 px-2";
        timeDiv.textContent = new Date().toLocaleTimeString("fr-FR", {
            hour: "2-digit",
            minute: "2-digit",
        });

        messageDiv.appendChild(contentDiv);
        messageDiv.appendChild(timeDiv);

        return messageDiv;
    }

    addMessage(content, type) {
        const messageDiv = document.createElement("div");

        if (type === "user") {
            messageDiv.className = "flex flex-col max-w-[80%] self-end";
        } else {
            messageDiv.className = "flex flex-col max-w-[80%] self-start";
        }

        const contentDiv = document.createElement("div");
        contentDiv.textContent = content;

        if (type === "user") {
            contentDiv.className =
                "py-3 px-4 rounded-2xl text-sm leading-6 break-words bg-contrasted-200 text-gray-800 border border-contrasted-200 shadow-sm rounded-br-sm";
        } else if (type === "bot") {
            contentDiv.className =
                "py-3 px-4 rounded-2xl text-sm leading-6 break-words bg-white text-gray-800 border border-gray-200 shadow-sm rounded-bl-sm";
        } else {
            contentDiv.className =
                "py-3 px-4 rounded-2xl text-sm leading-6 break-words bg-red-100 text-red-800 border border-red-200";
        }

        const timeDiv = document.createElement("div");
        timeDiv.textContent = new Date().toLocaleTimeString("fr-FR", {
            hour: "2-digit",
            minute: "2-digit",
        });

        if (type === "user") {
            timeDiv.className = "text-xs text-gray-400 mt-1.5 px-2 text-right";
        } else {
            timeDiv.className = "text-xs text-gray-400 mt-1.5 px-2";
        }

        messageDiv.appendChild(contentDiv);
        messageDiv.appendChild(timeDiv);

        this.messagesTarget.appendChild(messageDiv);

        this.messagesTarget.scrollTop = this.messagesTarget.scrollHeight;
    }

    handleKeydown(event) {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            this.sendMessage(event);
        }
    }
}
