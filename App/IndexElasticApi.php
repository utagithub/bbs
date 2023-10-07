<?php

//自动加载工具类-需要开启redis扩展（laravel框架中可能已关闭,这里测试使用需要重新开启）
//require __DIR__ . '/../vendor/autoload.php';
require_once 'AnswerElasticTools.php';

class IndexElasticApi

{

    /**
     * 创建索引
     * $index 索引名称
     * @return void
     */
    public function createEsIndex()
    {

        //请求参数校验
        empty($_POST['index']) && self::echoExit([], 'index不可为空', false);

        //请求方式校验
        !$_POST && self::echoExit([], '错误请求方式', false);

        try {

            $response = AnswerElasticTools::getInstance([])->createIndex($_POST['index']);

            self::echoExit($response->asArray(), 'ES_CFREATE_INDEX_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_CFREATE_INDEX_FAILURE', false);


        }

    }

    /**
     * 删除索引
     * @return void
     */
    public function deleteEsIndex()
    {

        //请求参数校验
        empty($_POST['index']) && self::echoExit([], 'index不可为空', false);

        //请求方式校验
        !$_POST && self::echoExit([], '错误请求方式', false);

        try {

            $response = AnswerElasticTools::getInstance([])->deleteIndex($_POST['index']);

            self::echoExit($response->asArray(), 'ES_DELETE_INDEX_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_DELETE_INDEX_FAILURE', false);
        }

    }


