<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


class SignPresenter extends Nette\Application\UI\Presenter
{
	protected function createComponentSignInForm(): Form
	{
		$form = new Form;
		$form->addText('username', 'Username:')
			->setRequired('Please insert your username.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please insert your password.');

		$form->addSubmit('send', 'Login');

		$form->onSuccess[] = [$this, 'signInFormSucceeded'];
		return $form;
	}


	public function signInFormSucceeded(Form $form, \stdClass $values): void
	{
	try {
		$this->getUser()->login($values->username, $values->password);
		$this->redirect('Homepage:');

	} catch (Nette\Security\AuthenticationException $e) {
		$form->addError('Wrong username or password.');
	}
	}



	public function actionOut(): void
	{
	$this->getUser()->logout();	
	$this->redirect('Homepage:');
	}
}