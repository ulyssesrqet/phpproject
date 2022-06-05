<?php
namespace myframe;

use Exception;

class HttpException extends Exception
{
    //响应对象
    protected $response;
    public function __construct(Response $response)
    {
        $this->response = $response;
    }
    /**
     * 获取响应对象
     * @return Response 响应对象
     */
    public function getResponse()
    {
        return $this->response;
    }
}