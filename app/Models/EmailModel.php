<?php

namespace App\Models;

use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use Nette\Database\Explorer;
use Nette\Mail\FallbackMailer;
use Nette\Mail\SendmailMailer;

class EmailModel
{
    protected Explorer $database;

    public function __construct(Explorer $database) 
    {
        $this->database = $database;
    }

    public function sendFromWeb($values)
    {
        $this->database->query(
            'INSERT INTO email ?', [
            'from' => $values['email'],
            'message' => $values['body'],
            'subject' => $values['subject']]
        );
        // return auto-increment of the inserted row
        $messageId =  $this->database->getInsertId();

        $mail1 = new Message;
        $mail1
            ->setFrom($values['email'])
            ->addTo('martin.maly.1977@gmail.com')
            ->setSubject($values['subject'])
            ->setHtmlBody(
                "<h2 style='color:red'>Test Email form localhost</h2>
                 <br>
                 <p>".$values['body']."</p>
                 <br>Message saved to DB, the record id is: " . $messageId
            );

        $mailer1 = new SendmailMailer;
        $mailer1->send($mail1);  
        
        $this->sendAutomaticReply($values);
        return;
    }

    public function sendAutomaticReply($values)
    {
        $mail2 = new Message;
        $mail2
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
        $mailer2 = new SendmailMailer;
        $mailer2->send($mail2);
        return;
    }

    public function sendBlogPost($values, $type)
    {
        $mail = new Message;
        $mail
            ->setFrom('martin.maly.1977@gmail.com')
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
        $mailer = new SendmailMailer;
        $mailer->send($mail);
        return;
    }

    public function sendConfirmationLink($link, $email) 
    {
        $mail = new Message;
        $mail
            ->setFrom('dontreply@martinm.cz')
            ->addTo($email)
            ->setSubject('Confirm your email address for martinm.cz')
            ->setHtmlBody(
                "<h2 style='color:red'>Automatic msg from MartinM.cz</h2>
                <br>                
                <p>You have registered at martinm.cz 
                <br>clic at the confirmation link to get all future features of the website:
                <a href='".$link."' target='_blank'>".$link."</a>
                <br>
                <br>
                <p style='color:blue'>Thanks Martin</p>"
            );
        $mailer = new SendmailMailer;
        $mailer->send($mail);
        return;
    }
}
