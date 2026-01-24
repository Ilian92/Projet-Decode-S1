import { tool } from "@langchain/core/tools";
import { z } from "zod";
import { API_BASE_URL } from "./config.mjs";

export const getWork = tool(
  async ({ id }) => {
    try {
      const url = `${API_BASE_URL}/database/get/works/${id}`;
      const response = await fetch(url, {
        headers: {
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        if (response.status === 404) {
          return `Livre avec l'ID ${id} non trouvé dans la base de données.`;
        }
        throw new Error(`Erreur API: ${response.status}`);
      }

      const data = await response.json();
      return JSON.stringify(data, null, 2);
    } catch (error) {
      console.error("Erreur base de données:", error);
      return `Impossible de récupérer les informations du livre avec l'ID ${id}. Vérifiez que le serveur est lancé sur le port 8000.`;
    }
  },
  {
    name: "getWork",
    description:
      "Récupère une œuvre littéraire spécifique par son ID depuis la base de données mississippi.com. Retourne un objet avec l'id, title, summary, genres, auteurs et livres associés.",
    schema: z.object({
      id: z.string().describe("L'identifiant unique de l'œuvre à récupérer"),
    }),
  },
);
