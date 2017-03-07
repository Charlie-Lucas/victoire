@mink:selenium2 @alice(Page) @alice(SpaceshipTemplate) @reset-schema
Feature: Business Domain Strategy

  Background:
    Given I maximize the window
    And I am on homepage

  Scenario: I create a BE with a BP with different locaes
    Given the following WidgetMaps:
      | id | action | position | parent | slot         | view              |
      | 1  | create |          |        | main_content | Spaceship template |
    Given the following WidgetTexts:
      | mode           | widgetMap  | businessentityId | fields                          |
      | businessEntity | 1          | spaceship        | a:1:{s:7:"content";s:4:"name";} |

  Scenario: I create a new spaceship in en and fr and access to their pages
    Given I am on "/victoire-dcms/backend/spaceship/"
    Then I should see "Aucun résultat"
    When I follow "Nouveau vaisseau spatial"
    Then I should be on "/victoire-dcms/backend/spaceship/new"
    And I should see "Nouveau vaisseau spatial"
    When  I follow "Fr [Default]"
    Then I fill in "Nom" with "Étoile de la mort"
    And I follow "En"
    Then I fill in "Nom" with "Death Star"
    And I press "Créer"
    When I am on "/vaisseau-spacial-etoile-de-la-mort"
    Then I should see "Étoile de la mort"
    When I visit homepage through domain "en.victoire.io"
    When I am on "/spaceship-death-star"
    Then I should see "Death Star"
    #Then the title should be "Homepage"
    #Given I visit homepage through domain "fr.victoire.io"
    #Then the title should be "Page d'accueil"

