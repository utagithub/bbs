<?php
/**
 * mysql操作类
 */
class AnswerMysqlTools

{

    private static $instance = NULL;

    public $mysqlLink;

    private $charset = "utf8mb4";

    private $username = 'root';

    private $password = 'root';


    private $database = 'wang_test';
//  private $database = 'mrwang';

    private $host = '127.0.0.1';
//  private $host = '172.17.0.7';
    private $port = '3306';

    private $table = 'tiezi';

    /**
     * 构造方法 初始化mysql对象
     * @param $config
     */

    private function __construct($config)

    {

        !empty($config['host']) && $this->host = $config['host'];
        !empty($config['username']) && $this->username = $config['username'];
        !empty($config['password']) && $this->password = $config['password'];
        !empty($config['port']) && $this->port = $config['port'];
        !empty($config['charset']) && $this->charset = $config['charset'];
        !empty($config['database']) && $this->database = $config['database'];


        try {

            //持久化连接
            $this->mysqlLink = new PDO("mysql:host=$this->host;dbname=$this->database;charset=$this->charset", "$this->username", "$this->password", [PDO::ATTR_PERSISTENT => true, PDO::ATTR_EMULATE_PREPARES => false]);

        } catch (PDOException $e) {

            die ("数据库连接出错!: " . $e->getMessage() . "<br/>");

        }

    }

    /**
     * 单例模式获取mysql对象
     * @param $config
     */

    public static function getInstance($config)
    {

        if (self::$instance == NULL) {

            self::$instance = new self($config);

        }

        return self::$instance;

    }

    /**
     * 添加数据
     * @param $data
     * @return bool|int|string 成功返回id
     */

    public function insert($data)
    {

        if (!is_array($data)) {
            return false;
        }

        //组装sql
        $sql = 'insert into ' . $this->table;
        $keys = '';
        $values = '';
        foreach ($data as $key => $value) {
            $keys = $keys . $key . ',';
            $values = $values . ':' . $key . ',';
        }
        $sql = $sql . '(' . rtrim($keys, ',') . ') values (' . rtrim($values, ',') . ')';

        $pdo_stat = $this->mysqlLink->prepare($sql);

        //绑定数据
        foreach ($data as $k => $v) {
            $pdo_stat->bindValue(':' . $k, $v);
        }

        //die($pdo_stat->debugDumpParams());

        //执行sql
        $res = $pdo_stat->execute();

        if ($res) {
            return $this->mysqlLink->lastInsertId();
        } else {
            echo "添加失败";
        }


    }

    /**
     * 删除一条帖子
     * @param $id
     * @return bool
     */
    public function delete($id)
    {

        if (empty($id)) {
            return false;
        }

        //组装sql
        $sql = "delete from  $this->table  where id = :id";
        $pdo_stat = $this->mysqlLink->prepare($sql);

        //绑定数据
        $pdo_stat->bindValue(':id', $id);


        //die($pdo_stat->debugDumpParams());
        //执行sql,删除只能通过影响行数来判断操作是否成功
        $pdo_stat->execute();
        if ($pdo_stat->rowCount()) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 更新数据
     * @param $data
     * @return bool|int|string 成功返回id
     */

    public function update($data)
    {

        if (!is_array($data)) {
            return false;
        }

        $id = $data['id'];
        unset($data['id']);
        //组装sql
        $sql = 'update ' . $this->table . ' set ';
        foreach ($data as $key => $value) {
            $sql = $sql . $key . ' =  :' . $key . ', ';
        }
        $sql = rtrim($sql, ', ') . ' where id = :id';
//        die($sql);

        $pdo_stat = $this->mysqlLink->prepare($sql);

        //绑定数据
        foreach ($data as $k => $v) {
            $pdo_stat->bindValue(':' . $k, $v);
        }
        $pdo_stat->bindValue(':id', $id);

//        die($pdo_stat->debugDumpParams());

        //执行sql
        $res = $pdo_stat->execute();

        //if ($pdo_stat->rowCount() > 0) {
        if ($res) {
            return true;
        } else {
            return false;
        }


    }

    /**
     * 详情
     * @param $id
     * @return bool
     */
    public function find($id)
    {

        if (empty($id)) {
            return false;
        }

        //组装sql
        $sql = "select * from  $this->table  where id = :id";
        $pdo_stat = $this->mysqlLink->prepare($sql);

        //绑定数据
        $pdo_stat->bindValue(':id', $id);


        //die($pdo_stat->debugDumpParams());
        //执行sql,删除只能通过影响行数来判断操作是否成功
        $pdo_stat->execute();

        $result = $pdo_stat->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else {
            return false;
        }

    }


    /**
     * 分页
     * @param $limit
     * @param $offset
     * @return array|false
     */
    public function paginate($limit, $offset, $orderBy)
    {
        /*        if (empty($offset) || empty($limit)) {
                    return false;
                }*/

        //组装sql
        $sql = "select * from  $this->table order by id $orderBy limit $limit offset $offset ";
        $pdo_stat = $this->mysqlLink->prepare($sql);

        //绑定数据

//        die($pdo_stat->debugDumpParams());
        //执行sql,删除只能通过影响行数来判断操作是否成功
        $pdo_stat->execute();

        $result = $pdo_stat->fetchAll(\PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else {
            return false;
        }

    }

    /**
     * 统计总数
     * @return false|int
     */
    public function count()
    {
        //组装sql
        $sql = "select count('id') from  $this->table";
        $pdo_stat = $this->mysqlLink->prepare($sql);

        //die($pdo_stat->debugDumpParams());
        //执行sql,删除只能通过影响行数来判断操作是否成功
        $pdo_stat->execute();
        $result = $pdo_stat->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            return (int)array_values($result)[0];
        } else {
            return false;
        }

    }


    /**
     * 全量
     * @return array|false
     */
    public function select()
    {

        //组装sql
        $sql = "select * from  $this->table ";
        $pdo_stat = $this->mysqlLink->prepare($sql);

        //绑定数据

        //die($pdo_stat->debugDumpParams());

        $pdo_stat->execute();

        $result = $pdo_stat->fetchAll(\PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else {
            return false;
        }

    }


}






