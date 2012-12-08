<?php
/*
* 
* Jul 20, 2012
* GBK
* 2:34:57 PM
* AgudaZaric
* PkController.php
*/

class PkController {
	
	function render($template ,$data) {
		$this->data = $data;
		include_once PrintHack($template);
	}
	
	function run() {
		$args = func_get_args();
		if (!empty($args[0])) {
			$action = $args[0];
			$reflectClass = new ReflectionClass(get_class($this));
			if ($reflectClass->hasMethod('action'.ucfirst($action))) {
				$rfm = new ReflectionMethod($reflectClass->name,'action'.ucfirst($action));
				$rfm->invoke($reflectClass->newInstance(), $rfm->name);
			}
		}
	}
}