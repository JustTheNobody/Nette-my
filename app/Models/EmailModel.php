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
        $this->mail
            ->setFrom($values['email'])
            ->addTo('martin.maly.1977@gmail.com')
            ->setSubject($values['subject'])
            ->setHtmlBody(
                "<h2 style='color:red'>Test Email form localhost</h2>
                 <br>
                 <p>".$values['body']."</p>"
            );
        $this->mailer->send($this->mail);  
        
        $this->sendAutomaticReply($values);
        return;
    }

    public function sendAutomaticReply($values)
    {
        $this->mail
            ->setFrom('automatic_reply@martinm.cz')
            ->addTo($values['email'])
            ->setSubject($values['subject'])
            ->setHtmlBody(
                "<h2 style='color:red'>Automatic reply from MartinM.cz</h2>
                <br>                
                <p>Your message was: <br>".$values['body']."</p>
                <br>
                <p>Thank you for your message</p>
                <p>With regards</p>
                <p style='color:blue'>Martin</p>"
            );
        $this->mailer->send($this->mail);
        return;
    }

    public function sendBlogPost($values, $type)
    {
        $this->mail
            ->setFrom('automatic@martinm.cz')
            ->addTo('martin.maly.1977@gmail.com')
            ->setSubject('New Blog Posted!')
            ->setHtmlBody(
                "<h2 style='color:red'>Automatic msg from MartinM.cz</h2>
                <br>                
                <p>New " . $type . " has been posted: 
                <br>title:
                ".($values->title)??$values->title."
                <br>
                <br>" . $values->content . "</p>
                <br>
                <p style='color:blue'>Martin</p>"
            );
        $this->mailer->send($this->mail);
        return;
    }
}
