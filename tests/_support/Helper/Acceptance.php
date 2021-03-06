<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Exception\ModuleException;
use Codeception\Module\Symfony;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Swift_Message;

/**
 * Class Acceptance
 */
class Acceptance extends \Codeception\Module
{

    public function seeSmsIsSent(): void
    {
        $this->getSymfonyModule()->seeEmailIsSent();
    }

    /**
     * @param Swift_Message $mail
     */
    public function seeUrlInMail($mail): void
    {
        $body = $mail->getBody();
        // http://www.regexguru.com/2008/11/detecting-urls-in-a-block-of-text/
        $result = [];
        preg_match('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $body, $result);

        $this->assertGreaterOrEquals(1, count($result), 'Expected at least one url in mail');
    }

    /**
     * @param string $expected
     * @param Swift_Message $mail
     */
    public function seeInMail(string $expected, $mail): void
    {
        $body = $mail->getBody();
        $this->assertContains($expected, $body);
    }

    /**
     * @param Swift_Message $mail
     *
     * @return string
     */
    public function grabUrlInMail($mail): string
    {
        $body = $mail->getBody();
        // http://www.regexguru.com/2008/11/detecting-urls-in-a-block-of-text/
        $result = [];
        preg_match('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $body, $result);

        return $result[0];
    }

    /**
     * @return string
     */
    public function grabCodeInSms(): string
    {
        $email = $this->grabLastSentEmail();
        $body = $email->getBody();
        preg_match('/\d+/', $body, $matches);
        return end($matches);
    }

    /**
     * @return Swift_Message
     */
    public function grabLastSentEmail(): Swift_Message
    {
        $profile = $this->getProfile();
        $mailCollector = $profile->getCollector('swiftmailer');
        $collectedMessages = $mailCollector->getMessages();
        return end($collectedMessages);
    }

    /**
     * @return Profile
     */
    protected function getProfile()
    {
        $profiler = $this->getSymfonyModule()->grabService('profiler');
        $response = $this->getSymfonyModule()->client->getResponse();
        if (null === $response) {
            $this->fail('You must perform a request before using this method.');
        }
        return $profiler->loadProfileFromResponse($response);
    }

    /**
     * @return Symfony|null
     */
    private function getSymfonyModule(): ?Symfony
    {
        try {
            /** @var Symfony $module */
            $module = $this->getModule('Symfony');
        } catch (ModuleException $e) {
            $this->fail('Where is Symfony module ? ... *cry*');
            $module = null;
        }

        return $module;
    }
}
