Feature: Admin events
    I should be manage events

    Background:
       Given the database is empty
         And The fixtures file "user.yml" is loaded
        When I go to "/"
         And I fill in "_username" with "ndewez"
         And I fill in "_password" with "ndewez"
         And I press "Log in"

    Scenario: On page events list, i should see list events
       Given The fixtures file "events.yml" is loaded
        When I follow "Events"
        Then I should see "Add event"
         And I should see the following table
            | Code     | Applications (nb) |
            | myEvent1 | 0                 |
            | myEvent2 | 0                 |
         And I should see 2 active elements

    Scenario: I can create an inactive event
        When I follow "Events"
        Then I should see 0 active elements
         And I should see 0 inactive elements
         And I should see the following table
            | Code        |
            | No elements |
        When I follow "Add event"
         And I fill in "event_code" with "myEvent"
         And I uncheck "event_active"
         And I press "Save"
        Then I should see "Event added"
         And I should see the following table
            | Code    | Applications (nb) |
            | myEvent | 0                 |
         And I should see 1 inactive elements
         And I should see 0 active elements

    Scenario: I can create an event without application
        When I follow "Events"
        Then I should see 0 active elements
         And I should see the following table
            | Code        |
            | No elements |
        When I follow "Add event"
         And I fill in "event_code" with "myEvent"
         And I press "Save"
        Then I should see "Event added"
         And I should see the following table
            | Code    | Applications (nb) |
            | myEvent | 0                 |
         And I should see 1 active elements

    @javascript
    Scenario: I can create an event with application
       Given The fixtures file "application.yml" is loaded
        When I follow "Events"
        Then I should see 0 active elements
         And I should see the following table
            | Code        |
            | No elements |
        When I follow "Add event"
         And I fill in "event_code" with "myEvent"
         And I follow "Add element"
         And I press "Save"
        Then I should see "Event added"
         And I should see the following table
             | Code    | Applications (nb) |
             | myEvent | 1                 |
         And I should see 1 active elements

    Scenario: I can update an event
       Given The fixtures file "event.yml" is loaded
        When I follow "Events"
        Then I should see the following table
            | Code    | Applications (nb) |
            | myEvent | 0                 |
         And I should see 1 active elements
        When I follow "myEvent"
         And I fill in "event_code" with "event"
         And I press "Save"
        Then I should see "Event updated"
         And I should see the following table
             | Code  | Applications (nb) |
             | event | 0                 |
         And I should see 1 active elements

    Scenario: I can disable an event
       Given The fixtures file "event.yml" is loaded
        When I follow "Events"
        Then I should see the following table
            | Code    | Applications (nb) |
            | myEvent | 0                 |
         And I should see 1 active elements
         And I should see 0 inactive elements
        When I follow "myEvent"
         And I uncheck "event_active"
         And I press "Save"
        Then I should see "Event updated"
         And I should see the following table
            | Code    | Applications (nb) |
            | myEvent | 0                 |
         And I should see 0 active elements
         And I should see 1 inactive elements

    @javascript
    Scenario: I can update an event and add an application
       Given The fixtures file "event.yml" is loaded
         And The fixtures file "applications.yml" is loaded
        When I follow "Events"
        Then I should see the following table
            | Code    | Applications (nb) |
            | myEvent | 0                 |
         And I should see 1 active elements
        When I follow "myEvent"
         And I follow "Add element"
         And I press "Save"
        Then I should see "Event updated"
         And I should see the following table
            | Code    | Applications (nb) |
            | myEvent | 1                 |
         And I should see 1 active elements

    @javascript
    Scenario: I can update an event and delete an application
       Given The fixtures file "eventWithApplications.yml" is loaded
        When I follow "Events"
        Then I should see the following table
            | Code    | Applications (nb) |
            | myEvent | 2                 |
         And I should see 1 active elements
        When I follow "myEvent"
         And I follow "Delete element"
         And I press "Save"
        Then I should see "Event updated"
         And I should see the following table
            | Code    | Applications (nb) |
            | myEvent | 1                 |
         And I should see 1 active elements
