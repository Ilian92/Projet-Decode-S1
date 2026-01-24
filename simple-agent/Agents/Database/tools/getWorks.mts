import { tool } from "@langchain/core/tools";
import { z } from "zod";
import { API_BASE_URL } from "./config.mjs";

export const getWorks = tool(
  async ({}) => {
    try {
      const url = `${API_BASE_URL}/database/get/works`;
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
      "Récupère toutes les œuvres littéraires de la base de données mississippi.com. Retourne un tableau contenant pour chaque œuvre : id, title, summary, genres associés (id, label), auteurs (id, firstName, lastName) et livres (id, publicationDate, currentUnitPrice, availableStock).",
    schema: z.object({}),
  },
);
