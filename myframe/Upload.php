<?php
namespace myframe;

use Exception;

class Upload
{
    //上传文件失败的常量对应的提示信息
    protected $msg = [
        UPLOAD_ERR_INI_SIZE => '文件大小超过了服务器设置的限制！',
        UPLOAD_ERR_FORM_SIZE => '文件大小超过了表单设置的限制！',
        UPLOAD_ERR_PARTIAL => '文件只有部分被上传！',
        UPLOAD_ERR_NO_FILE => '没有文件被上传！',
        UPLOAD_ERR_NO_TMP_DIR => '上传文件临时目录不存在！',
        UPLOAD_ERR_CANT_WRITE => '文件写入失败！'
    ];
    //上传文件信息
    protected $file = ['name' => '', 'tmp_name' => '', 'type' => '', 'size' => 0, 'error' => 4];

    public function __construct(array $file = [])
    {
        //判断文件信息是否合法
        if (!isset($file['error'])) {
            throw new Exception('文件不合法！');
        }
        //判断上传过程是否出错
        $error = $file['error'];
        if ($error !== UPLOAD_ERR_OK) {
            $msg = isset($this->msg[$error]) ? $this->msg[$error] : '未知错误！';
            throw new Exception($msg);
        }
        //保存文件信息
        $this->file = array_merge($this->file, $file);
    }

    /**
     * 创建Upload对象
     * @param array $file 上传文件信息
     * @return Upload Upload对象
     * @throws Exception 上传失败异常
     */
    public static function create(array $file = [])
    {
        return new static($file);
    }

    /**
     * 获取上传文件后缀
     * @return mixed 文件后缀
     */
    public function extension()
    {
        return pathinfo($this->file['name'], PATHINFO_EXTENSION);
    }

    /**
     * 移动上传文件到指定路径
     * @param string $path 指定目录
     * @param string $name 指定文件名
     * @return string 文件名
     * @throws Exception 移动上传文件到指定路径失败异常
     */
    public function move($path = '.', $name = '')  //  /uploads/image/
    {
        //处理路径
        $path = rtrim($path, '/') . '/';
        //判断指定目录是否存在，不存在则创建
        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new Exception('无法创建保存目录！');
        }
        //生成文件名
        if ($name === '') {
            $name = md5(microtime(true)) . '.' . $this->extension();
        }
        //移动文件到指定路径
        if (!move_uploaded_file($this->file['tmp_name'], $path . $name)) {
            throw new Exception('无法保存文件！');
        }
        //返回文件名
        return $name;
    }
}
