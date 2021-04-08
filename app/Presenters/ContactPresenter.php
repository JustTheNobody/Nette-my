<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Http\Request;
use Nette\Mail\Message;
use Tracy\Debugger;

use App\Forms\ContactForm;
use App\Models\EmailModel;
use Nette\Mail\SendmailMailer;
use Nette\Application\UI\Presenter;
use Tracy\Bridges\Nette\MailSender;

final class ContactPresenter extends Presenter
{

    public ContactForm $form;
    public Request $request;

    public function __construct(ContactForm $form, Request $request)
    {
        $this->form = $form;
        $this->request = $request;
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

    public function contactFormSucces($contactForm)
    {
        try {
            //mail from web
            $mail = new EmailModel;
            $mail->sendFromWeb($this->request->getPost());
            //confirm mail to sender
            //$automaticReply = new EmailModel;
            //$automaticReply->sendAutomaticReply($this->request->getPost());

            $this->flashMessage('Your message has been sended.', 'success'); 
            $this->flashMessage('We have sended you a confirmation email.', 'info'); 
            $this->redirect('Homepage:default');  

        } catch (\Exception $e) {
            $contactForm->addError($e->getMessage());
            Debugger::log($e, 'mailexception');
            $this->flashMessage('Something went wrong', 'fail');  
            $this->redirect('Contact:default');         
        }                             
    }
}