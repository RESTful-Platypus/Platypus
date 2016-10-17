# Platypus

Platypus is a simple Framework written in PHP for reusing existing Objects as a base for REST-APIs. It supports automatic generation of HATEOAS-links and makes use of annotations - so (in most cases) the code can stay the same.

A *very* basic example could look like this:

```php
<?php
/**
 * @self /user/{name}
 */
class User {
	/**
	 * @method get
	 */
	public function get($name) {
		return ['name' => $name];
	}
}
```

More at [restful-platyp.us](http://restful-platyp.us/).
