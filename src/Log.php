<?php
namespace Allinora\Simple;

class Log {
	
	public function _construct(){
		
	}
	
	public function log($str=""){
		if (is_string($str)){
			print "Log: $str<br>";
			//syslog(LOG_WARNING, $str);
		}
	}
}
