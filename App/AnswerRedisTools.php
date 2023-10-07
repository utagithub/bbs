<?php
/**
 * redis操作类
 */
class AnswerRedisTools

{

    private static $instance;

    public $objRedis;
//  private $objRedis;

    private $redisAddress = '127.0.0.1';

    private $redisPort = '6379';

    private $redisPassword = 'redis';
//  private $redisPassword = 'www.jjwxc.ne';

    private $redisDbName = 6;

    private $redisPrefix = 'BBS_ARTICLE_';

    private $key = 'LIST';

    /**
     * 构造方法 初始化redis对象
     * Redis constructor.
     * @param $config
     */

    private function __construct($config)

    {

        !empty($config['redisAddress']) && $this->redisAddress = $config['redisAddress'];
        !empty($config['redisPassword']) && $this->redisPassword = $config['redisPassword'];
        !empty($config['redisPort']) && $this->redisPort = $config['redisPort'];
        !empty($config['redisDbName']) && $this->redisDbName = $config['redisDbName'];
        !empty($config['redisAddress']) && $this->redisAddress = $config['redisAddress'];
        !empty($config['redisPrefix']) && $this->redisPrefix = $config['redisPrefix'];

        $this->objRedis = new Redis();

        //链接redis
        try {

            $connect = $this->objRedis->connect($this->redisAddress, $this->redisPort, 30);

        } catch (Exception $e) {

            echo $e->getMessage();
            exit;

        }

        //认证密码
        try {

            $auth = $this->objRedis->auth($this->redisPassword);

        } catch (Exception $e) {

            echo $e->getMessage();
            exit;

        }

        //选择数据库
        $this->objRedis->select($this->redisDbName);

    }

    /**
     * 单例
     * @param $config
     * @return Redis
     */

    public static function getInstance($config)
    {

        if (self::$instance == NULL) {

            self::$instance = new self($config);

        }

        return self::$instance;

    }


    /**
     * 添加数据到有序集合
     * @param $score
     * @param $value
     * @param $key
     * @return bool
     */
    public function zadd_($score, $value, $key)

    {

        if (empty($score) || empty($value)) {

            return false;

        }

        if (!empty($key)) {

            $this->key = $key;

        }

        return $this->objRedis->zAdd($this->redisPrefix . $this->key, ['NX' | 'XX'], $score, $value);

    }

    /**
     * 删除有序集合数据
     * @param $key
     * @param $member1
     * @param ...$otherMembers
     */
    public function zrem($key, $member, ...$otherMembers)
    {
        if (empty($member)) {

            return false;

        }

        if (!empty($key)) {

            $this->key = $key;

        }

        return $this->objRedis->zrem($this->redisPrefix . $this->key, $member, ...$otherMembers);
    }

    /**
     * 获取有序集合长度
     * @param $key
     * @return int
     */
    public function getZSetTotal($key)

    {

        if (!empty($key)) {

            $this->key = $key;

        }

        $keyName = $this->redisPrefix . $this->key;

        $total = $this->objRedis->zCard($keyName);

        return $total;

    }

    /**
     * 获取有序集合列表数据
     * @param $key
     * @param $offset
     * @param $end
     *
     * @param $orderBy
     */
    public function getZSetList($key, $offset, $end, $orderBy)

    {

        if (!empty($key)) {

            $this->key = $key;

        }

        $keyName = $this->redisPrefix . $this->key;

        if ($orderBy) {
            $list = $this->objRedis->zRevRange($keyName, $offset, $end, true);//倒叙
        } else {
            $list = $this->objRedis->zRange($keyName, $offset, $end, true);//正序
        }

        return $list;

    }

    /**
     * 获取有序集合单个数据
     * @param $key
     * @param $member
     * @return array|false|Redis
     * @throws \RedisException
     */
    public function getZSetOne($key, $member)

    {

        if (!empty($key)) {

            $this->key = $key;

        }

        $keyName = $this->redisPrefix . $this->key;
        //var_dump($keyName);

        $score = $this->objRedis->zScore($keyName, $member);
        //var_dump($list);
        if ($score) {
            return $score;
        } else {
            return false;
        }


    }


    /**
     * 添加hash数据
     * @param $id
     * @param $info
     */
    public function setHash($id, array $info)
    {
        if (!is_numeric($id) || !is_array($info) || empty($info)) {
            return false;
        }
        $redisHashKey = $this->redisPrefix . '_' . $id;
        return $this->objRedis->hMSet($redisHashKey, $info);
    }

    /**
     * 获取hash数据
     * @param $keys
     * @return array
     */
    public function getHash($keys)
    {
        $res = array();

        if (is_array($keys)) {
            foreach ($keys as $v) {
                $res[$v] = $this->objRedis->hGetAll($this->redisPrefix . '_' . $v);
            }
        } else {
            $res = $this->objRedis->hGetAll($this->redisPrefix . '_' . $keys);
        }
        return $res;
    }

    /**
     * 删除hash数据
     * @param $id
     * @param $info
     */
    public function hdel($id, $hashKey1, ...$otherHashKeys)
    {
        if (!is_numeric($id)) {
            return false;
        }
        $redisHashKey = $this->redisPrefix . '_' . $id;
        return $this->objRedis->hdel($redisHashKey, $hashKey1, ...$otherHashKeys);
    }


    /**
     * $scene 锁场景
     * $expire 锁有效期
     * return bool
     */
    public function lock($scene, $expire)
    {
        if (!$scene || !$expire) {
            return false;
        }

        if ($this->objRedis->get($scene)) {
            return $this->objRedis->get($scene);
        }

        // 生成随机值;锁标识
        $lockId = md5(uniqid());
        $result = $this->objRedis->set($scene, $lockId, ['NX', 'EX' => $expire]);

        if ($result) {
            return $lockId;
        } else {
            $fp = fopen('data.txt', 'a');
            fwrite($fp, $result);
            fclose($fp);
            return $result;
        }
    }

    /**
     * 解锁
     */

    /**
     * 解锁
     * @param $scene
     * @param $lockId
     * @return mixed|Redis
     * @throws \RedisException
     */
    public function unLock($scene, $lockId)
    {
        /*local key   = KEYS[1];
        local value = ARGV[1];
        return redis.call('del',key);*/

        $lua = <<<SCRIPT
          if(redis.call('get',KEYS[1]) == ARGV[1])
          then
            return redis.call('del',KEYS[1])
          end    
SCRIPT;
        //执行lua脚本,KEYS[1])和ARGV[1]都是参数,表示传递的第一个key和第一个value，numkeys表示有几个key
        return $this->objRedis->eval($lua, [$scene, $lockId], 1);


    }

}




