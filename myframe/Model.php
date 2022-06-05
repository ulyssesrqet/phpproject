<?php
namespace myframe;

use Exception;

class Model
{
    protected $db;
    protected $table = '';
    protected $options;

    public function __construct()
    {
        //初始化DB类
        $this->db = DB::getInstance();
        //初始化表名
        $this->initTable();
        //重置$options中的值
        $this->resetOption();
    }

    /**
     * 初始化表名
     */
    protected function initTable()
    {
        //判断是否设置表名，没有则将类名做为表名
        if ($this->table === '') {
            //获取模型类的名字
            $className = basename(str_replace('\\', '/', get_class($this)));
            //将模型类的名字全部转为小写
            $this->table = strtolower($className);
            //拼接数据表的前缀
            $this->table = $this->db->getConfig('prefix') . $this->table;
        }
    }

    /**
     * 重置$options中的值
     */
    protected function resetOption()
    {
        $this->options = [
            'where' => '',      // WHERE子句
            'order' => '',      // ORDER BY子句
            'limit' => '',      // LIMIT子句
            'data' => []        // WHERE中的数据部分
        ];
    }
    /**
     * 获取多条数据，并可以指定查询字段
     * @param array $field 字段列表
     * @return mixed 多条数据
     */
    public function get(array $field = [])
    {
        //拼接SELECT语句
        $sql = $this->buildSelect($field);
        //获取所有数据
        $data = $this->db->fetchAll($sql, $this->options['data']);
        //重置$options中的值
        $this->resetOption();
        //返回数据
        return $data;
    }

    /**
     * 获取一条数据的指定字段
     * @param array $field 字段列表
     * @return mixed 一条数据
     */
    public function first(array $field = [])
    {
        //判断SQL语句是否设置了LIMIT子句限制条数，没有则设置为1条
        if (!$this->options['limit']) {
            $this->limit(1);
        }
        //拼接SELECT语句
        $sql = $this->buildSelect($field);
        //获取单条数据
        $data = $this->db->fetchRow($sql, $this->options['data']);
        //重置$options中的值
        $this->resetOption();
        //返回数据
        return $data;
    }

    /**
     * 获取指定列的值
     * @param $field 字段名
     * @return mixed|null 值
     */
    public function value($field)
    {
        //获取单条数据
        $res = $this->first([$field]);
        //返回数据中的指定列
        return $res ? $res[$field] : null;
    }

    /**
     * 删除数据
     * @return mixed 受影响的行数
     * @throws Exception
     */
    public function delete()
    {
        //判断是否设置where子句，没有则抛出异常，以避免将整个表中数据删除
        if (empty($this->options['where'])) {
            throw new Exception('delete()缺少WHERE条件。');
        }
        //拼接DELETE语句
        $sql = $this->buildDelete();
        //执行SQL语句
        $res = $this->db->execute($sql, $this->options['data']);
        //重置$options中的值
        $this->resetOption();
        //返回结果
        return $res;
    }

    /**
     * 插入一条或多条数据
     * @param array $data 新增的数据，数组元素的键为字段名，值为字段对应的值
     * @return mixed 受影响行数
     */
    public function insert(array $data = [])
    {
        //判断参数$data是否为二维数组，是二维数组则表示一次新增多条数据，不是则新增一条
        if (isset($data[0]) && is_array($data[0])) {
            //拼接插入多条语句的INSERT语句
            $sql = $this->buildInsert(array_keys($data[0]), count($data));
            //获取占位符对应的数据
            $data = array_reduce($data, function ($carry, $item) {
                return array_merge($carry, array_values($item));
            }, []);
        } else {
            //拼接插入单条数据的INSERT语句
            $sql = $this->buildInsert(array_keys($data));
            //获取占位符对应的数据
            $data = array_values($data);
        }
        //执行SQL语句
        $res = $this->db->execute($sql, $data);
        //重置$options中的值
        $this->resetOption();
        //返回结果
        return $res;
    }
    /**
     * 插入一条或多条数据
     * @param array $data 新增的数据，数组元素的键为字段名，值为字段对应的值
     * @return mixed 最后插入的ID
     */
    public function insertGetId(array $data = [])
    {
        //插入数据
        $this->insert($data);
        //返回最后插入的ID
        return $this->db->lastInsertId();
    }

    /**
     * 更新数据
     * @param array $data 更新的数据
     * @return mixed 受影响的行数
     * @throws Exception 执行SQL语句异常
     */
    public function update(array $data = [])
    {
        //判断是否设置where子句，没有则抛出异常，以避免将整个表中数据更新
        if (empty($this->options['where'])) {
            throw new Exception('update()缺少WHERE条件。');
        }
        //拼接UPDATE语句
        $sql = $this->buildUpdate(array_keys($data));
        // 获取占位符对应的数据（将字段值的占位符和where子句中的占位符对应的数据合并）
        $data = array_merge(array_values($data), $this->options['data']);
        //执行SQL语句
        $res = $this->db->execute($sql, $data);
        //重置$options中的值
        $this->resetOption();
        //返回结果
        return $res;
    }

    /**
     * 实现WHERE条件查询，多个条件之间使用AND连接
     * @param $field 字段名
     * @param string $op 操作符
     * @param null $value 值
     * @return $this 当前类对象
     */
    public function where($field, $op = '=', $value = null)
    {
        //拼接WHERE子句，多个条件之间使用AND连接
        $this->buildWhere($field, $op, $value, 'AND');
        //返回模型对象，方便链式调用
        return $this;
    }

