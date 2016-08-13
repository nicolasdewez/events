Feature: Process
    If an event is send to application, it will be transfer

    Scenario: Publish an event by api
       Given the database is empty
         And The fixtures file "eventWithApplications.yml" is loaded
         And I prepare a POST request on "/api/publish"
         And I specified the following request body:
            """
            {
                "title": "myEvent",
                "namespace": "MyBundle\\MyModel",
                "payload": "{\"my_attribute1\": 1, \"my_attribute2\": \"title\"}"
            }
            """
        When I send the request
        Then I have a message in database with values
            | state     | sent                                           |
            | title     | myEvent                                        |
            | namespace | MyBundle\MyModel                               |
            | payload   | {"my_attribute1": 1, "my_attribute2": "title"} |
            | partials  |                                                |
