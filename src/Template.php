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
class Template {
	
	protected $variables = array();
	protected $_controller;
	protected $_action;


	protected $_wrapper = 'wrapper';
	protected $_wrapperDir;
	


	
	protected $smarty;
	protected $templateFile = null;
	
	protected $helper;
	

	function init($controller, $action) {
		$this->_controller = $controller;
		$this->_action = $action;

		if (isset($this->templateFile) ) {
			return;
		}

		//automatically load the right template from the app views folder
		$ctl = ($controller == null || $controller === '') ? '' : ($this->_controller);
		$_template_file = ROOT . DS . 'application' . DS . 'views' . DS . $ctl . DS . $this->_action . '.html';
		if (file_exists($_template_file)){
			$this->templateFile = $_template_file;
		}
	}

	public function __construct () {
		$this->helper=new Helper();
		
		if (!defined("SMARTY_TEMPLATE_DIR")) {
			// Default template directory 
			define('SMARTY_TEMPLATE_DIR', ROOT.DS."application".DS."views");
		}
		if (!defined('PROJECT_NAME')){
			define('PROJECT_NAME', 'Smarty');
		}
		
		if (!defined("SMARTY_COMPILE_DIR")) {
			define('SMARTY_COMPILE_DIR', sys_get_temp_dir() . '/' . PROJECT_NAME . '/tpl_compiled');
		}
		
		if (!defined("SMARTY_CACHE_DIR")) {
			define('SMARTY_CACHE_DIR', sys_get_temp_dir() . '/' . PROJECT_NAME . '/smarty_cache');
		}
		
		
		if(!defined("SMARTY_LEFT_DELIMETER")){
			define('SMARTY_LEFT_DELIMETER', "<{");
		}
		if(!defined("SMARTY_RIGHT_DELIMETER")){
			define('SMARTY_RIGHT_DELIMETER', "}>");
		}
		
		// Framework smarty plugins directory. This will be added to the list of smarty plugins
		if (!defined('SMARTY_FRAMEWORK_PLUGINS_DIR')){
			define('SMARTY_FRAMEWORK_PLUGINS_DIR', ROOT . DS . "helpers" .DS ."smarty-plugins");
		}

		// YOUR smarty plugins directory. This will be added to the list of smarty plugins
		if (!defined('SMARTY_LOCAL_PLUGINS_DIR')){
			define('SMARTY_LOCAL_PLUGINS_DIR', ROOT . DS . 'application' . DS . 'helpers' . DS . 'smarty-plugins');
		}
	
		
		$this->smarty = new \Smarty();
		
		//useful vars for plugins like media
		$this->smarty->assign('DEVELOPMENT_ENVIRONMENT', DEVELOPMENT_ENVIRONMENT);

		// Define the language
		if (defined('LANG')){
			$this->smarty->assign('lang', LANG);
		}

		// Supress notices.
		$this->smarty->error_reporting = error_reporting() & ~E_NOTICE; 

		// Allow space between delimeters
		$this->smarty->auto_literal = false;
		
		





		$this->smarty->template_dir = SMARTY_TEMPLATE_DIR;
		$this->smarty->compile_dir = SMARTY_COMPILE_DIR;
		$this->smarty->cache_dir = SMARTY_CACHE_DIR;
		$this->smarty->plugins_dir[] = SMARTY_FRAMEWORK_PLUGINS_DIR;
		$this->smarty->plugins_dir[] = SMARTY_LOCAL_PLUGINS_DIR;
		$this->smarty->left_delimiter = SMARTY_LEFT_DELIMETER;
		$this->smarty->right_delimiter = SMARTY_RIGHT_DELIMETER;

		if (DEVELOPMENT_ENVIRONMENT){
			$this->smarty->caching = 0;
			$this->smarty->compile_check = true;
		} else {
			$this->smarty->caching = 0;
			$this->smarty->compile_check = false;

		}		
	}



	/** Set Variables **/

	public function set ($key, $value) {
		$this->smarty->assign($key, $value);
	}
	

	/** Display Template **/
    public function setWrapper($wrapper){
            $this->_wrapper = $wrapper;
    }
    public function setWrapperDir($x){
            $this->_wrapperDir = $x;
    }
	public function setTemplateFile($file){
		$this->templateFile = $file;
	}
	public function getContents($noWrapper = 0){
		$res = '';
		$content = $this->smarty->fetch($this->templateFile);
		if ($noWrapper) {
			$res = $content;
		} else {
			if ($this->_wrapperDir){
				$wrapper = $this->_wrapperDir . DS . $this->_wrapper  . '.html';
			} else {
				$wrapper = $this->smarty->template_dir . DS . $this->_wrapper  . '.html';
			}
			$this->smarty->clearCache($wrapper);
			$this->set('content', $content);
			$res=$this->smarty->fetch($wrapper);
		}
		return $res;
		
	}
	
	public function render ($noWrapper = 0) {
		$res = $this->getContents($noWrapper);
		print $res;
	}
	public function fetch () {
		return $this->smarty->fetch($this->templateFile);
	}

}