import { tool } from "@langchain/core/tools";
import { z } from "zod";

export const getWorks = tool(
  async ({ information }) => {
    try {
      // const url = `https://wttr.in/${encodeURIComponent(information)}?format=j1&lang=fr`;
      const response = await fetch(url, {
        headers: {
          'User-Agent': 'curl/7.68.0'
        }
      });
      
      if (!response.ok) {
        if (response.status === 404) {
          return `Information "${information}" non trouvée. Vérifiez l'orthographe.`;
        }
        throw new Error(`Erreur API: ${response.status}`);
      }
      return await response.json();
      
    } catch (error) {
      console.error('Erreur base de données:', error);
      return `Impossible de récupérer les informations pour "${information}".`;
    }
  },
  {
    name: "getWorks",
    description: "Obtient les informations des livres de la base de données du site de vente de livres mississippi.com",
    schema: z.object({
      information: z.string().describe("Le nom de l'information à obtenir depuis la base de données"),
    }),
  }
); 