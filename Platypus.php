<?php
namespace Platypus;

class Platypus {
	/**
	 * @param [\Platypus\Annotation]
	 */
	private $resources = [];

	public function __construct() {
		spl_autoload_register([$this, '__autoload']);

		$this->uri = strtolower(rtrim($_SERVER['REQUEST_URI'], '/'));
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->contentType = strtolower(isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '*/*');
		$this->accept = array_map('strtolower', (explode(',', $_SERVER['HTTP_ACCEPT'])));
		$this->parser = new \Rize\UriTemplate\UriTemplate;
	}

	/**
	 * Include a class
	 *
	 * @param string $name Name
	 */
	public function __autoload(string $name) {
		$class = str_replace('\\', '/', $name);
		$class = str_replace('Platypus/', '', $class);

		include($class.'.php');
	}

	/**
	 * Add a resource by name
	 *
	 * @param string $name Name
	 */
	public function add(string $name) {
		$this->resources[] = new Annotations($name);
	}

	private function getRoute(): Model\Route {
		foreach($this->resources as $resource) {
			$class = $resource->getClass();

			if(empty($class->getAnnotation('self'))) {
				continue;
			}

			foreach($class->getAnnotation('self') as $self) {
				$params = $this->parser->extract($self, $this->uri, true);

				if(isset($params)) {
					break;
				}
			}

			if(!isset($params)) {
				continue;
			}

			$res_method = null;

			foreach($resource->getMethods() as $method) {
				if(empty($method->getAnnotation('method')) or !in_array($this->method, $method->getAnnotation('method'))) {
					continue;
				}

				$res_method = $method;

				// Break on first @provides-matching method
				// `*/*` may can be removed since CURL and all browsers send it as a
				// fallback. But we could add it to `$this->accept`.
				if(empty($method->getAnnotation('provides'))) {
					continue;
				}

				// print_r(['name'=>$method->getName(), 'provides'=>$method->getAnnotation('provides')]);

				if(!empty(array_intersect($this->accept, $method->getAnnotation('provides')))) {
					break 2;
				}

				if(in_array('*/*', $method->getAnnotation('provides'))) {
					continue;
				}
			}

			if($res_method != null) {
				break;
			}
		}

		$route = new Model\Route;

		if(!isset($res_method)) {
			$route->setClass('\Platypus\Resources\Error');
			$route->setMethod('get');
			$route->setProvides('application/json');
			$route->setParams([404, 'not found']);
		} else {
			$provides = !empty($res_method->getAnnotation('provides')) ? $res_method->getAnnotation('provides') : ['*/*'];
			$provides = in_array($this->contentType, $provides) ? $this->contentType : end($provides);

			$route->setClass($resource->getClass()->getName());
			$route->setMethod($res_method->getName());
			$route->setProvides($provides);
			$route->setParams($params);
			$route->setResource($resource);
		}

		return $route;
	}

	/**
	 * TODO: caching
	 */
	private function getRelations(array $annotations, array $links): array {
		$uris = [];

		foreach($links as $name => &$link) {
			// Every not-defined annotation should be set to `self`. This is usefull
			// for not redeclaring `next` or `prev`.
			$name = isset($annotations[$name]) ? $name : 'self';

			if(!isset($uris[$name])) {
				$annotation = end($annotations[$name]);

				// The annotation can be a URI-template or a class.
				if(class_exists($annotation)) {
					$annotation = new Annotations($annotation);
					$annotation = $annotation->getClass();
					$annotation = $annotation->getAnnotation('self');
					$annotation = end($annotation);
				}

				$uris[$name] = $annotation;
			}

			$link = ['href' => $this->parser->expand($uris[$name], $link)];
		}

		ksort($links);

		return $links;
	}

	/**
	 * Execute
	 */
	public function go(): string {
		$route = $this->getRoute();
		$key = $route->getResource() == null ? 'error' : 'result';
		$encoder = new Encoder;

		try {
			$class = $route->getClass();
			$instance = new $class;
			$result = [$key => $instance->{$route->getMethod()}(...array_values($route->getParams()))];
		} catch(\Exception $e) {
			$key = 'error';
			$this->contentType = 'application/json';
			$instance = new Resources\Error;
			$result = [$key => $instance->get($e->getCode(), $e->getMessage())];
		}

		// Convert object to array
		if(is_object($result[$key])) {
			$result[$key] = get_object_vars($result[$key]);
		} elseif(is_array($result[$key]) and !empty($result[$key]) and is_object(reset($result[$key]))) {
			$result[$key] = array_map('get_object_vars', $result[$key]);
		}

		header('Content-Type: '.$route->getProvides());

		if(is_string($result[$key])) {
			return $result[$key];
		}

		if(isset($result['error']) or $route->getResource() == null) {
			return $encoder->encode($result, $route->getProvides()).PHP_EOL;
		}

		if(!isset($result['result']['_links'])) {
			$result['result']['_links'] = [];
		}

		if(!isset($result['result']['_links']['self'])) {
			$result['result']['_links']['self'] = $route->getParams();
		}

		$annotations = $route->getResource()->getClass()->getAnnotations();
		$result['_links'] = $this->getRelations($annotations, $result['result']['_links']);

		unset($result['result']['_links']);

		foreach($result['result'] as &$item) {
			if(!isset($item['_links'])) {
				continue;
			}

			if(!isset($annotations['resource'])) {
				continue;
			}

			// Alternatively the `item`s `_links` could use `resource` instead of
			// `self`.
			$annotations['self'] = $annotations['resource'];
			$item['_links'] = $this->getRelations($annotations, $item['_links']);
		}

		ksort($result);

		return $encoder->encode($result, $route->getProvides()).PHP_EOL;
	}
}