    /**
     * index文档
     *  对指定的文档进行索引。 如果文档存在，则替换文档并递增版本
     *  Creates or updates a document in an index.
     * @return void
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function indexDocument()
    {

        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        //请求参数校验
        !array_key_exists('subject', $data) && self::echoExit([], 'subject必传', false);
        !array_key_exists('author', $data) && self::echoExit([], 'author必传', false);
        !array_key_exists('idate', $data) && self::echoExit([], 'idate必传', false);
        !array_key_exists('replies', $data) && self::echoExit([], 'repliest必传', false);
        !array_key_exists('body', $data) && self::echoExit([], 'body必传', false);
        !array_key_exists('ndate', $data) && self::echoExit([], 'ndate必传', false);
        !array_key_exists('ip', $data) && self::echoExit([], 'ip必传', false);

        empty($data['subject']) && self::echoExit([], 'subject不可为空', false);
        empty($data['author']) && self::echoExit([], 'author不可为空', false);
        empty($data['idate']) && self::echoExit([], 'idate不可为空', false);
        empty($data['replies']) && self::echoExit([], 'replies不可为空', false);
        empty($data['body']) && self::echoExit([], 'body不可为空', false);
        empty($data['ndate']) && self::echoExit([], 'ndate不可为空', false);
        empty($data['ip']) && self::echoExit([], 'ip不可为空', false);


        //请求方式校验
        //!$_POST && self::echoExit([], '错误请求方式', false);

        try {

            $response = AnswerElasticTools::getInstance([])->indexDocument($data, AnswerElasticTools::$index);

            self::echoExit($response->asArray(), 'ES_INDEX_DOC_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_INDEX_DOC_FAILURE', false);
        }

    }

    /**
     * create文档
     *  如果指定的文档不存在，则对其进行索引
     *  Creates a new document in the index.
     * @return void
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function createDocument()
    {

        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        //请求参数校验
        !array_key_exists('id', $data) && self::echoExit([], 'id必传', false);
        !array_key_exists('subject', $data) && self::echoExit([], 'subject必传', false);
        !array_key_exists('author', $data) && self::echoExit([], 'author必传', false);
        !array_key_exists('idate', $data) && self::echoExit([], 'idate必传', false);
        !array_key_exists('replies', $data) && self::echoExit([], 'repliest必传', false);
        !array_key_exists('body', $data) && self::echoExit([], 'body必传', false);
        !array_key_exists('ndate', $data) && self::echoExit([], 'ndate必传', false);
        !array_key_exists('ip', $data) && self::echoExit([], 'ip必传', false);

        empty($data['id']) && self::echoExit([], 'id不可为空', false);
        empty($data['subject']) && self::echoExit([], 'subject不可为空', false);
        empty($data['author']) && self::echoExit([], 'author不可为空', false);
        empty($data['idate']) && self::echoExit([], 'idate不可为空', false);
        empty($data['replies']) && self::echoExit([], 'replies不可为空', false);
        empty($data['body']) && self::echoExit([], 'body不可为空', false);
        empty($data['ndate']) && self::echoExit([], 'ndate不可为空', false);
        empty($data['ip']) && self::echoExit([], 'ip不可为空', false);


        //请求方式校验
        //!$_POST && self::echoExit([], '错误请求方式', false);

        try {

            $response = AnswerElasticTools::getInstance([])->createDocument($data, AnswerElasticTools::$index);

            self::echoExit($response->asArray(), 'ES_CFREATE_DOC_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_CFREATE_DOC_FAILURE', false);
        }

    }


    /**
     * 文档详情
     * @return void
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function getDocumentById()
    {


        empty($_POST['id']) && self::echoExit([], '文档id不可为空', false);


        //请求方式校验
        !$_POST && self::echoExit([], '错误请求方式', false);

        try {

            $response = AnswerElasticTools::getInstance([])->getDocumentById($_POST['id'], AnswerElasticTools::$index);

            self::echoExit($response->asArray(), 'ES_GET_DOC_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_GET_DOC_FAILURE', false);
        }

    }

    /**
     * 多条文档 by ids
     * @return void
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function getDocumentByIds()
    {


        empty($_POST['ids']) && self::echoExit([], '文档ids不可为空', false);

        //请求方式校验
        !$_POST && self::echoExit([], '错误请求方式', false);

        try {

            $response = AnswerElasticTools::getInstance([])->getDocumentByIds(json_decode($_POST['ids'], true), AnswerElasticTools::$index);

            self::echoExit($response->asArray(), 'ES_GET_DOCS_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_GET_DOCS_FAILURE', false);
        }

    }


    /**
     * 删除文档
     * @return void
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function deleteDocument()
    {


        empty($_POST['id']) && self::echoExit([], '文档id不可为空', false);


        //请求方式校验
        !$_POST && self::echoExit([], '错误请求方式', false);

        try {

            $response = AnswerElasticTools::getInstance([])->deleteDocument($_POST['id'], AnswerElasticTools::$index);

            self::echoExit($response->asArray(), 'ES_DELETE_DOC_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $clientResponseException->getMessage();

            $index = strpos($message, '{', 0);

            $message = substr($message, $index, strlen($message));

            $message = json_decode($message, true);

            self::echoExit($message, 'ES_DELETE_DOC_FAILURE', false);
        }

    }

    /**
     * 更新文档
     * @return void
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function updateDocument()
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        !array_key_exists('id', $data) && self::echoExit([], '文档id必传', false);
        empty($data['id']) && self::echoExit([], 'id不可为空', false);


        //请求方式校验
        //!$_POST && self::echoExit([], '错误请求方式', false);

        try {
            //$id = $data['id'];
            //unset($data['id']);
            $response = AnswerElasticTools::getInstance([])->updateDocument($data['id'], AnswerElasticTools::$index, $data);

            self::echoExit($response->asArray(), 'ES_UPDATE_DOC_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_UPDATE_DOC_FAILURE', false);
        }

    }

    /**
     * 查询文档-根据查询结果看因该是模糊查询
     * @return void
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function searchDocument()
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        empty($data) && self::echoExit([], '请选择查询条件', false);

        try {

            $response = AnswerElasticTools::getInstance([])->searchDocument(AnswerElasticTools::$index, $data);
            //var_dump($response);

            self::echoExit($response->asArray(), 'ES_SEARCH_DOC_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_SEARCH_DOC_FAILURE', false);
        }

    }

    /**
     * 批处理文档 增删改查
     * @return void
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function bulkDoc()
    {

        !array_key_exists('bulkDocMethod', $_POST) && self::echoExit([], '批量操作方法标识必传', false);
        empty($_POST['bulkDocMethod']) && self::echoExit([], '批量操作方法标识不能为空', false);

        //请求方式校验
        !$_POST && self::echoExit([], '错误请求方式', false);

        try {

            $response = AnswerElasticTools::getInstance([])->bulkDoc(AnswerElasticTools::$index, $_POST['bulkDocMethod']);
//            var_dump($response);die();

            self::echoExit($response->asArray(), 'ES_BLUCK_DOC_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_BLUCK_DOC_FAILURE', false);
        }

    }

    /**
     * mysql数据批量导入es
     * @return void
     */
    public function mysqlToEs()
    {
        try {

            $response = AnswerElasticTools::getInstance([])->mysqlToEs(AnswerElasticTools::$index);

            self::echoExit($response->asArray(), 'ES_TO_MYSQL_SUCCESS', true);

        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_TO_MYSQL_FAILURE', false);
        }
    }

    /**
     * 原生sql查询
     * @return void
     */
    public function sqlQuery()
    {

        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        empty($data) && self::echoExit([], '请输入查询sql', false);


        try {

            //$sql = " select * from " . AnswerElasticTools::$index . ' order by id desc limit 1 ';

            $response = AnswerElasticTools::getInstance([])->sqlQuery($data['sql']);

            self::echoExit($response->asArray(), 'ES_SQL_QUERY_SUCCESS', true);


        } catch (Elastic\Elasticsearch\Exception\ClientResponseException $clientResponseException) {

            $message = $this->formatTool($clientResponseException->getMessage());

            self::echoExit($message, 'ES_SQL_QUERY_FAILURE', false);
        }

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


}

$indexElasticApi = new IndexElasticApi();

$indexElasticApi->sqlQuery();

//$indexElasticApi->mysqlToEs();

//$indexElasticApi->bulkDoc();

//$indexElasticApi->searchDocument();

//$indexElasticApi->updateDocument();

//$indexElasticApi->deleteDocument();

//$indexElasticApi->getDocumentById();

//$indexElasticApi->getDocumentByIds();

//$indexElasticApi->indexDocument();

//$indexElasticApi->createDocument();

//$indexElasticApi->createEsIndex();

//$indexElasticApi->deleteEsIndex();




