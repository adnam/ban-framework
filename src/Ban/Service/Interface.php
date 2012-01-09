<?php

interface Ban_Service_Interface
{
    public function get(Ban_Request $request);

    public function post(Ban_Request $request);

    public function put(Ban_Request $request);

    public function head(Ban_Request $request);

    public function delete(Ban_Request $request);

    public function trace(Ban_Request $request);

    public function options(Ban_Request $request);

    public function merge(Ban_Request $request);
}
