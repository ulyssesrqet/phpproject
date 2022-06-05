<?php
namespace myframe;

class Response
{
    //状态码
    protected $code = 200;
    //响应头部
    protected $header = [];
    //响应数据
    protected $data = '';

    /**
     * Response constructor.初始化成员属性
     * @param string $data 响应数据
     * @param int $code 状态码
     * @param array $header 响应头部
     */
    public function __construct($data = '', $code = 200, array $header = [])
    {
        $this->data = $data;
        $this->code = $code;
        //将自定义头部和默认头部合并
        $this->header = array_merge($this->header, $header);
    }

    /**
     * 发送数据
     */
    public function send()
    {
        //设置响应状态码
        http_response_code($this->code);
        //设置响应头部
        foreach ($this->header as $name => $value) {
            //拼接响应头部：1. 头部没有对应的值：$name；2. 头部有对应的值：$name:$value
            $headerStr = $name.(is_null($value) ? '' : ':'.$value);
            //发送头部
            header($headerStr);
        }
        //发送数据
        echo $this->data;
    }

    /**
     * 获取Response实例对象
     * @param string $data 响应数据
     * @param int $code 状态码
     * @param array $header 响应头部
     * @return static Response实例对象
     */
    public static function create($data = '', $code = 200, array $header = [])
    {
        return new static($data, $code, $header);
    }
}