<?php
namespace Platypus\Resources;

class Error {
	public function get($code, $message) {
		return [
			'code' => $code,
			'message' => $message
		];
	}
}
