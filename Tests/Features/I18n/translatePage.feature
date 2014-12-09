@mink:selenium2 @database @fixtures @setBrowserMaxSize
Feature: Translate a page

Background:
    Given I am logged in as "admin@appventus.com" with password "test"

Scenario:  Translate the homepage
    Then I switch to edit mode "true"
    When I select "Anakin" from the "1" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Obscure"
    And I submit the widget
    Then I should see "Victoire !"
    And I reload the page
    Then I should see "Le côté Obscure de la force"
    When I follow "Traduire"
    Then I should see "Nouvelle traduction de la page Page d'accueil"
    And  I submit the page
    Then I should see "Victoire !"

