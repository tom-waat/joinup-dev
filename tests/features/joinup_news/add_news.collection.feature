@api
  # This features tests the workflow transitions. This is a complete test.
  # There is already a 'proof of concept'. @see tests/features/joinup_news/add_news.collection.feature.
  #
  # The steps @Then I go to the "news" content :title edit screen
  # can be tested through the UI by going to the page of the content and press
  # the edit button. But there are many edit buttons on the screen including
  # the contextual links.
  #
  # The steps @And the :title news content should (not )be published
  # cannot be tested through the UI and are only for ensuring proper
  # functionality. Published attribute can be found though by a moderator in
  # the content's edit screen.
Feature: News moderation.
  As a facilitator, member or collection administrator, or a site administrator
  In order to manage collection news
  I need to be able to have a workflow based news management system.

  Scenario: "Add news" button should only be shown to moderators and group members.
        # Check visibility for anonymous users.
    When I am not logged in
    And I go to the homepage of the "Justice League" collection
    Then I should not see the link "Add news"
        # Check visibility for authenticated users.
    When I am logged in as an "authenticated user"
    And I go to the homepage of the "Justice League" collection
    Then I should not see the link "Add news"
        # User from another collection should not be able to see the 'Add news'.
    When I am logged in as "Cheetah"
    And I go to the homepage of the "Justice League" collection
    Then I should not see the link "Add news"
        # Administrators cannot create content. Facilitators are the moderators of
        # the collection.
    When I am logged in as "Superman"
    And I go to the homepage of the "Justice League" collection
    Then I should not see the link "Add news"
    When I am logged in as "Hawkgirl"
    And I go to the homepage of the "Justice League" collection
    Then I should see the link "Add news"
    When I am logged in as "Eagle"
    And I go to the homepage of the "Justice League" collection
    Then I should see the link "Add news"


  Scenario: Add news as a member to a post-moderated collection.
    # Add news as a member.
    # There is no need to check for a facilitator because when he creates news,
    # he does it as a member. The transitions are the same for post moderated
    # collections in terms of news creation.
    When I am logged in as "Eagle"
    And I go to the homepage of the "Justice League" collection
    And I click "Add news"
    Then I should see the heading "Add news"
    And the following fields should be present "Headline, Kicker, Content, State"
    And the following fields should not be present "Groups audience"
    And the "field_news_state" field has the "Draft, Validated" options
    And the "field_news_state" field does not have the "Proposed, In assessment, Request deletion" options
    When I fill in the following:
      | Headline | Eagle joins the JL                   |
      | Kicker   | Eagle from WWII                      |
      | Content  | Specialized in close combat training |
    And I select "Draft" from "State"
    And I press "Save"
    # Check reference to news page.
    Then I should not see the success message "News <em>Eagle joins the JL</em> has been created."
    And the "Eagle joins the JL" news content should not be published
   # Test a transition change.
    When I go to the "news" content "Eagle joins the JL" edit screen
    Then I should not see the heading "Access denied"
    And the "State" field has the "Draft, Validated" options
    And the "State" field does not have the "Proposed, In assessment, Request delection" options
    When I select "Validated" from "State"
    And I press "Save"
    Then I should see the text "Validated"
    Then I should not see the success message "News <em>Eagle joins the JL</em> has been updated."
    And the "Eagle joins the JL" news content should be published
    When I click "Justice League"
    Then I should see the link "Eagle joins the JL"


  Scenario: Add news as a member to a pre-moderated collection and get it validated by a facilitator.
      # Add news as a member.
    When I am logged in as "Cheetah"
    And I go to the homepage of the "Legion of Doom" collection
    And I click "Add news"
    And the "State" field has the "Draft, Proposed" options
    And the "State" field does not have the "Validated, In assessment, Request delection" options
    When I fill in the following:
      | Headline | Cheetah kills WonderWoman                             |
      | Kicker   | Scarch of poison                                      |
      | Content  | A specific poison could expose Wonder-womans weakness |
    And I select "Proposed" from "State"
    And I press "Save"
      # Check reference to news page.
    Then I should not see the success message "News <em>Cheetah kills WonderWoman</em> has been created."
    Then I should see the heading "Cheetah kills WonderWoman"
    And the "Cheetah kills WonderWoman" news content should not be published
    And I should see the text "Collection"
    And I should see the text "Legion of Doom"
      # Visit the collection's news entity and press edit
    And I go to the "news" content "Cheetah kills WonderWoman" edit screen
    Then I should see the heading "Access denied"
      # Edit and publish the news as a facilitator
    When I am logged in as "Metallo"
    And I go to the "news" content "Cheetah kills WonderWoman" edit screen
    Then I should not see the heading "Access denied"
    And the "State" field has the "Proposed, Validated" options
    And the "State" field does not have the "Draft, In assessment, Request delection" options
    When I select "Validated" from "State"
    And I press "Save"
    Then I should see the text "Validated"
    And the "Cheetah kills WonderWoman" news content should be published
    When I click "Legion of Doom"
    Then I should see the link "Cheetah kills WonderWoman"



  Scenario Outline: Members can only edit news they own for specific states.
    # Post moderated.
    Given I am logged in as "<user>"
    When I visit the "news" content "<title>" edit screen
    Then I should not see the heading "Access denied"
    And the "State" field has the "<options available>" options
    And the "State" field does not have the "<options unavailable>" options
    Examples:
      | user          | title                         | options available | options unavailable                              |
      # State: draft, owned by Eagle
      | Eagle         | Creating Justice League       | Draft, Validated  | Proposed, In assessment                          |
      # State: validated, can report
      | Eagle         | Hawkgirl helped Green Lantern | In assessment     | Draft, Validated, Proposed                       |
      # State: draft, can propose
      | Mirror Master | Creating Legion of Doom       | Propose           | Draft, Validate, In assessment, Request deletion |
      # State: validated, can report
      | Mirror Master | Stealing from Batman          | In assessment     | Draft, Propose, Validate, Request deletion       |






  Scenario Outline: Members cannot edit news they own for specific states.
    Given I am logged in as "<user>"
    When I visit the "news" content "<title>" edit screen
    Then I should see the heading "Access denied"
    Examples:
      | user          | title                     |
      # State: proposed
      | Eagle         | Hawkgirl is a spy         |
      # State: in assessment
      | Eagle         | Space cannon fired        |
      # State: proposed
      | Eagle         | Eagle to join in season 4 |
      # State: draft, not owned
      | Eagle         | Question joined JL        |
      # State: proposed
      | Mirror Master | Learn batman's secret     |
      # State: draft, not owned
      | Mirror Master | Creating Legion of Doom   |
      # State: in assessment
      | Mirror Master | Stealing complete         |
      # State: deletion request
      | Mirror Master | Kill the sun              |

  Scenario Outline: Facilitators have access on content regardless of state.
    # Post moderated
    Given I am logged in as "<user>"
    When I visit the "news" content "<title>" edit screen
    Then I should not see the heading "Access denied"
    And the "State" field has the "<options available>" options
    And the "State" field has the "<options unavailable>" options
    Examples:
      | user     | title                         | options available                  | options unavailable                               |
      # Post moderated
      | Hawkgirl | Creating Justice League       | Draft, Validated                   | Proposed, In assessment, Request deletion         |
      | Hawkgirl | Hawkgirl is a spy             | Proposed, Validated                | Draft, In assessment, Request deletion            |
      | Hawkgirl | Hawkgirl helped Green Lantern | Validated, Proposed, In assessment | Draft, Request deletion                           |
      | Hawkgirl | Space cannon fired            | Proposed                           | Draft, Validated, In assessment, Request deletion |
      # Pre moderated
      | Metallo | Creating Legion of Doom | Proposed, Validated                                  | Draft, In assessment, Request deletion           |
      | Metallo | Stealing from Batman    | Proposed, Validated, In assessment, Request deletion | Draft                                            |
      | Metallo | Learn batman's secret   | Proposed, Validated                                  | Draft, In assessment, Request deletion           |
      | Metallo | Stealing complete       | Proposed                                             | Draft, Request deletion                          |
      | Metallo | Kill the sun            | Validated                                            | Draft, Proposed, In assessment, Request deletion |

  Scenario Outline: Moderators can edit news regardless of their state.
    Given I am logged in as "Batman"
    When I visit the "news" content "<title>" edit screen
    Then I should not see the heading "Access denied"
    Examples:
      | title                         |
      | Creating Justice League       |
      | Hawkgirl is a spy             |
      | Hawkgirl helped Green Lantern |
      | Space cannon fired            |
      | Eagle to join in season 4     |
      | Question joined JL            |
      | Creating Legion of Doom       |
      | Creating Legion of Doom       |
      | Stealing from Batman          |
      | Learn batman's secret         |

  Scenario: An entity should be automatically published/un published according to state
    # Regardless of moderation, the entity is published for the states
    # Validated, In assessment, Request deletion
    # and unpublished for Draft and Proposed.
    When I am logged in as "Hawkgirl"
    And I go to the "news" content "Hawkgirl is a spy" edit screen
    And I select "Validated" from "State"
    And I press "Save"
    Then the "Hawkgirl is a spy" "news" content should be published
    And I go to the "news" content "Hawkgirl is a spy" edit screen
    And I select "Proposed" from "State"
    And I press "Save"
    Then the "Hawkgirl is a spy" "news" content should not be published