<?php
namespace Laranix\Tests\Support\IO;

use Laranix\Support\Mail\Mail;
use Laranix\Support\Mail\MailSettings;
use Laranix\Tests\LaranixTestCase;

class MailTest extends LaranixTestCase
{
    /**
     * Test construction with params
     */
    public function testCanConstructWithParameters()
    {
        $settings = new MailSettings([
            'to'            => [['email' => 'foo@bar.com', 'name' => 'FooBar']],
            'view'          => 'Hello World',
            'subject'       => 'Subject',
        ]);

        $this->assertNotNull(($mail = new Mail($settings))->options);

        $this->assertSame('Subject', $mail->subject);
        $this->assertSame([['name' => 'FooBar', 'address' => 'foo@bar.com']], $mail->to);
    }

    /**
     * Test set options
     */
    public function testCallSetOptions()
    {
        $mail = new Mail();

        $settings = new MailSettings([
            'to'            => [['email' => 'foo@bar.com', 'name' => 'FooBar']],
            'view'          => 'Hello World',
            'subject'       => 'Subject',
        ]);

        $mail->setOptions($settings);

        $this->assertSame('Subject', $mail->options->subject);
        $this->assertSame([['email' => 'foo@bar.com', 'name' => 'FooBar']], $mail->options->to);
    }

    /**
     * Test set up mail
     */
    public function testCallSetupMail()
    {
        $mail = new Mail();

        $settings = new MailSettings([
            'to'            => [['email' => 'foo@bar.com', 'name' => 'FooBar']],
            'view'          => 'Hello World',
            'subject'       => 'Subject',
        ]);

        $mail->setOptions($settings);
        $mail->setupMail();

        $this->assertSame('Subject', $mail->subject);
        $this->assertSame([['name' => 'FooBar', 'address' => 'foo@bar.com']], $mail->to);
    }
}
