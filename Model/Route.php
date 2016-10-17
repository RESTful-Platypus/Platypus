<?php
namespace Platypus\Model;

class Route {
	/**
	 * @type String
	 */
	private $class;

	/**
	 * @type String
	 */
	private $method;

	/**
	 * @type String
	 */
	private $provides;

	/**
	 * @type Array
	 */
	private $params;

	/**
	 * @type \Platypus\Annotations
	 */
	private $resource;

	public function __constuct() {}

	public function setClass(string $class): Route {
		$this->class = $class;

		return $this;
	}

	public function getClass() {
		return $this->class;
	}

	public function setMethod(string $method): Route {
		$this->method = $method;

		return $this;
	}

	public function getMethod() {
		return $this->method;
	}

	public function setProvides(string $provides): Route {
		$this->provides = $provides;

		return $this;
	}

	public function getProvides() {
		return $this->provides;
	}

	public function setParams(array $params): Route {
		$this->params = $params;

		return $this;
	}

	public function getParams() {
		return $this->params;
	}

	public function setResource(\Platypus\Annotations $resource): Route {
		$this->resource = $resource;

		return $this;
	}

	public function getResource() {
		return $this->resource;
	}
}
