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
    public function testAutoSetupMail()
    {
        $attachments = [
            'file' => 'file1',
            'options' => [
                'as' => 'file.pdf'
            ]
        ];

        $settings = new MailSettings([
            'to'            => [['email' => 'foo@bar.com', 'name' => 'FooBar']],
            'view'          => 'hello.world',
            'subject'       => 'Subject',
            'attachments'   => $attachments,
        ]);

        $this->assertNotNull(($mail = new Mail($settings))->options);

        $this->assertNull($mail->view);
        $this->assertSame('Subject', $mail->subject);
        $this->assertSame([['name' => 'FooBar', 'address' => 'foo@bar.com']], $mail->to);

        $this->assertSame([$attachments], $mail->attachments);
    }

    /**
     * Test set options
     */
    public function testManualSetupMail()
    {
        $mail = new Mail();

        $attachments =  [
            [
                'file' => 'test',
                'options' => [
                    'as'    => 'file.pdf',
                ]
            ],
            [
                'file' => 'test2',
                'options' => [
                    'as'    => 'file2.pdf',
                ]
            ],
        ];

        $settings = new MailSettings([
            'to'            => [['email' => 'foo@bar.com', 'name' => 'FooBar']],
            'subject'       => 'Subject',
            'textView'      => 'text.view',
            'view'          => 'normal.view',
            'attachments'   => $attachments,
            'markdown'      => false,
        ]);

        $mail->setOptions($settings);
        $mail->setupMail();

        $this->assertSame('Subject', $mail->options->subject);
        $this->assertSame([['email' => 'foo@bar.com', 'name' => 'FooBar']], $mail->options->to);

        $this->assertSame('normal.view', $mail->view);
        $this->assertSame('text.view', $mail->textView);

        $this->assertSame($attachments, $mail->attachments);
    }
}
