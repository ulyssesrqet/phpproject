<?php
namespace myframe;

use Smarty;

class Controller
{
    protected $app;
    protected $request;
    protected $Smarty;

    public function __construct(App $app, Request $request, Smarty $smarty)
    {
        $this->app = $app;
        $this->request = $request;
        $this->Smarty = $smarty;
        //初始化smarty配置
        $rootPath = $app->getRootPath();
        //配置模板目录
        $this->Smarty->template_dir = $rootPath.'resources/views/';
        //配置编译文件目录
        $this->Smarty->compile_dir = $rootPath.'storage/framework/views/';
        //对每个模板变量都进行HTML编码转义
        $this->Smarty->default_modifiers = ['escape:"htmlall"'];

        $this->initialize();
    }

    protected function initialize()
    {

    }

    /**
     * 为模板变量赋值
     * @param $name 模板变量名
     * @param string $value 值
     */
    public function assign($name, $value = '')
    {
        $this->Smarty->assign($name, $value);
    }

    /**
     * 渲染模板文件
     * @param string $template 模板文件名
     * @return string 渲染结果
     * @throws \SmartyException Smarty异常
     */
    public function fetch($template = '')
    {
        return $this->Smarty->fetch($template.'.html');
    }

    /**
     * 发送成功响应结果
     * @param string $msg 提示信息
     * @throws HttpException 通过HttpException异常来直接返回数据，不再继续执行代码
     */
    public function success($msg = '')
    {
        //响应数据
        $data = [
            'code' => 1,
            'msg' => $msg
        ];
        //响应数据转为JSON格式
        $data = json_encode($data);
        //响应头部
        $header = ['Content-type' => 'application/json'];
        //实例化响应对象
        $response = Response::create($data, 200, $header);
        //抛出HttpException异常直接返回响应对象
        throw new HttpException($response);
    }
    /**
     * 发送失败响应结果
     * @param string $msg 提示信息
     * @throws HttpException 通过HttpException异常来直接返回数据，不再继续执行代码
     */
    public function error($msg = '')
    {
        //响应数据
        $data = [
            'code' => 0,
            'msg' => $msg
        ];
        //响应数据转为JSON格式
        $data = json_encode($data);
        //响应头部
        $header = ['Content-type' => 'application/json'];
        //实例化响应对象
        $response = Response::create($data, 200, $header);
        //抛出HttpException异常直接返回响应对象
        throw new HttpException($response);
    }

    /**
     * 重定向到指定路径
     * @param string $url 重定向路径
     * @param string $code 响应状态码
     * @throws HttpException 通过HttpException异常来直接返回数据，不再继续执行代码
     */
    protected function redirect($url = '', $code = '302')
    {
        //设置重定向头部
        $header = ['Location' => $url];
        //抛出HttpException异常直接返回响应对象
        throw new HttpException(Response::create('', $code, $header));
    }
}