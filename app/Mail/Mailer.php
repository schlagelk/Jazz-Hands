<?php

namespace App\Mail;

class Mailer {

	protected $mailer;
	protected $view;

	public function __construct($view, $mailer) {
		$this->mailer = $mailer;
		$this->view = $view;
	}

	public function send($response, $template, $data, $callback) {
		$message = new Message($this->mailer);
		$message->body($this->view->render($response, $template, $data));
		call_user_func($callback, $message);

		$this->mailer->send();
	}
}