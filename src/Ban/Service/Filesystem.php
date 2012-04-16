<?php

class Ban_Service_Filesystem extends Ban_Service_Abstract
{

    protected $_baseDir = '/home/adam/workspace/wss/larousse/trunk/plio-api/data/xml';

    protected function _listFiles()
    {
        $files = array();
        $iterator = new IteratorIterator(
            new DirectoryIterator($this->_baseDir)
        );
        
        // Tipos de archivo
        foreach ($iterator as $path) {
            if ($path->isDir()) {
                continue;
            }
            $tipo = $path->getExtension();
            $files[] = array(
                'name' => $path->getFilename(),
                'size' => number_format($path->getSize() / 1024, 2),
                'mtime' => date('Y-m-d h:i', $path->getMTime())
            );
        }
        return $files;
    }
    
    public function _getFile($filename)
    {
        $path = $this->_baseDir . DIRECTORY_SEPARATOR . $filename;
        $stat = @stat($path);
        if ($stat === false) {
            throw new Ban_Exception_Client(
                "File [$filename] does not exist on this server",
                404
            );
        }
        return array(
            'name' => $filename,
            'size' => $stat['size'],
            'mtime' => date('Y-m-d h:i', $stat['mtime']),
        );
    }

    public function get(Ban_Request $request)
    {
        if ($request->filename === null) {
            $files = $this->_listFiles();
            $result = $this->createResponse('recordset');
            $result->total_results = count($files);
            $result->total_records = $result->total_results;
            $result->result = $files;
        } else {
            $filename = basename($request->filename);
            $result = $this->createResponse('record');
            $result->result = $this->_getFile($filename);
        }
        return $result;
    }
    
    public function post(Ban_Request $request)
    {
        if ($request->filename === null) {
            throw new Ban_Exception_Client("No filename parameter supplied", 400);
        }
        $filename = basename($request->filename);
        $path = $this->_baseDir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, $request->content);
        $result = $this->createResponse('record');
        $result->result = $this->_getFile($filename);
        return $result;
    }

    public function put(Ban_Request $request)
    {
        $this->post($request);
    }

    public function delete(Ban_Request $request)
    {
        $filename = basename($request->filename);
        $path = $this->_baseDir . DIRECTORY_SEPARATOR . $filename;
        unlink($path);
        $result = $this->createResponse('recordid');
        $result->filename = $filename;
        return $result;
    }
}
