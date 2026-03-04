import { Client } from "@modelcontextprotocol/sdk/client/index.js";
import { StdioClientTransport } from "@modelcontextprotocol/sdk/client/stdio.js";
import { DynamicStructuredTool } from "@langchain/core/tools";
import { z } from "zod";

// Client MCP singleton
let mcpClient: Client | null = null;

/**
 * Initialise et retourne le client MCP
 */
async function getMcpClient(): Promise<Client> {
  if (!mcpClient) {
    const transport = new StdioClientTransport({
      command: "symfony",
      args: ["console", "mcp:server"],
      cwd: "/Users/ilian/Dev/Decode/Projet-Decode-S1/projet-s1-symfony",
    });

    mcpClient = new Client(
      {
        name: "mississippi-agent",
        version: "1.0.0",
      },
      {
        capabilities: {},
      }
    );

    await mcpClient.connect(transport);
  }
  return mcpClient;
}

/**
 * Outil 1: Lister toutes les œuvres
 */
export const listAllWorksTool = new DynamicStructuredTool({
  name: "list_all_works",
  description: "Liste toutes les œuvres littéraires disponibles dans la base de données avec leurs auteurs, genres et livres",
  schema: z.object({}),
  func: async (_input: any) => {
    const client = await getMcpClient();
    const result: any = await client.callTool({
      name: "get_works",
      arguments: {},
    });
    return result.content[0].text;
  },
});

/**
 * Outil 2: Récupérer une œuvre par ID
 */
export const getWorkByIdTool = new DynamicStructuredTool({
  name: "get_work_by_id",
  description: "Récupère les détails complets d'une œuvre littéraire par son ID (auteurs, genres, livres disponibles, prix, stock)",
  schema: z.object({
    id: z.number().describe("ID de l'œuvre à récupérer"),
  }),
  func: async (input: any) => {
    const { id } = input;
    const client = await getMcpClient();
    const result: any = await client.callTool({
      name: "get_work",
      arguments: { id },
    });
    return result.content[0].text;
  },
});

/**
 * Outil 3: Rechercher une œuvre par titre
 */
export const searchWorkByTitleTool = new DynamicStructuredTool({
  name: "search_work_by_title",
  description: "Recherche une œuvre littéraire par son titre. Effectue une recherche exacte en priorité, puis partielle si aucun résultat. Retourne toutes les œuvres correspondantes.",
  schema: z.object({
    title: z.string().describe("Titre de l'œuvre à rechercher (peut être partiel)"),
  }),
  func: async (input: any) => {
    const { title } = input;
    const client = await getMcpClient();
    const result: any = await client.callTool({
      name: "get_work_by_title",
      arguments: { title },
    });
    return result.content[0].text;
  },
});

/**
 * Outil 4: Lister tous les auteurs
 */
export const listAllAuthorsTool = new DynamicStructuredTool({
  name: "list_all_authors",
  description: "Liste tous les auteurs disponibles dans la base de données avec leurs biographies et œuvres",
  schema: z.object({}),
  func: async (_input: any) => {
    const client = await getMcpClient();
    const result: any = await client.callTool({
      name: "get_authors",
      arguments: {},
    });
    return result.content[0].text;
  },
});

/**
 * Outil 5: Récupérer un auteur par ID
 */
export const getAuthorByIdTool = new DynamicStructuredTool({
  name: "get_author_by_id",
  description: "Récupère les détails complets d'un auteur par son ID (biographie, photo, liste de ses œuvres)",
  schema: z.object({
    id: z.number().describe("ID de l'auteur à récupérer"),
  }),
  func: async (input: any) => {
    const { id } = input;
    const client = await getMcpClient();
    const result: any = await client.callTool({
      name: "get_author",
      arguments: { id },
    });
    return result.content[0].text;
  },
});

/**
 * Outil 6: Rechercher un auteur par nom/prénom
 */
export const searchAuthorByNameTool = new DynamicStructuredTool({
  name: "search_author_by_name",
  description: "Recherche un auteur par son prénom et/ou nom de famille. Effectue une recherche exacte puis partielle. Au moins un paramètre requis. Retourne tous les auteurs correspondants.",
  schema: z.object({
    firstName: z.string().optional().describe("Prénom de l'auteur (optionnel si lastName fourni)"),
    lastName: z.string().optional().describe("Nom de famille de l'auteur (optionnel si firstName fourni)"),
  }),
  func: async (input: any) => {
    const { firstName, lastName } = input;
    const client = await getMcpClient();
    const result: any = await client.callTool({
      name: "get_author_by_name",
      arguments: { firstName, lastName },
    });
    return result.content[0].text;
  },
});

/**
 * Export de tous les outils Mississippi
 */
export const mississippiTools = [
  listAllWorksTool,
  getWorkByIdTool,
  searchWorkByTitleTool,
  listAllAuthorsTool,
  getAuthorByIdTool,
  searchAuthorByNameTool,
];










