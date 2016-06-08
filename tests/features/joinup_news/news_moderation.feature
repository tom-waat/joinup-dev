@api @javascript
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
      | title                         | Kicker                                      | Content                                                                 | published | field_news_state | author        |
      | Creating Justice League       | 6 Members to start with                     | TBD                                                                     | 0         | draft            | Eagle         |
      | Hawkgirl is a spy             | Her race lies in another part of the galaxy | Hawkgirl has been giving information about Earth to Thanagarians.       | 0         | proposed         | Eagle         |
      | Hawkgirl helped Green Lantern | Hawkgirl went against Thanagarians?         | It was all of a sudden when Hawkgirl turned her back to her own people. | 1         | validated        | Eagle         |
      | Space cannon fired            | Justice League fired at army facilities     | Justice league is now the enemy                                         | 1         | in_assessment    | Eagle         |
      | Eagle to join in season 4     | Will not start before S04E05                | The offer came when I helped defeating Iphestus armor.                  | 0         | proposed         | Eagle         |
      | Question joined JL            | Justice league took in Question             | The famous detective is now part of JL.                                 | 1         | draft            | Question      |

      | Creating Legion of Doom       | 7 Members to start with                     | We need equal number of members with the JL.                            | 1         | draft            | Mirror Master |
      | Stealing from Batman          | Hide in his car's mirror                    | I need to steal from Batman.                                            | 1         | validated        | Mirror Master |
      | Learn batman's secret         | Can I find batman's secret identity         | I have the opportunity to find out his identity.                        | 0         | proposed         | Mirror Master |
      | Stealing complete             | All data were copied                        | Now someone has to decrypt the data.                                    | 1         | in_assessment    | Mirror Master |
      | Kill the sun                  | Savages plan                                | As it turns out Savage's plan is to cause a solar storm.                | 1         | deletion_request | Mirror Master |
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