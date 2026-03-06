describe("Recherche de livre", () => {
    const visitPath = (path) => {
        const baseUrl = Cypress.config("baseUrl");
        if (baseUrl) {
            cy.visit(path);
            return;
        }
    };

    it("recherche un livre depuis la page d'accueil et vérifie le titre", () => {
        visitPath("/");

        cy.get('input[type="search"][name="q"]')
            .first()
            .type("Red Rising Pierce Brown{enter}");

        cy.wait(1000);

        cy.get("article").first().find("a").first().click();

        cy.get("h1").should("contain", "Red Rising");
    });
});
