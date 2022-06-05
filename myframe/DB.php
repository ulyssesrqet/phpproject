<?php
namespace myframe;

use PDO;
use PDOException;
use Exception;

class DB
{
    //PDO对象
    protected $pdo;
    //当前类的实例
    protected static $instance;
    //自定义的数据库配置信息
    protected static $initConfig = [];
    //默认数据库信息
    protected $config = [
        'type' => '',
        'host' => '',
        'port' => '3306',
        'dbname' => '',
        'charset' => 'utf8',
        'user' => 'root',
        'pwd' => '',
        'prefix' => ''
    ];

    protected function __construct($config = [])
    {
        //合并数据库配置信息
        $this->config = array_merge($this->config, $config);
        //初始化数据库连接
        $this->initPDO();
    }
    /**
     * 获取当前类的实例对象
     * @return mixed 当前类的实例对象
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static(static::$initConfig);
        }
        return static::$instance;
    }
    /**
     * 私有化克隆函数, 避免在类外使用clone关键字实例化对象
     */
    protected function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * 初始化自定义的数据库配置信息
     * @param array $config 自定义的数据库配置信息
     */
    public static function init($config = [])
    {
        static::$initConfig = $config;
    }

    /**
     * 初始化数据库连接
     * @throws Exception 数据库连接失败异常
     */
    public function initPDO()
    {
        $type = $this->config['type'];
        $host = $this->config['host'];
        $port = $this->config['port'];
        $dbname = $this->config['dbname'];
        $charset = $this->config['charset'];
        //数据源名称
        $dsn = "$type:host=$host;port=$port;dbname=$dbname;charset=$charset";
        try {
            $this->pdo = new PDO($dsn, $this->config['user'], $this->config['pwd']);
        } catch (PDOException $e) {
            throw new Exception('连接数据库失败：'.$e->getMessage());
        }
        //设置错误处理为异常模式
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * 获取一行数据
     * @param $sql SQL模板
     * @param array $bind SQL模板中占位符绑定的数据
     * @return mixed 一行数据
     * @throws Exception SQL语句执行错误异常
     */
    public function fetchRow($sql, array $bind = []) {
        try {
           //准备预处理语句
            $stmt = $this->pdo->prepare($sql);
            //执行预处理语句
            $stmt->execute($bind);
            //处理结果集，获取一行数据
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            //返回数据
            return $data;
        } catch (PDOException $e) {
            //格式化错误信息
            $error = $this->errorMsg($e, $sql);
            //抛出异常
            throw new Exception($error);
        }
    }
    /**
     * 获取所有数据
     * @param $sql SQL模板
     * @param array $bind SQL模板中占位符绑定的数据
     * @return mixed 所有数据
     * @throws Exception SQL语句执行错误异常
     */
    public function fetchAll($sql, array $bind = []) {
        try {
            //准备预处理语句
            $stmt = $this->pdo->prepare($sql);
            //执行预处理语句
            $stmt->execute($bind);
            //处理结果集，获取所有数据
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //返回数据
            return $data;
        }  catch (PDOException $e) {
            //格式化错误信息
            $error = $this->errorMsg($e, $sql);
            //抛出异常
            throw new Exception($error);
        }
    }
    /**
     * 执行SQL语句
     * @param $sql SQL模板
     * @param array $bind SQL模板中占位符绑定的数据
     * @return mixed SQL语句影响行数
     * @throws Exception SQL语句执行错误异常
     */
    public function execute($sql, array $bind = [])
    {
        try {
            //准备预处理语句
            $stmt = $this->pdo->prepare($sql);
            //执行预处理语句
            $stmt->execute($bind);
            //获取受影响的行数
            $rowCount = $stmt->rowCount();
            //返回数据
            return $rowCount;
        } catch (PDOException $e) {
            //格式化错误信息
            $error = $this->errorMsg($e, $sql);
            //抛出异常
            throw new Exception($error);
        }
    }

    /**
     * 获取最后插入的ID
     * @return mixed 最后插入的ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * 格式化错误信息
     * @param $e 异常对象
     * @param string $sql SQL语句
     * @return string 格式化后的错误信息
     */
    protected function errorMsg($e, $sql = '')
    {
        //获取异常对象中的错误信息
        $error = $e->getMessage();
        if ($sql) {
            //拼接SQL语句
            $error .= ' 执行SQL语句失败: '.$sql;
        }
        //返回数据
        return $error;
    }

    /**
     * 获取数据库配置信息
     * @param $name 数据库信息名
     * @return array|mixed 指定数据库信息
     */
    public function getConfig($name)
    {
        return $name ? $this->config[$name] : $this->config;
    }
}