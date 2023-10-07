<?php
require __DIR__ . '/../vendor/autoload.php';
require_once 'AnswerMysqlTools.php';

use Elastic\Elasticsearch\ClientBuilder;

/**
 * es操作类
 */
class AnswerElasticTools

{

    private static $instance = NULL;

    public $client;

    private $username = 'elastic';

    private $password = '7j1TEQyVyoVLJ5G4SXM3NcH6Z';

    private $host = 'https://localhost';

    private $port = ':9200';

    //证书路径
    private $sceretPath = '../cert/es801.crt';

    //索引名称(表名)
    public static $index = 'tiezi';

    /**
     * 构造方法 初始化es对象
     * @param $config
     */
    private function __construct($config)
    {

        !empty($config['host']) && $this->host = $config['host'];
        !empty($config['username']) && $this->username = $config['username'];
        !empty($config['password']) && $this->password = $config['password'];
        !empty($config['port']) && $this->port = $config['port'];


        try {

            $this->client = ClientBuilder::create()
                ->setHosts([$this->host . $this->port])
                ->setBasicAuthentication($this->username, $this->password)
                ->setCABundle($this->sceretPath)
                ->build();

        } catch (\Exception $e) {

            die ("es连接出错!: " . $e->getMessage() . "<br/>");

        }

    }