    /**
     * 实现WHERE条件查询，多个条件之间使用OR连接
     * @param $field 字段名
     * @param string $op 操作符
     * @param null $value 值
     * @return $this 当前类对象
     */
    public function orWhere($field, $op = '=', $value = null)
    {
        //拼接WHERE子句，多个条件之间使用OR连接
        $this->buildWhere($field, $op, $value, 'OR');
        //返回模型对象，方便链式调用
        return $this;
    }

    /**
     * 拼接ORDER BY子句实现排序
     * @param $field 字段名
     * @param string $sort 排序方式
     * @return $this 当前类对象
     */
    public function orderBy($field, $sort = 'ASC')
    {
        //保存ORDER子句
        $this->options['order'] = "ORDER BY `$field` $sort";
        //返回模型对象，方便链式调用
        return $this;
    }

    /**
     * 拼接LIMIT子句实现获取指定范围的数据
     * @param $offset 传递1个参数则为限制条数，2个参数则为偏移量
     * @param string $limit 限制条数
     * @return $this 当前类对象
     */
    public function limit($offset, $limit = '')
    {
        if ($limit) {
            $limit = ", $limit";
        }
        //保存LIMIT子句
        $this->options['limit'] = 'LIMIT ' . $offset . $limit;
        //返回模型对象，方便链式调用
        return $this;
    }

    /**
     * 拼接SELECT语句，并可以指定查询字段
     * @param array $field 字段列表
     * @return string SQL语句
     */
    protected function buildSelect(array $field = [])
    {
        //拼接字段名字符串
        $field = empty($field) ? '*' : ('`' . implode('`,`', $field) . '`');
        //获取操作的数据表名
        $table = $this->table;
        //获取$options中保存得SQL子句，拼接完整的SELECT语句并返回
        $where = $this->options['where'];
        $order = $this->options['order'];
        $limit = $this->options['limit'];
        return "SELECT $field FROM `$table` $where $order $limit";
    }

    /**
     * 拼接where子句
     * @param $field 如果是array类型则数组元素为字段名和值组成的键值对，否则为字段名
     * @param $op 如果$fields不是array类型且只传递2个参数，则为值，否则为操作符
     * @param $value 值
     * @param string $join 连接符
     */
    protected function buildWhere($field, $op, $value, $join = 'AND')
    {
        //判断是否有多个条件
        if (is_array($field)) {
            //依次拼接多个条件
            foreach ($field as $k => $v) {
                $this->buildWhere($k, $op, $v, $join);
            }
            //结束方法
            return;
        } elseif (is_null($value)) {
            //如果$fields不是array类型且只传递2个参数，则$op为值，操作符默认为‘=’
            $value = $op;
            $op = '=';
        }
        //判断是否已保存WHERE子句，没有则拼接where关键词
        if (empty($this->options['where'])) {
            $join = 'WHERE';
        }
        //保存WHERE子句
        $this->options['where'] .= "$join `$field` $op ?";
        //保存where子句中占位符对应的数据
        $this->options['data'][] = $value;
    }

    /**
     * 拼接INSERT语句
     * @param array $field 字段数组
     * @param int $count 新增的条数
     * @return string SQL语句
     */
    protected function buildInsert(array $field = [], $count = 1)
    {
        //根据字段的个数，生成指定数量的“?”占位符
        $value = array_fill(0, count($field), '?');
        //将值拼接成“(?,?)”的形式
        $value = '(' . implode(',', $value) . ')';
        //根据插入的条数$count，将值拼接成“(?,?),(?,?)”的形式
        $value = implode(',', array_fill(0, $count, $value));
        //拼接字段名
        $field = '`' . implode('`,`', $field) . '`';
        //获取表名
        $table = $this->table;
        //返回INSERT语句
        return "INSERT INTO `$table` ($field) VALUES $value";
    }

    /**
     * 拼接UPDATE语句
     * @param array $fields 字段数组
     * @return string
     */
    protected function buildUpdate(array $fields = [])
    {
        //拼接`field1`=?,`field2`=?,...,`fieldN`=?的形式
        $field = implode(',', array_map(function ($v) {
            return "`$v`=?";
        }, $fields));
        //获取表名
        $table = $this->table;
        //获取$options中的SQL子句
        $where = $this->options['where'];
        $order = $this->options['order'];
        $limit = $this->options['limit'];
        //返回完整的UPDATE语句
        return "UPDATE `$table` SET $field $where $order $limit";
    }

    /**
     * 拼接DELETE语句
     * @return string SQL语句
     */
    protected function buildDelete()
    {
        //获取表名
        $table = $this->table;
        //获取$options中的SQL子句
        $where = $this->options['where'];
        $order = $this->options['order'];
        $limit = $this->options['limit'];
        //返回完整的DELETE语句
        return "DELETE FROM `$table` $where $order $limit";
    }

    /**
     * 获取符合条件的记录的条数
     * @return int|null 记录数
     */
    public function count()
    {
        $table = $this->table;
        $where = $this->options['where'];
        $sql = "SELECT COUNT(*) AS c FROM $table $where";
        $res = $this->db->fetchRow($sql, $this->options['data']);
        $this->resetOption();
        return $res ? $res['c'] : null;
    }

    /**
     * 字段自增
     * @param $field 字段名
     * @param int $add 增加的值
     * @return mixed 影响行数
     */
    public function increment($field, $add = 1)
    {
        $table = $this->table;
        $where = $this->options['where'];
        $order = $this->options['order'];
        $limit = $this->options['limit'];
        $sql = "UPDATE `$table` SET `$field`=`$field`+$add $where $order $limit";
        $res = $this->db->execute($sql, $this->options['data']);
        $this->resetOption();
        return $res;
    }
}
