<?php

use AppBundle\Entity\Message;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
* Defines application features from the specific context.
*/
class FeatureContext implements KernelAwareContext, SnippetAcceptingContext
{
    use KernelDictionary;

    /** @var MinkContext */
    private $minkContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->minkContext = $environment->getContext('Behat\MinkExtension\Context\MinkContext');
    }


    /**
     * @Then I should see :arg1 active elements
     */
    public function iShouldSeeActiveElements($arg1)
    {
        $this->minkContext->assertNumElements($arg1, 'td .glyphicon-ok');
    }

    /**
     * @Then I should see :arg1 inactive elements
     */
    public function iShouldSeeInactiveElements($arg1)
    {
        $this->minkContext->assertNumElements($arg1, 'td .glyphicon-remove');
    }

    /**
     * @Then /^I have a message in database with values:?$/
     */
    public function iHaveAMessageInDatabaseWithValues($expected)
    {
        if ($expected instanceof TableNode) {
            $table = $expected->getTable();

            $expected = [];
            foreach ($table as $line) {
                $expected[$line[0]] = $line[1];
            }
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $messages = $em->getRepository(Message::class)->findBy([], ['id' => 'DESC']);
        if (count($messages) != 1) {
            throw new \Exception(sprintf('Invalid message number %d, should be 1', count($messages)));
        }
        $message = $messages[0];

        if ($expected['state'] !== $message->getState()) {
            throw new Exception(sprintf('State "%s" in message is invalid, should be "%s"', $message->getState(), $expected['state']));
        }
        if ($expected['title'] !== $message->getTitle()) {
            throw new Exception(sprintf('Title "%s" in message is invalid, should be "%s"', $message->getTitle(), $expected['title']));
        }
        if ($expected['namespace'] !== $message->getNamespace()) {
            throw new Exception(sprintf('Namespace "%s" in message is invalid, should be "%s"', $message->getNamespace(), $expected['namespace']));
        }
        if ($expected['payload'] !== $message->getPayload()) {
            throw new Exception(sprintf('Payload "%s" in message is invalid, should be "%s"', $message->getPayload(), $expected['payload']));
        }
        $partials = implode(';', $message->getPartials());
        if ($expected['partials'] !== $partials) {
            throw new Exception(sprintf('Partials "%s" in message is invalid, should be "%s"', $partials, $expected['partials']));
        }
    }
}
