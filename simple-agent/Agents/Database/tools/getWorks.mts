import { tool } from "@langchain/core/tools";
import { z } from "zod";

export const getWorks = tool(
  async ({}) => {
    try {
      const url = `http://localhost:8000/database/get/works`;
      const response = await fetch(url, {
        headers: {
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        if (response.status === 404) {
          return `Route non trouvée. Vérifiez que le serveur est lancé.`;
        }
        throw new Error(`Erreur API: ${response.status}`);
      }

      const data = await response.json();
      return JSON.stringify(data, null, 2);
    } catch (error) {
      console.error("Erreur base de données:", error);
      return `Impossible de récupérer les informations des livres. Vérifiez que le serveur est lancé sur le port 8000.`;
    }
  },
  {
    name: "getWorks",
    description:
      "Récupère la liste complète des livres (works) depuis la base de données du site mississippi.com via l'API locale",
    schema: z.object({}),
  },
);
