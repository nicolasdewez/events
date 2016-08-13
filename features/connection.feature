Feature: Connection
    I should be connected for access to application

    Scenario: If i'm not authenticated i'm redirected to connection page
        When I go to "/"
        Then I should see "Username"
         And I should see "Password"
         And I should see "Log in"

    Scenario: I can log in with correct credentials
       Given the database is empty
         And The fixtures file "user.yml" is loaded
        When I go to "/"
         And I fill in "_username" with "ndewez"
         And I fill in "_password" with "ndewez"
         And I press "Log in"
        Then I should see "Home"
         And I should see "Log out"

    Scenario: I can't log in with bad credentials
       Given the database is empty
        When I go to "/"
         And I fill in "_username" with "test"
         And I fill in "_password" with "test"
         And I press "Log in"
        Then I should see "Username"

    Scenario: When i logged in i can log out
       Given the database is empty
         And The fixtures file "user.yml" is loaded
        When I go to "/"
         And I fill in "_username" with "ndewez"
         And I fill in "_password" with "ndewez"
         And I press "Log in"
         And I follow "Log out"
        Then the url should match "/"
