<?php

/**
 * This file is part of allinora/simple/framework.
 *
 * (c) Atif Ghaffar <atif.ghaffar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allinora\Simple;

class Helper{

	function __construct(){
	}
	
	function __call($name, $params){
		if (!defined("ROOT")){
			die("ROOT is not defined");
			return;
		}
		$helpers_directory = ROOT . DS . 'application' . DS . 'helpers';
		$helper_file = $helpers_directory . DS . $name . '.php';
		$functionName = $name;

		if (file_exists ($helper_file)){
			include_once($helper_file);
		}
		if (function_exists($name)){
			return call_user_func_array($functionName, $params);
		}
	}

}
