<?php

interface Ban_Client_Interface
{

	function get($path, $params);
	
	function put($path, $params);

	function merge($path, $params);
	
	function post($path, $params);

	function delete($path, $params);

	function options($path, $params);

}
