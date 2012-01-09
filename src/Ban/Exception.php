<?php

class Ban_Exception extends Exception
{
    
    var $code = Ban_Exception::NOT_FOUND;

    const BAD_REQUEST = 400;
    const NOT_AUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 490;
    const GONE = 410;
    /*
    411 const Length Required;
    412 const Precondition Failed;
    413 const Request Entity Too Large;
    414 const Request-URI Too Long;
    415 const Unsupported Media Type;
    */
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;

}
