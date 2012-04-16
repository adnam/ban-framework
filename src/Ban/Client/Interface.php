<?php

interface Ban_Client_Interface
{

	function get($path, $params = array());
	
	function put($path, $params = array());

	function merge($path, $params = array());
	
	function post($path, $params = array());

	function delete($path, $params = array());

	function options($path, $params = array());

}