    /**
     * 单例模式获取es对象
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
     * 创建索引
     * @param $index ...索引名称
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function createIndex($index)
    {

        $params = [
            'index' => $index
        ];

        $response = AnswerElasticTools::getInstance([])->client->indices()->create($params);

        return $response;

    }

    /**
     * 原生sql查询
     * @param $sql ... sql string
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function sqlQuery($sql)
    {

        $params = [
            'format' => 'json',
            'body' => [
                'query' => $sql,
            ]
        ];

        $response = AnswerElasticTools::getInstance([])->client->sql()->query($params);

        return $response;

    }


    /**
     * 删除索引
     * @param $index ...索引名称
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function deleteIndex($index)
    {

        $params = [
            'index' => $index
        ];

        $response = AnswerElasticTools::getInstance([])->client->indices()->delete($params);

        return $response;

    }

    /**
     * index文档 文档ID随机生成
     * 对指定的文档进行索引。 如果文档存在，则替换文档并递增版本
     * Creates or updates a document in an index.
     * @param $index ...索引名称
     * @param $document ...文档信息
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function indexDocument($document, $index)
    {

        $params = [
            'id' => $document['id'],
            'index' => $index,
            'body' => $document
        ];

        $response = AnswerElasticTools::getInstance([])->client->index($params);

        return $response;

    }

    /**
     * create文档 指定文档ID
     * 如果指定的文档不存在，则对其进行索引
     * Creates a new document in the index.
     * @param $index ...索引名称
     * @param $document ...文档信息
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function createDocument($document, $index)
    {
        $params = [
            'id' => $document['id'],
            'index' => $index,
            'body' => $document
        ];

        $response = AnswerElasticTools::getInstance([])->client->create($params);

        return $response;

    }


    /**
     * 文档详情
     * @param $index ...索引名称
     * @param $documentId ...文档ID
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function getDocumentById($documentId, $index)
    {

        $params = [
            'index' => $index,
            'id' => $documentId
        ];

        $response = AnswerElasticTools::getInstance([])->client->get($params);

        return $response;

    }

    /**
     * 多条文档 by ids
     * @param $index ...索引名称
     * @param $documentIds ...文档ID
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function getDocumentByIds($documentIds, $index)
    {

        $params = [
            'index' => $index,
            'body' => [
                'ids' => $documentIds
            ]
        ];

        $response = AnswerElasticTools::getInstance([])->client->mget($params);

        return $response;

    }


    /**
     * 删除文档
     * @param $index ...索引名称
     * @param $documentId ...文档ID
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function deleteDocument($documentId, $index)
    {

        $params = [
            'index' => $index,
            'id' => $documentId
        ];

        $response = AnswerElasticTools::getInstance([])->client->delete($params);

        return $response;

    }


    /**
     * 更新文档
     * @param $data ...要更新的数据
     * @param $index ...索引名称
     * @param $documentId ...文档ID
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function updateDocument($documentId, $index, $data)
    {
        $params = [
            'index' => $index,
            'id' => $documentId,
            'body' => [
                'doc' => $data,
            ]
        ];

        $response = AnswerElasticTools::getInstance([])->client->update($params);

        return $response;

    }

    /**
     * 查询文档
     * @param $data ...查询条件
     * @param $index ...索引名称
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function searchDocument($index, $data)
    {

//        $params = [
//            'index' => $index,
//            'body' => [
//                'query' => [
//                    'match' => $data
//                ]
//            ]
//        ];

        $params = [
            'index' => $index,
            'body' => $data
        ];


        $response = AnswerElasticTools::getInstance([])->client->search($params);

        return $response;

    }

    /**
     * @param $index ...索引名称
     * @param $method ...操作方法
     * @param $data
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function bulkDoc($index, $method)
    {


        switch ($method) {

            case 'index':
                return $this->bulkIndex($index, $method);

            case 'create':
                return $this->bulkCreate($index, $method);

            case 'update':
                return $this->bulkUpdate($index, $method);

            case 'delete':
                return $this->bulkDelete($index, $method);

            default :

        }


    }

    /**
     * 对指定的文档进行索引。 如果文档存在，则替换文档并递增版本
     * @param $index ...索引名称
     * @param $method ...操作方法
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function bulkIndex($index, $method)
    {
        $params = ['body' => []];

        for ($i = 1; $i <= 100; $i++) {
            $params['body'][] = [
                $method => [
                    '_index' => $index,
                    '_id' => $i
                ]
            ];

            $data = $this->productData();
            $data['id'] = $i;
            //添加结构
            $params['body'][] = $data;

            // Every 1000 documents stop and send the bulk request
            if ($i % 1000 == 0) {
                $response = AnswerElasticTools::getInstance([])->client->bulk($params);

                // erase the old bulk request
                $params = ['body' => []];

                // unset the bulk response when you are done to save memory
                unset($response);
            }
        }
        // Send the last batch if it exists
        if (!empty($params['body'])) {

            $response = AnswerElasticTools::getInstance([])->client->bulk($params);

        }

        return $response;
    }

    /**
     * 如果指定的文档不存在，则对其进行索引
     * @param $index ...索引名称
     * @param $method ...操作方法
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function bulkCreate($index, $method)
    {
        $params = ['body' => []];

        for ($i = 1; $i <= 100; $i++) {
            $params['body'][] = [
                $method => [
                    '_index' => $index,
                    '_id' => $i
                ]
            ];

            $data = $this->productData();
            $data['id'] = $i;
            //添加结构
            $params['body'][] = $data;

            // Every 1000 documents stop and send the bulk request
            if ($i % 1000 == 0) {
                $response = AnswerElasticTools::getInstance([])->client->bulk($params);

                // erase the old bulk request
                $params = ['body' => []];

                // unset the bulk response when you are done to save memory
                unset($response);
            }
        }
        // Send the last batch if it exists
        if (!empty($params['body'])) {

            $response = AnswerElasticTools::getInstance([])->client->bulk($params);

        }

        return $response;
    }

    /**
     * @param $index ...索引名称
     * @param $method ...操作方法
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function bulkUpdate($index, $method)
    {
        $params = ['body' => []];

        for ($i = 1; $i <= 50; $i++) {
            $params['body'][] = [
                $method => [
                    '_index' => $index,
                    '_id' => $i
                ]
            ];

            $data = $this->productData();
            $data['id'] = $i;
            //结构
            $params['body'][] = [
                'doc' => $data
            ];


            // Every 1000 documents stop and send the bulk request
            if ($i % 1000 == 0) {
                $response = AnswerElasticTools::getInstance([])->client->bulk($params);

                // erase the old bulk request
                $params = ['body' => []];

                // unset the bulk response when you are done to save memory
                unset($response);
            }
        }
        // Send the last batch if it exists
        if (!empty($params['body'])) {

            $response = AnswerElasticTools::getInstance([])->client->bulk($params);

        }

        return $response;
    }

    /**
     * @param $index ...索引名称
     * @param $method ...操作方法
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function bulkDelete($index, $method)
    {
        $params = ['body' => []];

        for ($i = 1; $i <= 100; $i++) {
            $params['body'][] = [
                $method => [
                    '_index' => $index,
                    '_id' => $i
                ]
            ];

            // Every 1000 documents stop and send the bulk request
            if ($i % 1000 == 0) {
                $response = AnswerElasticTools::getInstance([])->client->bulk($params);

                // erase the old bulk request
                $params = ['body' => []];

                // unset the bulk response when you are done to save memory
                unset($response);
            }
        }
        // Send the last batch if it exists
        if (!empty($params['body'])) {

            $response = AnswerElasticTools::getInstance([])->client->bulk($params);

        }

        return $response;
    }


    public function indexCount($index)
    {
        $params = [
            'index' => $index,
        ];

        $response = AnswerElasticTools::getInstance([])->client->count($params);


        return $response;
    }


    /**
     * mysql数据批量导入es
     * @param $index ...索引名称
     * @return void
     */
    public function mysqlToEs($index)
    {
        $dbData = AnswerMysqlTools::getInstance([])->select();

        if (empty($dbData)) {
            return null;
        }
        //return  $dbData[0];
        return $this->bulkIndexForMysql($index, 'index', $dbData);

    }

