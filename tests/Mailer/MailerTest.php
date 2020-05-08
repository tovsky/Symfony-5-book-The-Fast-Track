<?php


namespace App\Tests\Mailer;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MailerTest extends WebTestCase
{
        public function testMailerAssertions()
        {
            $client = static::createClient();
            $client->request('GET', '/');

            $this->assertResponseIsSuccessful();

//            $this->assertEmailCount(1);
//            $event = $this->getMailerEvent(0);
//            $this->assertEmailIsQueued($event);
//
//            $email = $this->getMailerMessage(0);
//            $this->assertEmailHeaderSame($email, 'To', 'fabien@example.com');
//            $this->assertEmailTextBodyContains($email, 'Bar');
//            $this->assertEmailAttachmentCount($email, 1);

        }
}