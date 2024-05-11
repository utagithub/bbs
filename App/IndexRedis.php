<?php

//自动加载工具类-需要开启redis扩展（laravel框架中可能已关闭,这里测试使用需要重新开启）
//类的自动加载
function my_load($class_name)
{
    require_once $class_name . '.php';
}
spl_autoload_register('my_load');


//require_once 'AnswerMysqlTools.php';
//require_once 'AnswerRedisTools.php';

class IndexRedis

{

    //用作删除hash数据field
    const TABLE_FIELDS = ['subject', 'author', 'idate', 'replies', 'body', 'ndate', 'ip'];

    /*
     * 释放mysql链接资源
     */
    public function __destruct()
    {
        AnswerMysqlTools::getInstance([])->mysqlLink = null;
    }

    /**
     * 新增帖子
     */

    public function addBBSArticle()
    {
        //请求方式校验
        !$_POST && self::echoExit([], '错误请求方式', false);

        //请求参数校验
        empty($_POST['subject']) && self::echoExit([], '标题不可为空', false);
        empty($_POST['body']) && self::echoExit([], '主贴内容', false);
        empty($_POST['author']) && self::echoExit([], '发帖人', false);


        //入参组装
        $info = [
            'subject' => $_POST['subject'],
            'author' => $_POST['author'],
            'body' => $_POST['body'],
            'idate' => date('Y-m-d H:i:s', time()),
            'replies' => 0,
            'ndate' => date('Y-m-d H:i:s', time()),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];

        try {
            //mysql-开启事物
            $pdo = AnswerMysqlTools::getInstance([])->mysqlLink;
            $pdo->beginTransaction();
            //数据入mysql
            $addMysqlId = AnswerMysqlTools::getInstance([])->insert($info);
            if (!$addMysqlId) {
                throw new Exception("发帖入mysql失败");
            }

            //数据入redis
            $insertRedisResult = $this->insertRedis($addMysqlId, json_encode(array_merge($info, ['id' => $addMysqlId])));
//            var_dump($insertRedisResult);
            if ($insertRedisResult !== true) {
                throw new Exception('数据如redis失败');
            }

            //mysql-提交事物
            $pdo->commit();
            self::echoExit(array_merge($info, ['id' => $addMysqlId]), '发帖成功', true);

        } catch (Exception $exception) {

            //mysql-回滚事物
            $pdo->rollback();
            //删除redis数据
            $this->delRedis($addMysqlId);

            self::echoExit([], $exception->getMessage(), false);

        }


    }

    /**
     * 获取列表数据
     * @return array
     */

    public function getBBSArticleList()
    {
        //页数
        $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        //每页数据量
        $size = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 3;

        //偏移量-开始
        $offset = ($page - 1) * $size;
        //偏移量-结束
        $end = ($offset + $size) - 1;


        //数据总量
        $total = AnswerRedisTools::getInstance([])->getZSetTotal('');
        //总页码
        $allPage = (int)(ceil($total / $size));
        //取缓存数据
        $redisDataList = AnswerRedisTools::getInstance([])->getZSetList('', $offset, $end, true);


        //格式化数据,如果数据存了hash，这里可以这样获取
        $list = [];
        if (!empty($redisDataList)) {
            foreach ($redisDataList as $v) {
                $list[] = AnswerRedisTools::getInstance([])->getHash($v);
            }
        }

        //判断缓存是否有数据
        /*if($total <= 0){
            var_dump('111');
            $total = AnswerMysqlTools::getInstance([])-> count();
            $list  = AnswerMysqlTools::getInstance([])-> paginate($size,$offset,'desc');
            var_dump($list);
            //总页码
            //数据入redis
            if($total > 0 && $list){
                $allPage = (int)(ceil($total / $size));
                foreach ($list as $k => $v){
                    $this->insertRedis($v['id'], json_encode($v));
                }
            }else{
                $allPage = 0;
            }

        }else{
            var_dump('222');

            //总页码
            $allPage = (int)(ceil($total / $size));
            //取缓存数据
            $redisDataList = AnswerRedisTools::getInstance([])->getZSetList('', $offset, $end, true);


            //格式化数据,如果数据存了hash，这里可以这样获取
            $list = [];
            if (!empty($redisDataList)) {
                foreach ($redisDataList as $v) {
                    $list[] = AnswerRedisTools::getInstance([])->getHash($v);
                }
            }
        }*/


        //数据组装
        $res = [
            'listInfo' => $list,

            'pageInfo' => [
                'allPage' => $allPage,
                'total' => $total,
                'page' => $page,
                'size' => $size
            ]

        ];

        self::echoExit($res, 'SUCCESS', true);

    }

    /**
     * 详情
     * @return void
     * @throws Exception
     */
    public function getBBSArticleById()
    {
        //请求参数校验
        empty($_REQUEST['id']) && self::echoExit([], 'id不可为空', false);

        //检测数据在redis中是否存在
        $redis_zset_res = AnswerRedisTools::getInstance([])->getZSetOne('', $_REQUEST['id']);
        //var_dump($redis_zset_res);
        if ($redis_zset_res) {
            $redis_hash_res = AnswerRedisTools::getInstance([])->getHash((string)($redis_zset_res));
            //var_dump($redis_hash_res);
            self::echoExit($redis_hash_res, 'REDIS_SUCCESS', true);
        } else {
            $db_res = AnswerMysqlTools::getInstance([])->find($_REQUEST['id']);
            if ($db_res) {
                //数据入redis
                $insertRedisResult = $this->insertRedis($db_res['id'], json_encode($db_res));
                if ($insertRedisResult !== true) {
                    throw new Exception('数据入redis失败');
                }
                self::echoExit($db_res, 'DB_SUCCESS', true);
            } else {
                self::echoExit(null, '数据库没有查询到该数据', false);
            }
        }

    }