    /**
     * mysql数据批量导入es--逻辑
     * @param $index
     * @param $method
     * @param $data
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function bulkIndexForMysql($index, $method, $data)
    {
        //$ids = array_column($data, "id");

        $params = ['body' => []];

        for ($i = 0; $i < count($data); $i++) {
            $params['body'][] = [
                $method => [
                    '_index' => $index,
                    '_id' => $data[$i]['id']
                ]
            ];

            //添加结构
            $params['body'][] = $data[$i];


            // Every 1000 documents stop and send the bulk request
            if ($i % 1000 == 0) {
                $response = AnswerElasticTools::getInstance([])->client->bulk($params);

                // erase the old bulk request
                $params = ['body' => []];

                // unset the bulk response when you are done to save memory
                unset($response);
            }
        }
        // Send the last batch if it exists
        if (!empty($params['body'])) {

            $response = AnswerElasticTools::getInstance([])->client->bulk($params);

        }

        return $response;
    }


    /**
     * 随机字符串-跑批用
     * @param $length
     * @return string
     */
    public function generateRandomString($length = 10)
    {
        $bytes = openssl_random_pseudo_bytes($length);
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    /**
     * ip生产-跑批用
     * @return false|string
     */
    public function ip()
    {
        $ip_long = array(
            array('607649792', '608174079'), // 36.56.0.0-36.63.255.255
            array('1038614528', '1039007743'), // 61.232.0.0-61.237.255.255
            array('1783627776', '1784676351'), // 106.80.0.0-106.95.255.255
            array('2035023872', '2035154943'), // 121.76.0.0-121.77.255.255
            array('2078801920', '2079064063'), // 123.232.0.0-123.235.255.255
            array('-1950089216', '-1948778497'), // 139.196.0.0-139.215.255.255
            array('-1425539072', '-1425014785'), // 171.8.0.0-171.15.255.255
            array('-1236271104', '-1235419137'), // 182.80.0.0-182.92.255.255
            array('-770113536', '-768606209'), // 210.25.0.0-210.47.255.255
            array('-569376768', '-564133889'), // 222.16.0.0-222.95.255.255
        );
        $rand_key = mt_rand(0, 9);
        return $ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    }

    /**
     * 生成数据
     * @return array
     */
    public function productData()
    {
        $data = [];
        $data['subject'] = $this->generateRandomString(10);
        $data['author'] = $this->generateRandomString(6);
        $data['replies'] = rand(1, 100);
        $data['body'] = $this->generateRandomString(32);
        $data['idate'] = date('Y-m-d :H:i:s', time());
        $data['ndate'] = date('Y-m-d :H:i:s', time());
        $data['ip'] = $this->ip();
        return $data;
    }

}






