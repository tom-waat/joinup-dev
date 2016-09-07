@api
Feature: "Add news" visibility options.
  In order to manage news
  As a collection member
  I need to be able to add "News" content through UI.

  Scenario: "Add news" button should not be shown to normal members, authenticated users and anonymous users.
    Given the following collections:
      | title               | logo     | banner     |
      | The Stripped Stream | logo.png | banner.jpg |
      | Years in the Nobody | logo.png | banner.jpg |

    When I am logged in as an "authenticated user"
    And I go to the homepage of the "The Stripped Stream" collection
    Then I should not see the link "Add news"

    When I am an anonymous user
    And I go to the homepage of the "The Stripped Stream" collection
    Then I should not see the link "Add news"

    When I am logged in as a member of the "The Stripped Stream" collection
    And I go to the homepage of the "The Stripped Stream" collection
    Then I should see the link "Add news"

    When I am logged in as a "facilitator" of the "The Stripped Stream" collection
    And I go to the homepage of the "The Stripped Stream" collection
    Then I should see the link "Add news"
    # I should not be able to add a news to a different collection
    When I go to the homepage of the "Years in the Nobody" collection
    Then I should not see the link "Add news"

    When I am logged in as a "moderator"
    And I go to the homepage of the "The Stripped Stream" collection
    Then I should see the link "Add news"

  Scenario: Add news as a facilitator.
    Given collections:
      | title            | logo     | banner     |
      | Stream of Dreams | logo.png | banner.jpg |
    And I am logged in as a facilitator of the "Stream of Dreams" collection

    When I go to the homepage of the "Stream of Dreams" collection
    And I click "Add news"
    Then I should see the heading "Add news"
    And the following fields should be present "Headline, Kicker, Content, URL, State, Spatial coverage, Topic"
    And the following fields should not be present "Groups audience"
    When I fill in the following:
      | Headline | Android 7 Nougat Review                        |
      | Kicker   | Small Update, Big Time Saver                   |
      | Content  | Google is rolling out a new flavor of Android. |
    And I press "Save"
    Then I should see the heading "Android 7 Nougat Review"
    And I should see the success message "News Android 7 Nougat Review has been created."
    And the "Stream of Dreams" collection has a news page titled "Android 7 Nougat Review"
    # Check that the link to the news is visible on the collection page.
    When I go to the homepage of the "Stream of Dreams" collection
    Then I should see the link "Android 7 Nougat Review"
