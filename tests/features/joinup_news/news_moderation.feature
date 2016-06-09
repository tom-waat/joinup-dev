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

  Background:
    # The complete list of the authorized roles are stored in configuration.
    # @see: modules/custom/joinup_news/config/install/joinup_news.settings.yml.
    Given collections:
      | title          | logo     | moderation |
      | Justice League | logo.png | no         |
      | Legion of Doom | logo.png | yes        |
    And users:
      | name          | pass             | mail                              | roles     |
      | Batman        | BatsEverywhere   | adminOfWayneINC@example.com       | moderator |
      | Superman      | PutYourGlassesOn | dailyPlanetEmployee23@example.com |           |
      | Hawkgirl      | IHaveWings       | hawkSounds@example.com            |           |
      | Eagle         | Ilovemycostume   | WolrdWarVeteran@example.com       |           |
      | Question      | secretsSecrets   | WhoAmI@example.com                |           |
      | Vandal Savage | IliveForever     | voldemort@example.com             |           |
      | Cheetah       | meowmeow         | ihatewonderwoman@example.com      |           |
      | Mirror Master | hideinmirrors    | mirrormirroronthewall@example.com |           |
      | Metallo       | checkMyHeart     | kryptoniteEverywhere@example.com  |           |
    And the following user memberships:
      | collection     | user          | roles         |
      | Justice League | Superman      | administrator |
      | Justice League | Hawkgirl      | facilitator   |
      | Justice League | Eagle         | member        |
      | Justice League | Question      | member        |
      | Legion of Doom | Vandal Savage | administrator |
      | Legion of Doom | Metallo       | facilitator   |
      | Legion of Doom | Mirror Master | member        |
      | Legion of Doom | Cheetah       | member        |
    And "news" content:
      | title                         | Kicker                                      | Content                                                                 | field_news_state | author        |
      | Creating Justice League       | 6 Members to start with                     | TBD                                                                     | draft            | Eagle         |
      | Hawkgirl is a spy             | Her race lies in another part of the galaxy | Hawkgirl has been giving information about Earth to Thanagarians.       | proposed         | Eagle         |
      | Hawkgirl helped Green Lantern | Hawkgirl went against Thanagarians?         | It was all of a sudden when Hawkgirl turned her back to her own people. | validated        | Eagle         |
      | Space cannon fired            | Justice League fired at army facilities     | Justice league is now the enemy                                         | in_assessment    | Eagle         |
      | Eagle to join in season 4     | Will not start before S04E05                | The offer came when I helped defeating Iphestus armor.                  | proposed         | Eagle         |
      | Question joined JL            | Justice league took in Question             | The famous detective is now part of JL.                                 | draft            | Question      |

      | Creating Legion of Doom       | 7 Members to start with                     | We need equal number of members with the JL.                            | draft            | Mirror Master |
      | Stealing from Batman          | Hide in his car's mirror                    | I need to steal from Batman.                                            | validated        | Mirror Master |
      | Learn batman's secret         | Can I find batman's secret identity         | I have the opportunity to find out his identity.                        | proposed         | Mirror Master |
      | Stealing complete             | All data were copied                        | Now someone has to decrypt the data.                                    | in_assessment    | Mirror Master |
      | Kill the sun                  | Savages plan                                | As it turns out Savage's plan is to cause a solar storm.                | deletion_request | Mirror Master |
    And "news" content belong to the corresponding collections:
      | content                       | collection     |
      | Creating Justice League       | Justice League |
      | Hawkgirl is a spy             | Justice League |
      | Hawkgirl helped Green Lantern | Justice League |
      | Space cannon fired            | Justice League |
      | Eagle to join in season 4     | Justice League |
      | Question joined JL            | Justice League |
      | Creating Legion of Doom       | Legion of Doom |
      | Stealing from Batman          | Legion of Doom |
      | Learn batman's secret         | Legion of Doom |
      | Stealing complete             | Legion of Doom |
      | Kill the sun                  | Legion of Doom |


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
      | Eagle         | Creating Justice League       | Validated         | Draft, Proposed, In assessment                   |
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