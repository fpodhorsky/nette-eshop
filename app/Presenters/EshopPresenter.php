<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


class EshopPresenter extends Nette\Application\UI\Presenter
{
	private Nette\Database\Explorer $database;

	public function __construct(Nette\Database\Explorer $database)
	{
		$this->database = $database;
	}


	public function renderProducts(): void
	{
		$this->template->products = $this->database->table('products')
			->order('price DESC');
	}



	public function rendershowProduct(int $productId): void
	{
	$product = $this->database->table('products')->get($productId);
	if (!$product) {
		$this->error('Page not found');
	}

	$this->template->product = $product;
	}


	protected function createComponentProductForm(): Form
	{
	$form = new Form;
	$form->addText('title', 'Title:')
		->setRequired();

	$form->addTextArea('description', 'Decription:')
		->setRequired();

	$form->addText('price', 'Price:')
		->setRequired();

	$availability = [
					"Unavailable"=>"Unavailable",
					"Available"=>"Available"
					]; 
	$form->addSelect('availability', 'Availability:', $availability);
		
	$form->addUpload('picture', 'Picture:')
		->addRule(Form::IMAGE, 'File must be JPEG, PNG nebo GIF.')
		->setRequired();
		

	$form->addSubmit('send', 'Send');
	$form->onSuccess[] = [$this, 'productFormSucceeded'];

	return $form;
	}	


	public function productFormSucceeded(Form $form, array $values): void
	{
		if (!$this->getUser()->isLoggedIn()) {
		$this->error('For creating or editing you have to be logged in.');
	}

	

  	if($values['picture'])
  	{
    $file_ext=strtolower(mb_substr($values['picture']->getSanitizedName(), strrpos($values['picture']->getSanitizedName(), ".")));
    $file_name = uniqid(rand(0,20), TRUE) . $file_ext;
    $path = 'images/uploads/' . $file_name;
    $values['picture']->move($path);
	}

	$productId = $this->getParameter('productId');

	if ($productId) {
		$product = $this->database->table('products')->get($productId);
		$product->update($values);
	} else {
		$product = $this->database->table('products')->insert($values);
	}


	$this->redirect('products');
	}
}