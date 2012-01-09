<?php

class Ban_Bootstrap extends Zend_Application_Bootstrap_BootstrapAbstract
{

    public function run()
    {
        $server = new Ban_Server();
        $request = new Ban_Request();
        $server->respond($request);
    }

}
