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

/**
 * Framework
 *
 * @package   com.allinora.simmple.framework
 * @author    Atif Ghaffar <atif.ghaffar@gmail.com>
 * @copyright 2012-2014 Atif Ghaffar
 * @license   http://www.opensource.org/licenses/MIT The MIT License
 */



# functions that are usable everywhere within this namespace. 

function debug($str){
	print "DEBUG: $str<br>\n";

}

function log($str){
	print "Log: $str<br>\n";

}
function syslog($str){
	print "Syslog: $str<br>\n";
}

# End of functions


# Autoload for controllers
function framework_class_loader($className) {
	if (class_exists($className)){
		return;
	}

	if (file_exists(ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower($className) . '.php')) {
		require_once(ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower($className) . '.php');
	} else {
		/* Error Generation Code Here */
	}
	return false;
}


spl_autoload_register('Allinora\Simple\framework_class_loader');

// Check various settings before loading everything

if (!defined('ROOT')){
	if (isset($_SERVER['SCRIPT_FILENAME'])){
		$project_root = realpath(dirname($_SERVER['SCRIPT_FILENAME']) . "/../");
		define('ROOT', $project_root);
	} else {
		die("Please define a constant called ROOT that should point to the root of your project directory");
	}
}


// This is final class. It cannot be extended by a subclass.
final class Framework {

	protected $url;
	protected $lang;
	protected $routes;


	function __construct($url){
		$config_file = ROOT . DS . 'config' . 	DS . 'config.php';
		
		if (file_exists($config_file)){
			require_once($config_file);
		}
		
		
		$this->url = $url;

		$this->setReporting();
		$this->removeMagicQuotes();
		$this->unregisterGlobals();


		$this->setLanguage();
		$this->routes = $this->getRoutes();
		$this->callHook();



	}


	function setReporting() {
		if (DEVELOPMENT_ENVIRONMENT == true) {
			error_reporting(E_ERROR);
			ini_set('display_errors','On');
			ini_set('log_errors', 'On');
			ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');
		} else {
			error_reporting(E_ERROR);
			ini_set('display_errors','Off');
			ini_set('log_errors', 'Off');
			ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');
		}
	}

	/** Takes care of setting the LANG cst **/
	/* Languages should be defined in the config such as 
	define("LANGUAGES", "en|de|fr");
	*/

	function setLanguage () {
		if (!defined('LANGUAGES')){
			define('LANGUAGES', 'en');
		}	
		$this->i18nURL();
		if (!defined('LANG')){
			if (!$this->lang){
				$this->lang = 'en';
			}
			define('LANG', $this->lang);
		}
	}
	function cleanURL(){
		// Do whatever need to clean the url..
		$this->url=preg_replace("@^/*@", '', $this->url);
		$this->url=preg_replace("@/*$@", '', $this->url);

	}



	function i18nURL(){
		if (!defined('LANGUAGES')){
			return;
		}
		if (isset($this->lang)){
			return;
		}

		// Get the language from the url
		//print "URL:" . __LINE__ . " " . $this->url .  "<br>";

		$this->cleanURL();
		//print "URL:" . __LINE__ . " " . $this->url .  "<br>";

		if (preg_match("@^(" . LANGUAGES . ")$@", $this->url, $x)){
			// This should match http://domain.com/en
			$this->lang=$x[1];
		} elseif (preg_match("@^(" . LANGUAGES . ")/@", $this->url, $x)){
			// This should match http://domain.com/en/something/
			$this->lang=$x[1];
		}
		if (isset($this->lang)){
			$this->url=preg_replace("@^(" . LANGUAGES . ")/*@", "", $this->url);
			// Now the url is without the language so 
			// /en/something/do/x becomes
			// /something/do/x 
		}
		//print "URL:" . __LINE__ . " " . $this->url .  "<br>";

	}


	/** Check for Magic Quotes and remove them **/

	function stripSlashesDeep($value) {
		$value = is_array($value) ? array_map('stripSlashesDeep', $value) : stripslashes($value);
		return $value;
	}

	function removeMagicQuotes() {
		if ( get_magic_quotes_gpc() ) {
			die("Cannot work with magic_quotes_gpc. Please disable");
		}
	}

	/** Check register globals and remove them **/

	function unregisterGlobals() {
	    if (ini_get('register_globals')) {
			die("Cannot function with register_globals on ");
        }
	}

	/** Main Call Function **/

	function callHook() {
		global $default; // Should take care of this later.
		$queryString = array();

		if (!isset($this->url)) {

			$controller = $this->routes['default']['controller'];
			$action = $this->routes['default']['action'];
		} else {
			//print "URL: " . $this->url . "<br>";
			$this->routeURL(); // Replace the URL with something else based on preg_matches defined in the routing
			//print "URL: " . $this->url . "<br>";
			$urlArray = array();
			$urlArray = explode("/",$this->url);
			$controller = $urlArray[0];
			array_shift($urlArray); // Pop the first element
			if (!$controller){
				$controller="index";  // Default Controller
			}

			if (isset($urlArray[0])) {
				$action = $urlArray[0];
				array_shift($urlArray); // pop the second element
			} else {
				$action = 'index'; // Default Action
			}
			$queryString = $urlArray;
		}

		$controllerName = ucfirst($controller).'Controller';
		//print "Calling $controllerName with $controller and $action at ";
		$this->runControllerAction($controller, $action, $queryString, $controllerName, $action);

	}

	private function runControllerAction($controller, $action, $queryString){
		$controllerName =  ucfirst($controller).'Controller';
		$actionName=$action . "Action"; // This is done to avoid clash with reserved function names like list();
		$dispatch = new $controllerName($controller,$action, $_REQUEST);

		// Do not propogate POST. Force to use the this->request
		unset($_POST);

		// Call the init stuff
		call_user_func_array(array($dispatch, "beforeAction"), $queryString);

		// Dont care if the action does not exist. Let the __call handle it
		call_user_func_array(array($dispatch, $actionName), $queryString);

		// Call the cleanup stuff
		call_user_func_array(array($dispatch, "afterAction"), $queryString);


		if ($dispatch->render) {
			$_content=$dispatch->getContents();
			// Check if there is a global function in the main namespace called _app_contentHook
			if (function_exists("\_app_contentHook")){
				print \_app_contentHook($_content);
			} else {
				print $_content;
			}
		}
	}


	/** Routing **/

	private function routeURL() {
		$routing=$this->routes["routing"];
		//print "<pre>" . print_r($routing, true) . "</pre>";

		foreach ( $routing as $pattern => $result ) {
			//print "Pattern is $pattern and Result is $result<br>";


			if ( preg_match( $pattern, $this->url , $x) ) {
				$this->url= preg_replace( $pattern, $result, $this->url);
			}
		}

	}

	function getRoutes(){
		$routing_file = ROOT . DS . 'config' . DS . 'routing.php';
		$ret = array();
		if (file_exists($routing_file)){
			include_once($routing_file);
			$router = new Router;
			$ret['default']['controller'] = $router->default['controller'];
			$ret['defaul']['action'] = $router->default['action'];
			$ret['routing'] = $router->routing;
		} else {
			$ret['default']['controller'] = 'index';
			$ret['default']['action'] = 'index';
			$ret['routing'] = array();
		}
		return $ret;
	}


	function __call($method, $args){
		print "Dont know how to do $method<br>";

	}
}
