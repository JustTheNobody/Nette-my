<?php

declare(strict_types=1);

namespace App\Presenters;

use Tracy\Debugger;
use Nette\Http\Request;

use App\Forms\ContactForm;
use App\Models\EmailModel;
use App\Models\StatisticModel;
use Nette\Application\UI\Presenter;

final class ContactPresenter extends Presenter
{

    public ContactForm $form;
    public Request $request;
    public StatisticModel $statistic;
    public EmailModel $mail;

    public function __construct(
        ContactForm $form,
        Request $request,
        StatisticModel $statistic,
        EmailModel $mail
    ) {
        $this->form = $form;
        $this->request = $request;
        $this->statistic = $statistic;
        $this->mail = $mail;
    }

    public function beforeRender()
    {
        $this->template->title = 'contact'; 
        $this->statistic->saveStatistic();  
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
            $this->mail->sendFromWeb($this->request->getPost());
            
        } catch (\Exception $e) {
            $contactForm->addError($e->getMessage());
            Debugger::log($e, 'mailexception');
            $this->flashMessage('Something went wrong', 'fail');  
            $this->redirect('Contact:default');         
        }
        $this->flashMessage('Your message has been sended.', 'success'); 
        //$this->flashMessage('We have sended you a confirmation email.', 'info'); 
        $this->redirect('Homepage:default');  
    }
}