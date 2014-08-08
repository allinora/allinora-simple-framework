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
