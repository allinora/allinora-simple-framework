<?php
namespace Allinora\Simple;

class Helper{

	function __construct(){
	}
	
	function __call($name, $params){
		if (!defined("ROOT")){
			die("ROOT is not defined");
			return;
		}
		$helpers_directory=ROOT . DS . "application" . DS . "helpers";
		$helper_file=$helpers_directory . DS . $name . ".php";
		$functionName=$name;

		//print "Searching for helper $name<br>";

		//print "Loading $helper_file<br>";
		if (file_exists ($helper_file)){
			include_once($helper_file);
		}
		if (function_exists($name)){
			return call_user_func_array($functionName,$params);
		}
	}

}
