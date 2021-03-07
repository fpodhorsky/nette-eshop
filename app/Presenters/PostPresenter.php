<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


class PostPresenter extends Nette\Application\UI\Presenter
{
	private Nette\Database\Explorer $database;

	public function __construct(Nette\Database\Explorer $database)
	{
		$this->database = $database;
	}


	public function renderShow(int $postId): void
	{
	$post = $this->database->table('posts')->get($postId);
	if (!$post) {
		$this->error('Page not found');
	}

	$this->template->post = $post;
	$this->template->post = $post;
	$this->template->comments = $post
	->related('comments')
	->order('created_at');
	}


	protected function createComponentCommentForm(): Form
	{
	$form = new Form; 

	$form->addText('name', 'Name:')
		->setRequired();

	$form->addText('surname', 'Surname:')
		->setRequired();

	$form->addEmail('email', 'E-mail:');

	$form->addTextArea('content', 'Comment:')
		->setRequired();

	$form->addSubmit('send', 'Publish');

	$form->onSuccess[] = [$this, 'commentFormSucceeded'];

	return $form;
	}


	public function commentFormSucceeded(\stdClass $values): void
	{
	$postId = $this->getParameter('postId');

	$this->database->table('comments')->insert([
		'post_id' => $postId,
		'name' => $values->name,
		'surname' => $values->surname,
		'email' => $values->email,
		'content' => $values->content,
	]);
	}


	protected function createComponentPostForm(): Form
	{
	$form = new Form;
	$form->addText('title', 'Title:')
		->setRequired();
	$form->addTextArea('content', 'Content:')
		->setRequired();
	$form->addText('author', 'Author')
		->setRequired();

	$form->addSubmit('send', 'Save and publish');
	$form->onSuccess[] = [$this, 'postFormSucceeded'];

	return $form;
	}


	public function postFormSucceeded(Form $form, array $values): void
	{
		if (!$this->getUser()->isLoggedIn()) {
		$this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
	}

	$postId = $this->getParameter('postId');

	if ($postId) {
		$post = $this->database->table('posts')->get($postId);
		$post->update($values);
	} else {
		$post = $this->database->table('posts')->insert($values);
	}

	$this->redirect('show', $post->id);
	}


	public function actionEdit(int $postId): void
	{
	$post = $this->database->table('posts')->get($postId);
	if (!$post && !$this->getUser()->isLoggedIn()) {
		$this->error('Article not found');
	}
		$this['postForm']->setDefaults($post->toArray());
		$this->redirect('Sign:in');
	}


	public function actionCreate(): void
	{
	if (!$this->getUser()->isLoggedIn()) {
		$this->redirect('Sign:in');
	}
	}

}

