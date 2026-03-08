describe("Inscription", () => {
    const visitPath = (path) => {
        const baseUrl = Cypress.config("baseUrl");
        if (baseUrl) {
            cy.visit(path);
            return;
        }
    };

    const uniqueEmail = `cypress.${Date.now()}@example.test`;
    const password = "Cypress123!";

    const wrongEmail = "wrongemail@test.com"

    it("crée un compte", () => {
        visitPath("/register");

        cy.get("h1").should("contain", "Créer un compte");

        cy.get("#lastName").type("Test");
        cy.get("#firstName").type("Cypress");
        cy.get("#email").type(uniqueEmail);
        cy.get("#password").type(password, { log: false });
        cy.get("#phone").type("0600000000");

        cy.contains("button", "Créer le compte").click();
    });

    it("se connecte avec le compte créé", () => {
        visitPath("/login");

        cy.get("h1").should("contain", "Connexion");
        cy.get("#username").type(uniqueEmail);
        cy.get("#password").type(password, { log: false });
        cy.contains("button", "Se connecter").click();
    });

    it("se connecter avec la commande personnalisé", () => {
        cy.login(uniqueEmail, password);
    });

    it("échoue à se connecter avec un email invalide", () => {
        visitPath("/login");

        cy.get("h1").should("contain", "Connexion");
        cy.get("#username").type(wrongEmail);
        cy.get("#password").type(password, { log: false });
        cy.contains("button", "Se connecter").click();
        cy.get(".bg-red-50").should("be.visible");
        cy.get(".bg-red-50").invoke("text").should("match", /invalides|Invalid credentials/i);
    });
});
