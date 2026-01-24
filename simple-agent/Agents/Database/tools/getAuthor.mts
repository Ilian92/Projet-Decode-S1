import { tool } from "@langchain/core/tools";
import { z } from "zod";
import { API_BASE_URL } from "./config.mjs";

export const getAuthor = tool(
  async ({ id }) => {
    try {
      const url = `${API_BASE_URL}/database/get/authors/${id}`;
      const response = await fetch(url, {
        headers: {
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        if (response.status === 404) {
          return `Auteur avec l'ID ${id} non trouvé dans la base de données.`;
        }
        throw new Error(`Erreur API: ${response.status}`);
      }

      const data = await response.json();
      return JSON.stringify(data, null, 2);
    } catch (error) {
      console.error("Erreur base de données:", error);
      return `Impossible de récupérer les informations de l'auteur avec l'ID ${id}. Vérifiez que le serveur est lancé sur le port 8000.`;
    }
  },
  {
    name: "getAuthor",
    description:
      "Récupère les informations d'un auteur spécifique depuis la base de données du site mississippi.com via l'API locale en utilisant son ID",
    schema: z.object({
      id: z.string().describe("L'identifiant de l'auteur à récupérer"),
    }),
  },
);
