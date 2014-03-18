<?php

namespace App\Presenters;
use Nette\Application\UI\Form;

use Nette,
	App\Model;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{	
        public function handleSignOut()
        {
		$user = $this->getUser();
                if($user->isLoggedIn()){
                    $user->logout();
		    $this->flashMessage('Boli ste odhlásený.');
		    $this->redirect('Homepage:');
                }else{
		    $this->flashMessage('Nikto nie je prihlásený.');
                }
        }
        
        public function beforRender()
        {
                $user = $this->getUser();
                $this->template->user = $user;
        }
        
        protected function createComponentSignInForm()
	{
		$form = new Form;
		$form->addText('email', 'E-mail:')
                     ->setAttribute("placeholder","E-mail")
	             ->setRequired('Prosím vlož svoj prihlasovací e-mail.')
                     ->setDefaultValue("")
                     ->getControlPrototype()->setClass("form-control");

		$form->addPassword('password', 'Heslo:')
                     ->setAttribute("placeholder","heslo")
		     ->setRequired(' Prosím vlož svoje heslo.')
                     ->setDefaultValue("")
                     ->getControlPrototype()->setClass("form-control");

		$form->addCheckbox('remember', ' Zostaň prihlásený.');

		$form->addSubmit('send', 'Login')
                     ->getControlPrototype()->setClass("btn btn-lg btn-primary btn-block");

		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}


	public function signInFormSucceeded($form)
	{
		$values = $form->getValues();
                
                $user = $this->getUser();
                try {
                    $user->login($values->email, $values->password);
                    $user->setExpiration('10 minutes', TRUE);

                } catch (Nette\Security\AuthenticationException $e) {
                    $this->flashMessage($e->getMessage());
                }
                
		if ($values->remember) {
			$this->getUser()->setExpiration('14 days', FALSE);
		} else {
			$this->getUser()->setExpiration('20 minutes', TRUE);
		}

		try {
			$this->getUser()->login($values->email, $values->password);
                        $this->redirect('Homepage:default');

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}
        
        public function createComponentLogoutForm()
        {
            $form2 = new Form;
	    $form2->addSubmit('send', 'Logout')
                 ->getControlPrototype()->setClass("btn btn-lg btn-primary btn-block");
            $form2->onSuccess[] = $this->logoutFormSucceeded;
	    return $form2;
        }
        
        public function logoutFormSucceeded($form)
        {               
                $user = $this->getUser();
                $user->logout();
                $this->redirect('Homepage:default');
        }
        
        public $database;

        function __construct(Nette\Database\Context $database)
        {
            $this->database = $database;
        }
}
