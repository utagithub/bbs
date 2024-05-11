<?php
//自动加载工具类-需要开启redis扩展（laravel框架中可能已关闭,这里测试使用需要重新开启）
require_once 'AnswerMysqlTools.php';
require_once 'AnswerElasticTools.php';

class IndexEs

{


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

            //数据入es
            if ($addMysqlId) {
                $response = AnswerElasticTools::getInstance([])->createDocument(array_merge($info, ['id' => $addMysqlId]), AnswerElasticTools::$index);
            } else {
                throw new \Exception('mysql入库失败');
            }
            //mysql-提交事物
            $pdo->commit();
            //self::echoExit($response->asArray(), '发帖成功', true);
            self::echoExit(array_merge($info, ['id' => $addMysqlId]), '发帖成功', true);

        } catch (Exception $exception) {

            //mysql 入库失败
            if (!$addMysqlId) {
                $pdo->rollback();
            } else {
                $message = $this->formatTool($exception->getMessage());
                //mysql入库成功 es入库失败
                if ($addMysqlId && $message['result'] !== 'created') {
                    //mysql-回滚事物
                    $pdo->rollback();
                }
            }

            self::echoExit(null, $exception->getMessage(), false);

        }


    }

    /**
     * 获取列表数据
     * @return array
     */

    public function getBBSArticleList()
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        //页数
        $page = !empty($data['page']) ? $data['page'] : 1;
        //每页数据量
        $size = !empty($data['size']) ? $data['size'] : 5;

        //偏移量-开始
        $from = ($page - 1) * $size;
        //偏移量-结束
        //$end = ($offset + $size) - 1;

        $search = $data['search'];
        $search['from'] = $from;
        $search['size'] = $size;

//        $total = AnswerMysqlTools::getInstance([])-> count();
//        $list  = AnswerMysqlTools::getInstance([])-> paginate($from,$size,'desc');
//        $allPage = (int)(ceil($total['count'] / $size));
//
//        self::echoExit($list, 'SUCCESS', true);


        //数据总量
        $total = AnswerElasticTools::getInstance([])->indexCount(AnswerElasticTools::$index);
        $total = $total->asArray();

        //总页码
        $allPage = (int)(ceil($total['count'] / $size));
        //取缓存数据
        $redisDataList = AnswerElasticTools::getInstance([])->searchDocument(AnswerElasticTools::$index, $search);

        //self::echoExit($redisDataList->asArray(), 'SUCCESS', true);
        $redisDataList->asArray();

        //格式化数据,如果数据存了hash，这里可以这样获取
        $list = [];
        if (!empty($redisDataList['hits']['hits'])) {
            foreach ($redisDataList['hits']['hits'] as $v) {
                $list[] = $v['_source'];
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
//                'total' => $total['count'],
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

        try {

            //检测数据在es中是否存在
            $response = AnswerElasticTools::getInstance([])->getDocumentById($_REQUEST['id'], AnswerElasticTools::$index);
            //es有数据
            if ($response['found'] === true) {
                self::echoExit($response['_source'], 'ES_FIND_SUCCESS', true);
            }

            // PS 这里注意如果es查到数据则正常响应,es中没有查到数据自动抛异常,所以es没有数据的情况在catch中处理

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $db_res = AnswerMysqlTools::getInstance([])->find($_REQUEST['id']);

            if ($db_res) {
                //数据入es
                $insertEsResult = AnswerElasticTools::getInstance([])->createDocument($db_res, AnswerElasticTools::$index);
                $insertEsResult = $insertEsResult->asArray();
                if ($insertEsResult['result'] === 'created') {
                    self::echoExit($db_res, '数据入es成功', true);
                } else {
                    throw new Elastic\Elasticsearch\Exception\ClientResponseException();
                }
            } else {
                self::echoExit(null, '数据库没有查询到该数据', false);
            }

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_FIND_FAILURE', false);

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

            $response = AnswerElasticTools::getInstance([])->deleteDocument($id, AnswerElasticTools::$index);
            /*$response = $response->asArray();
            if($response['result' == 'deleted']){
                $msg = '删除es数据成功';
                $pdo->commit();
            }*/

            // PS 这里注意如果es有数据则删除正常响应,es中没有查到数据自动抛异常,所以es没有数据的情况在catch中处理

            //mysql-提交事物
            $pdo->commit();

            self::echoExit($res, '删除mysql and es数据成功', true);

        } catch (Exception $exception) {

            $message = $this->formatTool($exception->getMessage());
            //es 没有数据,mysql有数据,删除mysql数据
            if ($res && $message['result'] == 'not_found') {
                $pdo->commit();
                self::echoExit($message, 'es not_found data, delete mysql data success', true);
            } elseif (!$res && $message['result'] == 'not_found') {
                //mysql-回滚事物
                $pdo->rollback();
                self::echoExit($message, 'es and mysql not_found data', true);
            }

        }

    }


    /**
     * 更新帖子
     * @return void
     */
    public function updateBBSArticle()
    {

        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        //请求参数校验
        (!isset($data['id']) || empty($data['id'])) && self::echoExit([], 'id不可为空', false);


        $flag = 0;
        try {
            //mysql-开启事物
            $pdo = AnswerMysqlTools::getInstance([])->mysqlLink;
            $pdo->beginTransaction();

            //一删除es数据,如果es没有数据,自动抛出异常
            $response_1 = AnswerElasticTools::getInstance([])->deleteDocument($data['id'], AnswerElasticTools::$index);
            //self::echoExit($response_1->asArray(), 'es数据删除成功,mysql数据更新失败', true);

            //更新mysql数据
            $result = AnswerMysqlTools::getInstance([])->update($data);

            if ($result) {
                //延迟双删
                sleep(0.5);
                $flag++;
                $response_2 = AnswerElasticTools::getInstance([])->deleteDocument($data['id'], AnswerElasticTools::$index);

                //es 二次删除成功,mysql-提交事物
                $pdo->commit();
                self::echoExit(null, 'es数据双删成功,mysql数据更新成功', true);
            } else {
                $pdo->rollback();
                self::echoExit(null, 'es数据删除成功,mysql数据更新失败', true);
            }


        } catch (Exception $exception) {

            $message = $this->formatTool($exception->getMessage());

            // es第一次删除失败(可能是应为删除的时候没有es数据,自动抛出异常)
            if ($flag == 0 && $message['result'] == 'not_found') {
                //更新mysql数据
                $res = AnswerMysqlTools::getInstance([])->update($data);
                if ($res) {
                    $pdo->commit();
                    self::echoExit($res, 'es not_found data, update mysql data success', true);
                } else {
                    $pdo->rollback();
                    self::echoExit($res, 'es not_found data, update mysql data failure', true);
                }
            } elseif ($flag == 1 && $message['result'] == 'not_found') {
                // es一删除成功,二删失败(可能是应为删除的时候没有es数据,自动抛出异常)
                //提交事物,更新mysql数据
                $pdo->commit();
                self::echoExit($result, 'es delete data first success and secoond failure, update mysql data success', true);

            }

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
     * 异常相应数据格式化
     * @param $message
     * @return mixed
     */
    public function formatTool($message)
    {

        $index = strpos($message, '{', 0);

        $message = substr($message, $index, strlen($message));

        return json_decode($message, true);

    }


}

$indexEs = new IndexEs;


//新增帖子
//$indexEs->addBBSArticle();

//详情
//$indexEs->getBBSArticleById();

//获取帖子列表
$indexEs->getBBSArticleList();

//删除帖子
//$indexEs->delBBSArticle();

//更新
$indexEs->updateBBSArticle();



