<?php

namespace App\Mail;

class Message {

	protected $mailer;

	public function __construct($mailer) {
		$this->mailer = $mailer;
	}

	public function to($address) {
		$this->mailer->addAddress($address);
	}

	public function subject($subject) {
		$this->mailer->Subject = $subject;
	}

	public function body($body) {
		// or ->Body
		$this->mailer->Body = $body;
	}

}