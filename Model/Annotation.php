<?php
namespace Platypus\Model;

class Annotation {
	/**
	 * @type String
	 */
	private $name = '';

	/**
	 * @type Array[Annotation]
	 */
	private $methods = [];

	/**
	 * @type Array[String]
	 */
	private $annotations = [];

	public function __constuct() {}

	public function setName(string $name): Annotation {
		$this->name = $name;

		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	public function addMethods(array $methods): Annotation {
		foreach($methods as $method) {
			$this->addMethod($method);
		}

		return $this;
	}

	public function getMethods(): array {
		return $this->methods;
	}

	public function addMethod(Annotation $method): Annotation {
		$this->methods[] = $method;

		return $this;
	}

	public function addAnnotations(array $annotations) {
		foreach($annotations as $name => $annotation) {
			$this->addAnnotation($name, $annotation);
		}

		return $this;
	}

	public function getAnnotations(): array {
		return $this->annotations;
	}

	public function addAnnotation(string $name, array $annotation): Annotation {
		$this->annotations[$name] = $annotation;

		return $this;
	}

	public function getAnnotation(string $name): array {
		if(!isset($this->annotations[$name])) {
			return [];
		}

		return $this->annotations[$name];
	}
}
