@api @email
Feature: Add comments
  As a visitor of the website I can leave a comment on community content.

  Background:
    Given the following collections:
      | title             | state     | closed |
      | Gossip collection | validated | no     |
      | Shy collection    | validated | yes    |
    And users:
      | Username          | E-mail                        | Roles     | First name | Family name |
      | Miss tell tales   | tell.tales@example.com        |           | Miss       | Tales       |
      | Comment moderator | comment.moderator@example.com | moderator | Comment    | Moderator   |

  Scenario Outline: Make an anonymous comment, needs moderation.
    Given <content type> content:
      | title   | body                                                | collection        | state   |
      | <title> | How could this ever happen? Moral panic on its way! | Gossip collection | <state> |
    Given I am an anonymous user
    And all e-mails have been sent
    When I go to the content page of the type "<content type>" with the title "<title>"
    And I fill in "Your name" with "Mr Scandal"
    And I fill in "Email" with "mrscandal@example.com"
    And I fill in "Create comment" with "I've heard this story..."
    Then I press "Post comment"
    Then I should see the following success messages:
      | Your comment has been queued for review by site administrators and will be published after approval. |
    And I should not see "I've heard this story..."
    And the following email should have been sent:
      | recipient | Comment moderator                                                                              |
      | subject   | Joinup: A new comment has been created.                                                        |
      | body      | an anonymous user posted a comment in collection "Gossip collection".To view the comment click |

    # Users with 'administer comments' permission can see the comment that is set for approval.
    Given I am logged in as a facilitator of the "Gossip collection" collection
    When I go to the content page of the type "<content type>" with the title "<title>"
    Then I should see "I've heard this story..."

    # The configuration options for comments should not be shown to
    # facilitators. Whether or not comments are available is managed on
    # collection level.
    When I click "Edit"
    Then I should not see the text "Comment settings"

    Examples:
      | content type | title               | state     |
      | news         | Scandalous news     | validated |
      | event        | Celebrity gathering | validated |
      | discussion   | Is gossip bad?      | validated |
      | document     | Wikileaks           | validated |

  Scenario Outline: Make an authenticated comment, skips moderation.
    Given <content type> content:
      | title   | body                                                | collection        | state   |
      | <title> | How could this ever happen? Moral panic on its way! | Gossip collection | <state> |
    Given I am logged in as "Miss tell tales"
    And all e-mails have been sent
    When I go to the content page of the type "<content type>" with the title "<title>"
    And I fill in "Create comment" with "Mr scandal was doing something weird the other day."
    Then I press "Post comment"
    Then I should not see the following success messages:
      | Your comment has been queued for review by site administrators and will be published after approval. |
    And I should see text matching "Mr scandal was doing something weird the other day."
    And the following email should have been sent:
      | recipient | Comment moderator                                                                       |
      | subject   | Joinup: A new comment has been created.                                                 |
      | body      | Miss Tales posted a comment in collection "Gossip collection".To view the comment click |

    Examples:
      | content type | title               | state     |
      | news         | Scandalous news     | validated |
      | event        | Celebrity gathering | validated |
      | discussion   | Is gossip bad?      | validated |
      | document     | Wikileaks           | validated |

  Scenario Outline: Comments are disallowed for anonymous users in closed collections.
    Given <content type> content:
      | title   | body                                                | collection     | state   |
      | <title> | How could this ever happen? Moral panic on its way! | Shy collection | <state> |

    # Anonymous users should not be able to comment.
    Given I am an anonymous user
    When I go to the content page of the type "<content type>" with the title "<title>"
    Then I should see the text "Login or create an account to comment"
    And the link "Login" should point to "user/login"
    And the link "create an account" should point to "user/register"
    And the following fields should not be present "Create comment"
    And I should not see the button "Post comment"

    # Logged-in users can still comment.
    Given I am logged in as "Miss tell tales"
    When I go to the content page of the type "<content type>" with the title "<title>"
    Then the following fields should be present "Create comment"
    And I should see the button "Post comment"

    Examples:
      | content type | title               | state     |
      | news         | Scandalous news     | validated |
      | event        | Celebrity gathering | validated |
      | discussion   | Is gossip bad?      | validated |
      | document     | Wikileaks           | validated |
