<?php
namespace myframe;

use Exception;
use ReflectionMethod;

class App extends Container
{
    //Request对象
    protected $request;
    //项目根目录
    protected $rootPath;
    //调式开关
    protected $debug = true;
    public function __construct()
    {
        $this->instances[App::class] = $this;
        //通过容器类获取Request对象并初始化成员属性
        $this->request = $this->make(Request::class);
        //保存项目根目录
        $this->rootPath = dirname(__DIR__).'/';
        //获取自定义数据库配置信息
        $config = require $this->rootPath.'config/database.php';
        //将自定义数据库配置保存到DB类中
        DB::init($config);
    }

    public function getRootPath()
    {
        return $this->rootPath;
    }
    /**
     * 启动应用
     * @return Response 响应对象
     */
    public function run()
    {
        try {
            $dispatch = $this->routeCheck();
            return $this->dispatch($dispatch);
        } catch (HttpException $e) {
            return $e->getResponse();
        } catch (Exception $e) {
            $msg = $this->debug ? $e->getMessage() : '';
            return Response::create('系统发生错误。'.$msg, 403);
        }
    }

    /**
     * 路由检测
     * @return array 控制器和方法名
     */
    public function routeCheck()
    {
        //获取路径中的PATH_INFO参数
        $pathInfo = $this->request->pathinfo();
        //获取路径中的控制器信息(包含\App\Http\Controllers\下的子空间名称和控制器名称)
        $controller = dirname($pathInfo);
        //获取方法名
        $action = basename($pathInfo);
        //设置默认控制器和方法名
        if ($controller === '' || $controller === '.') {
            $controller = 'Index';
        }
        if ($action === '') {
            $action = 'index';
        }
        //将控制器信息中单词的首字母大写
        $controller = ucwords($controller, '/');
        //控制器信息中的单词分别存储到数组的不同元素中
        $pathArr = explode('/', $controller);
        //使用“\”连接控制器信息中的单词
        $controller = implode('\\', $pathArr);
        //按控制器命名规范（*+Controller）拼接控制器名
        $controller = $controller.'Controller';
        //将方法名放到存有控制器信息的数组中,方便后续统一检测
        $pathArr[] = $action;
        //检测路径信息，要求以英文字母开头，后跟0-20个英文字母、数字或下划线
        foreach ($pathArr as $item) {
            if (!preg_match('/^[A-Za-z]\w{0,20}$/', $item)) {
                throw new Exception('请求参数包含特殊字符');
            }
        }
        //返回控制器名和方法名
        return [$controller, $action];
    }

    /**
     * 请求分发
     * @param array $dispatch 控制器和方法名
     * @return Response 响应对象
     */
    public function dispatch(array $dispatch)
    {
        //将控制器名和方法名分别保存到变量中
        list($controller, $action) = $dispatch;
        //保存方法名
        $this->request->setAction($action);
        //实例化控制器    IndexController
        $instance = $this->controller($controller);
        //判断控制器中的方法是否可调用
        if (is_callable([$instance, $action])) {
            //获取控制器方法的反射
            $refMethod = new ReflectionMethod($instance, $action);
        } else {
            throw new Exception('操作不存在。'.$action.'()');
        }
        //获取控制器方法所依赖的对象
        $args = $this->bindParam($refMethod);
        //调用控制器方法
        $data = $refMethod->invokeArgs($instance, $args);
        //返回响应对象
        return Response::create($data);
    }

    /**
     * 实例化控制器
     * @param $name 控制器类名
     * @return mixed 控制器实例
     */
    public function controller($name)
    {
        //为控制器名拼接命名空间
        $class = "\\App\\Http\\Controllers\\".$name;
        //判断控制器是否存在
        if (!class_exists($class)) {
            throw new Exception('请求的控制器'.$class.'不存在');
        }
        //返回控制器实例
        return $this->make($class);
    }
}