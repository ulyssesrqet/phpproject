<?php
namespace myframe;

use ReflectionClass;
use ReflectionMethod;

class Container
{
    //实例对象集合
    protected $instances = [];
    //当前类的实例对象
    protected static $instance;

    /**
     * Container constructor. 私有化构造函数, 避免在类外使用new关键字实例化对象
     */
    protected function __construct()
    {
    }

    /**
     * 私有化克隆函数, 避免在类外使用clone关键字实例化对象
     */
    protected function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * 获取当前类的实例对象
     * @return mixed 当前类的实例对象
     */
    public static function getInstance()
    {
        //判断当前类的实例对象是否存在
        if (is_null(static::$instance)) {
            //不存在则实例化，并保存
            static::$instance = new static();
        }
        //返回当前类的实例对象
        return static::$instance;
    }

    /**
     * 创建实例对象
     * @param $class 要创建的实例对象所属的类名（包含命令空间）
     * @return mixed 实例对象
     */
    public function make($class)
    {
        //判断是否已保存$class的实例对象，如果已保存则直接返回，没有则实例化对象
        if (!isset($this->instances[$class])) {
            //获取$class的反射
            $reflect = new ReflectionClass($class);
            //获取构造方法
            $constructor = $reflect->getConstructor();
            //获取构造方法依赖的对象（参数）
            $args = $constructor ? $this->bindParam($constructor) : [];
            //实例化$class的对象并保存
            $this->instances[$class] = $reflect->newInstanceArgs($args);
        }
        //返回实例对象
        return $this->instances[$class];
    }

    /**
     * 获取方法所依赖的对象（参数）
     * @param ReflectionMethod $method 方法的反射
     * @return array 依赖对象
     */
    public function bindParam(ReflectionMethod $method)
    {
        //获取方法所需参数的信息
        $params = $method->getParameters();
        $args = [];
        //遍历参数
        foreach ($params as $param) {
            //获取参数所属类的反射
            $class = $param->getClass();
            if ($class) {
                //获取参数所属类的名字
                $className = $class->getName();
                //递归调用make()方法创建对象
                $args[] = $this->make($className);
            }
        }
        //返回参数
        return $args;
    }
}