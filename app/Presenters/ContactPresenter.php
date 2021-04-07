<?php

declare(strict_types=1);

namespace App\Presenters;


use Nette\Mail\Message;
use App\Forms\ContactForm;
use App\Models\EmailModel;

use Nette\Mail\SendmailMailer;
use Nette\Application\UI\Presenter;
use Tracy\Bridges\Nette\MailSender;

final class ContactPresenter extends Presenter
{
    public ContactForm $form;

    public function __construct(ContactForm $form)
    {
        $this->form = $form;
    }

    public function beforeRender()
    {
        $this->template->title = 'contact';   
    }

    public function renderDefault()
    {
        //
    }

    public function createComponentContactForm()
    {
        $contactForm = $this->form->renderForm();
        $contactForm->onSuccess[] = [$this, 'contactFormSucces'];
        return $contactForm;

    }

    public function contactFormSucces($contactForm, $values)
    {
        try {
            //mail from web
            $mail = new EmailModel;
            $mail->sendFromWeb($values);
            //confirm mail to sender
            $automaticReply = new EmailModel;
            $automaticReply->sendAutomaticReply($values);

        } catch (\Exception $e) {
            $contactForm->addError($e->getMessage());
            $this->flashMessage('Something went wrong', 'fail');
        }

        $this->flashMessage('Thank you.', 'success');
        $this->flashMessage('Your message has been sended.', 'success');
        $this->flashMessage('We have sended you a confirmation email.', 'info');
        $this->redirect('Homepage:default');
    }
}