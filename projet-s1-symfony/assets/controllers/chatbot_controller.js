import { Controller } from "@hotwired/stimulus";

// TODO Modifier le design temporaire

export default class extends Controller {
    static targets = ["messages", "input", "button"];

    connect() {
        this.threadId = null;
    }

    async sendMessage(event) {
        event.preventDefault(); // Empêche le rechargement de la page

        console.log("sendMessage appelé");

        const message = this.inputTarget.value.trim();
        if (!message) return;

        console.log("Message à envoyer:", message);

        // Ajouter le message de l'utilisateur à li'nterface
        this.addMessage(message, "user");
        this.inputTarget.value = "";

        // Désactiver le bouton pendant l'envoi
        const originalButtonContent = this.buttonTarget.innerHTML;
        this.buttonTarget.disabled = true;
        this.buttonTarget.innerHTML = "<span>Envoi...</span>";

        try {
            console.log("Envoi de la requête à /api/chatbot/message");
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

            console.log("Réponse reçue:", response.status);
            const data = await response.json();
            console.log("Données reçues:", data);

            if (data.content) {
                // Format direct de l'agent IA
                this.threadId = data.thread_id;
                this.addMessage(data.content, "bot");
            } else if (data.error) {
                // Format d'erreur
                this.addMessage("Erreur: " + data.error, "error");
            } else {
                this.addMessage("Erreur: Réponse inattendue", "error");
            }
        } catch (error) {
            console.error("Erreur:", error);
            this.addMessage("Erreur de connexion au chatbot", "error");
        } finally {
            this.buttonTarget.disabled = false;
            this.buttonTarget.innerHTML = originalButtonContent;
        }
    }

    addMessage(content, type) {
        const messageDiv = document.createElement("div");

        // Classes Tailwind selon le type
        if (type === "user") {
            messageDiv.className = "flex flex-col max-w-[80%] self-end";
        } else {
            messageDiv.className = "flex flex-col max-w-[80%] self-start";
        }

        const contentDiv = document.createElement("div");
        contentDiv.textContent = content;

        // Classes Tailwind pour le contenu
        if (type === "user") {
            contentDiv.className =
                "py-3 px-4 rounded-2xl text-sm leading-6 break-words bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-sm rounded-br-sm";
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

        // Classes Tailwind pour le timestamp
        if (type === "user") {
            timeDiv.className = "text-xs text-gray-400 mt-1.5 px-2 text-right";
        } else {
            timeDiv.className = "text-xs text-gray-400 mt-1.5 px-2";
        }

        messageDiv.appendChild(contentDiv);
        messageDiv.appendChild(timeDiv);

        this.messagesTarget.appendChild(messageDiv);

        // Scroll automatique vers le bas
        this.messagesTarget.scrollTop = this.messagesTarget.scrollHeight;
    }

    handleKeydown(event) {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            this.sendMessage(event);
        }
    }
}
