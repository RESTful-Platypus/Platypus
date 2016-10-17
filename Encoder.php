<?php
namespace Platypus;

class Encoder {
	private $types;
	private $methods;

	public function __construct() {
		$this->annotations = new Annotations(get_class());
	}

	public function encode(array $data, string $type): string {
		// TODO
		return json_encode($data);

		foreach($this->annotations->getMethods() as $method) {
			if(!isset($method->annotations['provides'])) {
				continue;
			}

			if(!in_array($type, $method->annotations['provides'])) {
				continue;
			}

			return $this->{$method->name}($data);
		}
	}

	/**
	 * @provides *\/*
	 * @provides application/json
	 */
	public function json(array $data) {
		return json_encode($data, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
	}

	/**
	 * @provides text/xml
	 * @provides application/xml
	 */
	public function xml(array $data) {
		return xmlrpc_encode($data);
	}
}
