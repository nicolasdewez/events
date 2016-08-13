Feature: Admin applications
    I should be manage applications

    Background:
       Given the database is empty
         And The fixtures file "user.yml" is loaded
        When I go to "/"
         And I fill in "_username" with "ndewez"
         And I fill in "_password" with "ndewez"
         And I press "Log in"

    Scenario: On page applications list, i should see list applications
       Given The fixtures file "applications.yml" is loaded
        When I follow "Applications"
        Then I should see "Add application"
         And I should see the following table
            | Code   | Title         | Events type            |
            | APP 1  | Application 1 | Asynchronous (Queuing) |
            | APP 2  | Application 2 | Synchronous (API)      |
         And I should see 2 active elements

    Scenario: I can create an inactive application
        When I follow "Applications"
        Then I should see 0 active elements
         And I should see 0 inactive elements
        When I follow "Add application"
         And I fill in "application_code" with "APP"
         And I fill in "application_title" with "Application"
         And I fill in "application_eventsType" with "asynchronous"
         And I uncheck "application_active"
         And I press "Save"
        Then I should see "Application added"
         And I should see the following table
            | Code | Title       | Events type            |
            | APP  | Application | Asynchronous (Queuing) |
         And I should see 1 inactive elements
         And I should see 0 active elements

    Scenario: I can create an asynchronous application
        When I follow "Applications"
        Then I should see 0 active elements
        When I follow "Add application"
         And I fill in "application_code" with "APP"
         And I fill in "application_title" with "Application"
         And I fill in "application_eventsType" with "asynchronous"
         And I press "Save"
        Then I should see "Application added"
         And I should see the following table
            | Code | Title       | Events type            |
            | APP  | Application | Asynchronous (Queuing) |
         And I should see 1 active elements

    Scenario: I can create a synchronous application
        When I follow "Applications"
        Then I should see 0 active elements
        When I follow "Add application"
         And I fill in "application_code" with "APP"
         And I fill in "application_title" with "Application"
         And I fill in "application_url" with "http://localhost"
         And I fill in "application_eventsType" with "synchronous"
         And I press "Save"
        Then I should see "Application added"
         And I should see the following table
            | Code | Title       | Events type       |
            | APP  | Application | Synchronous (API) |
         And I should see 1 active elements

    Scenario: I can't create a synchronous application without url
        When I follow "Applications"
        Then I should see 0 active elements
        When I follow "Add application"
         And I fill in "application_code" with "APP"
         And I fill in "application_title" with "Application"
         And I fill in "application_eventsType" with "synchronous"
         And I press "Save"
        Then I should see "Field url is required for type asynchronous"
         And I should not see "Add application"

    Scenario: I can update an application
       Given The fixtures file "application.yml" is loaded
        When I follow "Applications"
        Then I should see the following table
            | Code   | Title         | Events type            |
            | APP 1  | Application 1 | Asynchronous (Queuing) |
        And I should see 1 active elements
        When I follow "APP 1"
         And I fill in "application_title" with "Application"
         And I press "Save"
        Then I should see "Application updated"
         And I should see the following table
            | Code   | Title       | Events type            |
            | APP 1  | Application | Asynchronous (Queuing) |
         And I should see 1 active elements

    Scenario: I can disable an application
       Given The fixtures file "application.yml" is loaded
        When I follow "Applications"
        Then I should see the following table
            | Code   | Title         | Events type            |
            | APP 1  | Application 1 | Asynchronous (Queuing) |
         And I should see 1 active elements
         And I should see 0 inactive elements
        When I follow "APP 1"
         And I uncheck "application_active"
         And I press "Save"
        Then I should see "Application updated"
         And I should see the following table
            | Code   | Title         | Events type            |
            | APP 1  | Application 1 | Asynchronous (Queuing) |
         And I should see 0 active elements
         And I should see 1 inactive elements

    Scenario: I can update the event type of an application
       Given The fixtures file "application.yml" is loaded
        When I follow "Applications"
        Then I should see the following table
            | Code   | Title         | Events type            |
            | APP 1  | Application 1 | Asynchronous (Queuing) |
         And I should see 1 active elements
        When I follow "APP 1"
         And I fill in "application_eventsType" with "synchronous"
         And I fill in "application_url" with "http://localhost"
         And I press "Save"
        Then I should see "Application updated"
         And I should see the following table
            | Code   | Title         | Events type            |
            | APP 1  | Application 1 | Synchronous (API) |
         And I should see 1 active elements

    Scenario: I can't update the event type of an application to synchronous without url (without javascript)
       Given The fixtures file "application.yml" is loaded
        When I follow "Applications"
        Then I should see the following table
            | Code   | Title         | Events type            |
            | APP 1  | Application 1 | Asynchronous (Queuing) |
         And I should see 1 active elements
        When I follow "APP 1"
         And I fill in "application_eventsType" with "synchronous"
         And I press "Save"
        Then I should see "Field url is required for type asynchronous"
         And I should not see "Add application"

    @javascript
    Scenario: I can't update the event type of an application to synchronous without url
       Given The fixtures file "application.yml" is loaded
        When I follow "Applications"
        Then I should see the following table
            | Code   | Title         | Events type            |
            | APP 1  | Application 1 | Asynchronous (Queuing) |
         And I should see 1 active elements
        When I follow "APP 1"
         And I fill in "application_eventsType" with "synchronous"
         And I press "Save"
        Then I should not see "Add application"