    /**
     * 删除帖子
     * @return void
     */
    public function delBBSArticle()
    {

        //请求参数校验
        empty($_POST['id']) && self::echoExit([], 'id不可为空', false);

        //请求方式校验
        empty($_POST) && self::echoExit([], '错误请求方式', false);

        $id = $_POST['id'];

        try {
            //mysql-开启事物
            $pdo = AnswerMysqlTools::getInstance([])->mysqlLink;
            $pdo->beginTransaction();
            //删除入mysql数据
            $res = AnswerMysqlTools::getInstance([])->delete($id);
            //var_dump($res);
            if ($res) {
                //删除redis数据
                $this->delRedis($id);
            } else {
                throw new \Exception("删除mysql数据失败,数据可能不存在");
            }

            //mysql-提交事物
            $pdo->commit();

            self::echoExit($res, '删除mysql数据成功', true);

        } catch (Exception $exception) {
            //mysql-回滚事物
            $pdo->rollback();
            self::echoExit($res, $exception->getMessage(), false);

        }

    }


    /**
     * 更新帖子
     * @return void
     */
    public function updateBBSArticle()
    {
        //请求参数校验
        empty($_POST['id']) && self::echoExit([], 'id不可为空', false);

        //请求方式校验
        !$_POST && self::echoExit([], '错误请求方式', false);

        $data = $_POST;
        empty($data) && self::echoExit([], '更新数据出错', false);

        try {
            //mysql-开启事物
            $pdo = AnswerMysqlTools::getInstance([])->mysqlLink;
            $pdo->beginTransaction();

            //先删除redis数据
            $this->delRedis($data['id']);

            //更新mysql数据
            $res = AnswerMysqlTools::getInstance([])->update($data);
            //var_dump($res);

            if ($res) {
                //mysql-提交事物
                $pdo->commit();

                //延迟双删
                sleep(0.5);
                $this->delRedis($data['id']);

            } else {
                throw new \Exception("更新mysql数据失败");
            }

            self::echoExit($res, '更新mysql数据成功', true);


        } catch (Exception $exception) {
            //mysql-回滚事物
            $pdo->rollback();
            self::echoExit($res, $exception->getMessage(), false);
        }


    }


    /**
     * 返回json数据
     * @param array $data
     * @param string $msg
     * @param bool $status
     */
    public function echoExit($data = array(), $msg = "", $code = true)
    {

        $returnParam = [
            'code' => $code ? 200 : 500,
            'msg' => $msg,
            'data' => $data,
        ];

        $res = json_encode($returnParam);

        header("Content-type:application/json;charset=utf-8");

        die($res);

    }


    /**
     * 数据入redis
     * @param $addMysqlId ...
     * @param $data
     * @return bool|string 值
     */
    public function insertRedis($addMysqlId, $data)
    {
        try {
            //写数据前加锁
            $lock_res = AnswerRedisTools::getInstance([])->lock('BBS_ARTICLE_INSERT_REDIS_SORT_SET', 10);
            //var_dump($lock_res);

            //获取锁成功,写入redis数据
            if ($lock_res) {

                $insert_hash_res = AnswerRedisTools::getInstance([])->setHash($addMysqlId, json_decode($data, true));
                $insert_zset_res = AnswerRedisTools::getInstance([])->zadd_($addMysqlId, $addMysqlId, '');
//                var_dump($insert_hash_res,$insert_zset_res);

//                if ($insert_hash_res && ($insert_zset_res || $insert_zset_res == 0)) {
                if ($insert_hash_res && $insert_zset_res) {
                    $unlock_res = AnswerRedisTools::getInstance([])->unLock('BBS_ARTICLE_INSERT_REDIS_SORT_SET', $lock_res);
//                    var_dump($unlock_res);
                    if ($unlock_res) {
                        return true;
                    } else {
                        throw new \RedisException("redis写入数据-释放锁失败");
                    }
                }
            } else {
                throw new \RedisException("redis写入数据-获取锁失败");

            }


        } catch (\RedisException $redisException) {
            return false;
        }
    }

    /**
     * 删除redis数据
     * @param $id
     * @return void
     * @throws RedisException
     */
    public function delRedis($id)
    {
        //删除有续集合中的数据
        AnswerRedisTools::getInstance([])->zrem('', $id);
        //删除hash中的数据
        AnswerRedisTools::getInstance([])->hdel($id, 'id', ...self::TABLE_FIELDS);

    }


    //锁测试
    public function test()
    {
        $res = AnswerRedisTools::getInstance([])->lock('BBS_ARTICLE_INSERT_REDIS_SORT_SET', 10);
        //return $res;
        if ($res)
            AnswerRedisTools::getInstance([])->unLock('BBS_ARTICLE_INSERT_REDIS_SORT_SET', $res);

    }

}

$indexRedis = new IndexRedis;


//新增帖子
//$indexRedis->addBBSArticle();

//详情
//$indexRedis->getBBSArticleById();

//获取帖子列表
$indexRedis->getBBSArticleList();

//删除帖子
//$indexRedis->delBBSArticle();

//更新
//$indexRedis->updateBBSArticle();



