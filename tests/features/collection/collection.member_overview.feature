@api
Feature: Collection membership overview
  In order to foster my community and create a sense of belonging
  As a collection member
  I need to be able to see an overview of my fellow members

  Scenario: Show the collection members as a list of tiles
    Given the following owner:
      | name           |
      | Ayodele Sommer |
    And the following contact:
      | name  | Nita Yang             |
      | email | supernita@yahoo.co.uk |
    And users:
      # We're adding many users so we can test the different roles and states,
      #  as well as the pager. 12 users are shown per page.
      | Username              | First name | Family name | Photo        | Business title                  |
      | Ruby Robert           | Ruby       | Robert      | leonardo.jpg | Chairman                        |
      | Bohumil Unterbrink    | Bohumil    | Unterbrink  | ada.png      | Senior Executive Vice President |
      | Isabell Zahariev      | Isabell    | Zahariev    | charles.jpg  | Chief Executive Officer         |
      | Gemma Hackett         | Gemma      | Hackett     | tim.jpg      | President                       |
      | Delicia Hart          | Delicia    | Hart        | alan.jpg     | Executive Director              |
      | Sukhrab Valenta       | Sukhrab    | Valenta     | linus.jpeg   | Managing Director               |
      | Jun Schrader          | Jun        | Schrader    | blaise.jpg   | General Manager                 |
      | Ingibjörg De Snaaijer | Ingibjörg  | De Snaaijer | richard.jpg  | Department Head                 |
      | Suk Karpáti           | Suk        | Karpáti     | leonardo.jpg | Deputy General Manager          |
      | Janna Miller          | Janna      | Miller      | ada.png      | Assistant Manager               |
      | Lisa Miller           | Lisa       | Miller      | charles.jpg  | Chairman of the Board           |
      | Kendall Miller        | Kendall    | Miller      | tim.jpg      | Chief of Staff                  |
      | Kamil Napoleonis      | Kamil      | Napoleonis  | alan.jpg     | Commissioner                    |
      | Law Atteberry         | Law        | Atteberry   | linus.jpeg   | Comptroller                     |
      | Aniruddha Kováts      | Aniruddha  | Kováts      | blaise.jpg   | Chief Communications Officer    |
      | Aali Dalton           | Aali       | Dalton      | richard.jpg  | Founder                         |
    And the following collections:
      | title           | description        | logo     | banner     | owner          | contact information | state     |
      | Jubilant Robots | Fresh oil harvest! | logo.png | banner.jpg | Ayodele Sommer | Nita Yang           | validated |
    And the following collection user memberships:
      | collection      | user                  | roles       | state   |
      | Jubilant Robots | Ruby Robert           | owner       |         |
      | Jubilant Robots | Bohumil Unterbrink    | facilitator |         |
      | Jubilant Robots | Isabell Zahariev      |             | blocked |
      | Jubilant Robots | Gemma Hackett         |             | pending |
      | Jubilant Robots | Delicia Hart          |             |         |
      | Jubilant Robots | Sukhrab Valenta       |             |         |
      | Jubilant Robots | Jun Schrader          |             |         |
      | Jubilant Robots | Ingibjörg De Snaaijer |             |         |
      | Jubilant Robots | Suk Karpáti           |             |         |
      | Jubilant Robots | Janna Miller          |             |         |
      | Jubilant Robots | Lisa Miller           |             |         |
      | Jubilant Robots | Kendall Miller        |             |         |
      | Jubilant Robots | Kamil Napoleonis      |             |         |
      | Jubilant Robots | Law Atteberry         |             |         |
      | Jubilant Robots | Aniruddha Kováts      |             |         |
      | Jubilant Robots | Aali Dalton           |             |         |

    # The membership overview should be accessible for anonymous users.
    When I am not logged in
    And I go to the "Jubilant Robots" collection
    Then I should see the link "Members" in the "Left sidebar"

    # The first 12 active members should be shown, ordered by last name - first name.
    When I click "Members"
    Then I should see the heading "Members"
    And I should see the following tiles in the correct order:
      | Law Atteberry         |
      | Aali Dalton           |
      | Ingibjörg De Snaaijer |
      | Delicia Hart          |
      | Suk Karpáti           |
      | Aniruddha Kováts      |
      | Janna Miller          |
      | Kendall Miller        |
      | Lisa Miller           |
      | Kamil Napoleonis      |
      | Ruby Robert           |
      | Jun Schrader          |
    # The 13th and 14th member should not be visible on this page, but on the next page.
    And I should not see the "Bohumil Unterbrink" tile
    And I should not see the "Sukhrab Valenta" tile
    # A blocked member should not be visible.
    And I should not see the "Isabell Zahariev" tile
    # A pending member should not be visible.
    And I should not see the "Gemma Hackett" tile

    # Navigate to the next page and check that the 13th member is now visible.
    When I click "››"
    Then I should see the "Bohumil Unterbrink" tile
    And I should see the "Sukhrab Valenta" tile

    # Clicking the user name should lead to the user profile page.
    When I click "Sukhrab Valenta"
    Then I should see the heading "Sukhrab Valenta"
