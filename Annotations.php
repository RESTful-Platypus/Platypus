<?php
namespace Platypus;

class Annotations {
	/**
	 * @param Array
	 */
	private $methods;

	/**
	 * @param \ReflectionClass
	 */
	private $reflection;

	/**
	 * @param string $name Name of a class
	 */
	public function __construct(string $name) {
		$this->methods = [];
		$this->reflection = new \ReflectionClass($name);
	}

	/**
	 * Parse a DocComment
	 *
	 * @param string $comment DocComment
	 * @param bool $stripslashes Escape-slashes entfernen?
	 * @return array
	 */
	private function parse(string $comment, bool $stripslashes = true): array {
		$fields = [];

		foreach(explode(PHP_EOL, $comment) as $line) {
			if(!preg_match('/^\s*\* @([a-z]+?) (.+?)$/i', $line, $field)) {
				continue;
			}

			array_shift($field);
			list($key, $value) = $field;

			if(!isset($fields[$key])) {
				$fields[$key] = [];
			}

			$fields[$key][] = $stripslashes ? stripslashes($value) : $value;
		}

		return $fields;
	}

	public function getName(): string {
		return $this->reflection->getName();
	}

	/**
	 * TODO: Comment
	 */
	public function getClass(): Model\Annotation {
		$comment = $this->reflection->getDocComment();
		$annotations = $this->parse($comment);
		$annotation = new Model\Annotation;

		$annotation->setName($this->reflection->getName());
		$annotation->addMethods(iterator_to_array($this->getMethods()));
		$annotation->addAnnotations($annotations);

		return $annotation;
	}

	/**
	 * TODO: Comment
	 */
	public function getMethods(int $flag = \ReflectionMethod::IS_PUBLIC): \Generator {
		/*
		if(!empty($this->methods)) {
			foreach($this->methods as $method) {
				yield($method);
			}

			return;
		}
		*/

		$methods = $this->reflection->getMethods($flag);

		foreach($methods as $method) {
			$comment = $method->getDocComment();
			$annotations = $this->parse($comment);
			$annotation = new Model\Annotation;

			$annotation->setName($method->name);
			$annotation->addAnnotations($annotations);

			yield $annotation;

			// $this->methods[] = (object) [
			// 	'name' => $method->name,
			// 	'annotations' => $annotations
			// ];
			// print_r(end($this->methods));

			// yield end($this->methods);
		}
	}
}
