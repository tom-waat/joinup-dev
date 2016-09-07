@api
Feature: "Add news" visibility options.
  In order to manage news
  As a solution member
  I need to be able to add "News" content through UI.

  Scenario: "Add news" button should not be shown to normal members, authenticated users and anonymous users.
    Given the following solutions:
      | title           | logo     | banner     |
      | Ragged Tower    | logo.png | banner.jpg |
      | Prince of Magic | logo.png | banner.jpg |
    And the following collection:
      | title      | Collective Ragged tower       |
      | logo       | logo.png                      |
      | banner     | banner.jpg                    |
      | affiliates | Ragged Tower, Prince of Magic |

    When I am logged in as an "authenticated user"
    And I go to the homepage of the "Ragged Tower" solution
    Then I should not see the link "Add news"

    When I am an anonymous user
    And I go to the homepage of the "Ragged Tower" solution
    Then I should not see the link "Add news"

    When I am logged in as a "facilitator" of the "Ragged Tower" solution
    And I go to the homepage of the "Ragged Tower" solution
    Then I should see the link "Add news"
    # I should not be able to add a news to a different solution
    When I go to the homepage of the "Prince of Magic" solution
    Then I should not see the link "Add news"

    When I am logged in as a "moderator"
    And I go to the homepage of the "Ragged Tower" solution
    Then I should see the link "Add news"

  Scenario: Add news as a facilitator.
    Given solutions:
      | title                | logo     | banner     |
      | The Luscious Bridges | logo.png | banner.jpg |
    And the following collection:
      | title      | Collective The Luscious Bridges |
      | logo       | logo.png                        |
      | banner     | banner.jpg                      |
      | affiliates | The Luscious Bridges            |
    And I am logged in as a facilitator of the "The Luscious Bridges" solution
    When I go to the homepage of the "The Luscious Bridges" solution
    And I click "Add news"
    Then I should see the heading "Add news"
    And the following fields should be present "Headline, Kicker, Content, URL, State, Spatial coverage, Topic"
    And the following fields should not be present "Groups audience"
    When I fill in the following:
      | Headline | Android 7 Nougat Review                        |
      | Kicker   | Small Update, Big Time Saver                   |
      | Content  | Google is rolling out a new flavor of Android. |

    # @todo Uncomment next steps when a workflow for solution nes will be in place.
    # And I press "Save"
    # Then I should see the heading "Android 7 Nougat Review"
    # And I should see the success message "News Android 7 Nougat Review has been created."
    # And the "The Luscious Bridges" solution has a news page titled "Android 7 Nougat Review"
    # Check that the link to the news is visible on the solution page.
    # When I go to the homepage of the "The Luscious Bridges" solution
    # Then I should see the link "Android 7 Nougat Review "
