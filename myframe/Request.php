<?php
namespace myframe;

class Request
{
    //PATH_INFO信息
    protected $pathInfo;
    //方法名
    protected $action;

    /**
     * 获取PATH_INFO信息
     * @return mixed|null PATH_INFO信息
     */
    public function pathInfo()
    {
        //判断成员属性$pathInfo中是否已保存PATH_INFO信息
        if (is_null($this->pathInfo)) {
            //如果没有则获取
            $this->pathInfo = $this->server('PATH_INFO') ? $this->server('PATH_INFO') : $this->server('REDIRECT_PATH_INFO');
        }
        //返回PATH_INFO信息
        return $this->pathInfo;
    }

    /**
     * 获取$_SERVER中的数据
     * @param $name $_SERVER数组元素的下标
     * @param null $default 默认值
     * @return mixed|null $_SERVER中指定元素的值
     */
    public function server($name, $default = null)
    {
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
    }

    /**
     * 获取$_GET中的数据
     * @param $name $_GET数组元素的下标
     * @param null $default 默认值
     * @return mixed|null $_GET中指定元素的值
     */
    public function get($name, $default = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * 获取$_POST中的数据
     * @param $name $_POST数组元素的下标
     * @param null $default 默认值
     * @return mixed|null $_POST中指定元素的值
     */
    public function post($name, $default = null)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    /**
     * 设置当前请求的方法名
     * @param $action 方法名
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    /**
     * 获取当前请求的方法名
     * @return string
     */
    public function action()
    {
        return $this->action ?: '';
    }

    /**
     * 判断是否为Ajax请求
     * @return bool 是则返回true，不是则返回false
     */
    public function isAjax()
    {
        return $this->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    /**
     * 判断是否有上传文件
     * @param $name $_FILES数组的键名
     * @return bool 上传文件则返回true，否则返回false
     */
    public function hasFile($name)
    {
        return isset($_FILES[$name]['error']) && $_FILES[$name]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * 根据上传文件信息创建Upload对象
     * @param $name $_FILES数组的键名
     * @return Upload Upload对象
     * @throws \Exception 创建Upload对象异常
     */
    public function file($name)
    {
        $file = isset($_FILES[$name]) ? $_FILES[$name] : [];
        return Upload::create($file);
    }
}