<?php

namespace App\Models;

use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use Nette\Mail\FallbackMailer;
use Nette\Mail\SendmailMailer;

class EmailModel
{
    public Message $mail;
    public SendmailMailer $mailer;

    public function __construct()
    {
        $this->mail = new Message;
        $this->mailer = new SendmailMailer;
    }

    public function sendFromWeb($values)
    {
        $this->mail->setFrom($values['email'])
            //->addTo('martin.maly.1977@gmail.com')
            ->setSubject($values['subject'])
            ->setHtmlBody(
                "<h2 style='color:red'>Test Email form localhost</h2>
                 <br>
                 <p>".$values['body']."</p>"
            );
        $this->mailer->send($this->mail);       
        exit;
    }

    public function sendAutomaticReply($values)
    {
        $this->mail->setFrom('automatic_reply@martinm.cz') //not working due to the SMTP setting, if goes from gmail
            ->addTo($values['email'])
            ->setSubject($values['subject'])
            ->setHtmlBody(
                "<h2 style='color:red'>Automatic reply Test</h2>
                <br>
                <p>".$values['body']."</p>
                <br>
                <p>S pozdravem</p>
                <p style='color:blue'>Martin</p>"
            );
        $this->mailer->send($this->mail);
        exit;
    }
}
