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

class Controller  {
	
	protected $_controller;
	protected $_action;
	protected $_request;
	protected $_template;

	public $doNotRenderHeader;
	public $render;

	function __construct($controller, $action, $request=array()) {

		
		$this->_controller = ucfirst($controller);
		$this->_action = $action;
		$this->_request = $request;
		
		$this->doNotRenderHeader = 0;
		$this->render = 1;
		
		$this->_template = new Template();
		$this->_template->init($controller,$action);
		
		$this->set("controller", $controller);
		$this->set("action", $action);
		$this->set("request", $request);
		$this->set("ROOT", ROOT);
		$this->set("TEMPLATES", ROOT . "/templates");
		
		$this->helper = new Helper();
		$this->logger = new Log();
		$this->setWrapperDir();

	}
	function setWrapper($x){
		$this->_template->setWrapper($x);
	}

	function setWrapperDir($x){
		$this->_template->setWrapperDir(ROOT . DS . "application" . DS . "views" . DS . $x);
	}
	
	/* 
	Forward from an action to another action in the same or another controller
	Examples:
	1. Forward to another action in the same controller
	   $this->forward("anotheraction");
	
	2. Forward to an action in another controller
	   $this->forward("somecontroller", "action");
	
	*/
	function forward(){
		$this->render = 0;
	    $numargs = func_num_args();
		$arguments = func_get_args();
		//print $numargs;
		if ($numargs){
			if ($numargs>1){
				$this->performAction(array_shift($arguments), array_shift($arguments), array_shift($arguments), 1);
			} else {
				$this->performAction($this->_controller, array_shift($arguments),array_shift($arguments),10);
			}
		}
	}
	
	/** Secondary Call Function **/
	function performAction($controller,$action,$queryString = null,$render = 0) {
		$controllerFile=ROOT . "/application/controllers/$controller" . "controller.php";
		//print "Trying to load $controllerFile<br>";
		if (file_exists($controllerFile)){
			//print "File exists<br>";
			include_once($controllerFile);
		} else {
			throw new Exception("Controller file: $controllerFile does not exists");
		}
		$controllerName = ucfirst($controller).'Controller';
		$actionName=$action . "Action"; // This is done to avoid clash with reserved function names like list();
		if (class_exists($controllerName)){
			
		} else {
			throw new Exception("ControllerClass: $controllerName does not exists");
		}

		//print "Trying to load $controllerName<br>"; 
		$dispatch = new $controllerName($controller,$action);
		$dispatch->render = $render;
		$dispatch->$actionName($queryString);

		if ($dispatch->render >0) {
				$dispatch->display();
		}

	}
	
	function redirect($controller, $action=null, $params=array()){
		$url = "/" . LANG . "/$controller/$action";
		if (is_array($params) && count($params)){
			$url .= "/?" . http_build_query($params);
		}
		if (headers_sent()){
			print "Redirecting to $url<br>";
			print "Headers already sent. Must redirect via javascript<br>";
		} else {
			header("Location: $url");exit;
		}
		
		exit;
		
		
	}
	
	public function run(&$controller, $action, $q) {
		$controller->$action($q);
		
	}
	function action() {
		return $this->_action;
	}
	
	function controller() {
		return $this->_controller;
	}
	
	function template() {
		return $this->_template;
	}

	// Override the default template
	function setTemplateFile($file){
		$this->_template->setTemplateFile(ROOT . DS . "application" . DS . "views" . DS . $file);
		
	}

	function set($name,$value) {
		$this->_template->set($name,$value);
	}
	function get($name){
		$this->_template->get($name);
	}
	
	function display () {
		$this->_template->render($this->doNotRenderHeader);
	}
	function getContents() {
		return $this->_template->getContents($this->doNotRenderHeader);
	}
	
	function beforeAction(){
	}

	function afterAction(){
		if (isset($this->packages)){
			$this->set("packages", $this->packages);
		}
	}

	function http_post_request($url,$params,$http_params=array()) {
		return $this->http_request($url,'POST', $params, $http_params);
	}

	function http_get_request($url,$params,$http_params=array()) {
		return $this->http_request($url,'GET', $params, $http_params);
	}
	

	function http_request($url, $method, $params, $http_params=array()) {

		// Http stream options
		// See http://www.php.net/manual/en/context.http.php
	    $options = array( 
	          'http' => array( 
	            'method' => $method, 
	            'header' => "Accept-language: en\r\n"
              ) 
        ); 

		if (isset($http_params["timeout"])){
			// This can be used to set a long timeout when called from the CLI based daemon
			$options["http"]["timeout"] = $http_params["timeout"];
		}

		$url .= '?' . http_build_str($params);
	    $context = stream_context_create($options); 
		// print "<pre>" . print_r($url, true) . "</pre>";
	    $response = file_get_contents($url, false, $context);
		if (!$response) {
			return false;
		}
		
		$result = trim($response);
		return $result;
	}

	function getSession($key=null){
		if (!$key){
			if (isset($_SESSION)){
				return $_SESSION;
			}
		}
		
		if (isset($_SESSION)){
			if(isset($_SESSION[$key])){
				return $_SESSION[$key];
			}
		}
	}
	
	function setSession($key, $val){
		$_SESSION[$key] = $val;
	}
	
	function clearSession(){
		(unset)$_SESSION["token"];
		$_SESSION = array();
	}
	
	function getParam($x){
		if(isset($_REQUEST[$x])){
			return $_REQUEST[$x];
		}
	}		
}