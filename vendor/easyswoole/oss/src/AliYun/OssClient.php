<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/11/14 0014
 * Time: 15:03
 */

namespace EasySwoole\Oss\AliYun;


use EasySwoole\Oss\AliYun\Core\MimeTypes;
use EasySwoole\Oss\AliYun\Core\OssException;
use EasySwoole\Oss\AliYun\Core\OssUtil;
use EasySwoole\Oss\AliYun\Http\HttpClient;
use EasySwoole\Oss\AliYun\Http\Response;
use EasySwoole\Oss\AliYun\Http\RequestHeaders;
use EasySwoole\Oss\AliYun\Model\BucketListInfo;
use EasySwoole\Oss\AliYun\Model\CnameConfig;
use EasySwoole\Oss\AliYun\Model\CorsConfig;
use EasySwoole\Oss\AliYun\Model\ExtendWormConfig;
use EasySwoole\Oss\AliYun\Model\InitiateWormConfig;
use EasySwoole\Oss\AliYun\Model\LoggingConfig;
use EasySwoole\Oss\AliYun\Model\RequestPaymentConfig;
use EasySwoole\Oss\AliYun\Model\StorageCapacityConfig;
use EasySwoole\Oss\AliYun\Model\TaggingConfig;
use EasySwoole\Oss\AliYun\Model\VersioningConfig;
use EasySwoole\Oss\AliYun\Model\WebsiteConfig;
use EasySwoole\Oss\AliYun\Result\DeleteObjectVersionsResult;
use EasySwoole\Oss\AliYun\Result\ExistResult;
use EasySwoole\Oss\AliYun\Result\GetBucketEncryptionResult;
use EasySwoole\Oss\AliYun\Result\GetBucketInfoResult;
use EasySwoole\Oss\AliYun\Result\GetBucketRequestPaymentResult;
use EasySwoole\Oss\AliYun\Result\GetBucketStatResult;
use EasySwoole\Oss\AliYun\Result\GetBucketTagsResult;
use EasySwoole\Oss\AliYun\Result\GetBucketVersioningResult;
use EasySwoole\Oss\AliYun\Result\GetBucketWormResult;
use EasySwoole\Oss\AliYun\Result\GetLocationResult;
use EasySwoole\Oss\AliYun\Result\InitiateBucketWormResult;
use EasySwoole\Oss\AliYun\Result\ListBucketsResult;
use EasySwoole\Oss\AliYun\Result\ListObjectVersionsResult;
use EasySwoole\Oss\AliYun\Result\PutSetDeleteResult;
use EasySwoole\HttpClient\Exception\InvalidUrl;
use EasySwoole\Oss\AliYun\Result\AclResult;
use EasySwoole\Oss\AliYun\Result\BodyResult;
use EasySwoole\Oss\AliYun\Result\GetCorsResult;
use EasySwoole\Oss\AliYun\Result\GetLifecycleResult;
use EasySwoole\Oss\AliYun\Result\GetLoggingResult;
use EasySwoole\Oss\AliYun\Result\GetRefererResult;
use EasySwoole\Oss\AliYun\Result\GetWebsiteResult;
use EasySwoole\Oss\AliYun\Result\GetCnameResult;
use EasySwoole\Oss\AliYun\Result\HeaderResult;
use EasySwoole\Oss\AliYun\Result\InitiateMultipartUploadResult;
use EasySwoole\Oss\AliYun\Result\ListMultipartUploadResult;
use EasySwoole\Oss\AliYun\Result\SymlinkResult;
use EasySwoole\Oss\AliYun\Result\TaggingResult;
use EasySwoole\Oss\AliYun\Result\UploadPartResult;
use EasySwoole\Oss\AliYun\Result\ListObjectsResult;
use EasySwoole\Oss\AliYun\Result\ListPartsResult;
use EasySwoole\Oss\AliYun\Result\DeleteObjectsResult;
use EasySwoole\Oss\AliYun\Result\CopyObjectResult;
use EasySwoole\Oss\AliYun\Result\CallbackResult;
use EasySwoole\Oss\AliYun\Result\PutLiveChannelResult;
use EasySwoole\Oss\AliYun\Result\GetLiveChannelHistoryResult;
use EasySwoole\Oss\AliYun\Result\GetLiveChannelInfoResult;
use EasySwoole\Oss\AliYun\Result\GetLiveChannelStatusResult;
use EasySwoole\Oss\AliYun\Result\ListLiveChannelResult;
use EasySwoole\Oss\AliYun\Result\GetStorageCapacityResult;
use EasySwoole\Oss\AliYun\Result\AppendResult;
use EasySwoole\Spl\SplFileStream;
use Swoole\Coroutine;

class OssClient
{
    /**
     * @var $config Config
     */
    protected $config;

    protected $requestUrl;

    protected $hostname;


    // ??????????????????????????????????????? OSS_HOST_TYPE_NORMAL, OSS_HOST_TYPE_IP, OSS_HOST_TYPE_SPECIAL, OSS_HOST_TYPE_CNAME
    protected $hostType = OssConst::OSS_HOST_TYPE_NORMAL;

    protected $enableStsInUrl = false;

    protected $securityToken = null;
    //????????????
    protected $timeout = 0;
    //??????????????????
    protected $connectTimeout = 0;

    //????????????ssl
    private $useSSL = false;
    //??????????????????
    private $maxRetries = 3;
    //??????????????????
    protected $requestProxy = null;// $requestProxy=['127.0.0.1','8080','user','pass']

    public function __construct(Config $config, $securityToken = NULL, $requestProxy = null)
    {
        $this->config = $config;
        $this->requestProxy = $requestProxy;
        $this->securityToken = $securityToken;
        $this->hostname = $this->checkEndpoint();
    }

    ##############################????????????######################################

    ###########################buket??????#################################
    /**
     * listBuckets
     * @param array $options
     * @return mixed
     * @throws OssException
     * @throws InvalidUrl
     * @author Tioncico
     * Time: 15:30
     */
    public function listBuckets(array $options = []): BucketListInfo
    {
        if ($this->hostType === OssConst::OSS_HOST_TYPE_CNAME) {
            throw new OssException("operation is not permitted with CName host");
        }
        OssUtil::validateOptions($options);
        $options[OssConst::OSS_BUCKET] = '';
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $response = $this->auth($options);
        $result = new ListBucketsResult($response);
        return $result->getData();
    }

    /**
     * ??????bucket??????????????????bucket???ACL???OssClient::OSS_ACL_TYPE_PRIVATE
     *
     * @param string $bucket
     * @param string $acl
     * @param array  $options
     * @param string $storageType
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function createBucket($bucket, $acl = OssConst::OSS_ACL_TYPE_PRIVATE, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_HEADERS] = array(OssConst::OSS_ACL => $acl);
        if (isset($options[OssConst::OSS_STORAGE])) {
            $this->preCheckStorage($options[OssConst::OSS_STORAGE]);
            $options[OssConst::OSS_CONTENT] = OssUtil::createBucketXmlBody($options[OssConst::OSS_STORAGE]);
            unset($options[OssConst::OSS_STORAGE]);
        }
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????bucket
     * ??????Bucket????????????Bucket??????Object??????????????????????????????????????????Bucket???????????????
     * ????????????Bucket????????????Object??????????????????Bucket?????????????????????
     *
     * @param string $bucket
     * @param array  $options
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function deleteBucket($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = '/';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????bucket????????????
     *
     * @param string $bucket
     * @return bool
     * @throws InvalidUrl
     * @throws OssException
     */
    public function doesBucketExist($bucket)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new ExistResult($response);

        return $result->getData();
    }

    /**
     * ??????bucket?????????????????????????????????
     *
     * @param string $bucket
     * @param array  $options
     * @return string
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getBucketLocation($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'location';
        $response = $this->auth($options);
        $result = new GetLocationResult($response);
        return $result->getData();
    }

    /**
     * ??????Bucket???Meta??????
     *
     * @param string $bucket
     * @param array  $options ????????????SDK??????
     * @return array
     * @throws InvalidUrl
     * @throws OssException
     */
    public function getBucketMeta($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_HEAD;
        $options[OssConst::OSS_OBJECT] = '/';
        $response = $this->auth($options);
        $result = new HeaderResult($response);

        return $result->getData();
    }

    /**
     * ??????bucket???ACL????????????
     *
     * @param string $bucket
     * @param array  $options
     * @return string
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getBucketAcl($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new AclResult($response);
        return $result->getData();
    }

    /**
     * ??????bucket???ACL????????????
     *
     * @param string $bucket bucket??????
     * @param string $acl ???????????????????????? ['private', 'public-read', 'public-read-write']
     * @param array  $options ????????????
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function putBucketAcl($bucket, $acl, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_HEADERS] = array(OssConst::OSS_ACL => $acl);
        $options[OssConst::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????Bucket???????????????????????????
     *
     * @param string $bucket bucket??????
     * @param array  $options ????????????
     * @return LoggingConfig
     * @throws InvalidUrl
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getBucketLogging($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'logging';
        $response = $this->auth($options);
        $result = new GetLoggingResult($response);
        return $result->getData();
    }

    /**
     * ??????Bucket?????????????????????????????????Bucket????????????????????????
     *
     * @param string $bucket bucket??????
     * @param string $targetBucket ?????????????????????bucket
     * @param string $targetPrefix ?????????????????????
     * @param array  $options ????????????
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function putBucketLogging($bucket, $targetBucket, $targetPrefix, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $this->preCheckBucket($targetBucket, 'targetbucket is not allowed empty');
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'logging';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';

        $loggingConfig = new LoggingConfig($targetBucket, $targetPrefix);
        $options[OssConst::OSS_CONTENT] = $loggingConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????bucket????????????????????????
     *
     * @param string $bucket bucket??????
     * @param array  $options ????????????
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function deleteBucketLogging($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'logging';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ???bucket?????????????????????????????????
     *
     * @param string        $bucket bucket??????
     * @param WebsiteConfig $websiteConfig
     * @param array         $options ????????????
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function putBucketWebsite($bucket, $websiteConfig, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'website';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $options[OssConst::OSS_CONTENT] = $websiteConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????bucket???????????????????????????
     *
     * @param string $bucket bucket??????
     * @param array  $options
     * @return WebsiteConfig
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getBucketWebsite($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'website';
        $response = $this->auth($options);
        $result = new GetWebsiteResult($response);
        return $result->getData();
    }

    /**
     * ??????bucket???????????????????????????
     *
     * @param string $bucket bucket??????
     * @param array  $options
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function deleteBucketWebsite($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'website';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ????????????bucket?????????????????????????????????(CORS)???????????????????????????????????????????????????
     *
     * @param string     $bucket bucket??????
     * @param CorsConfig $corsConfig ?????????????????????????????????????????????SDK??????
     * @param array      $options array
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function putBucketCors($bucket, $corsConfig, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'cors';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $options[OssConst::OSS_CONTENT] = $corsConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????Bucket???CORS????????????
     *
     * @param string $bucket bucket??????
     * @param array  $options ????????????
     * @return CorsConfig
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getBucketCors($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'cors';
        $response = $this->auth($options);
        $result = new GetCorsResult($response, __FUNCTION__);
        return $result->getData();
    }

    /**
     * ????????????Bucket?????????CORS???????????????????????????
     *
     * @param string $bucket bucket??????
     * @param array  $options
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function deleteBucketCors($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'cors';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ?????????Bucket??????CNAME??????
     *
     * @param string $bucket bucket??????
     * @param string $cname
     * @param array  $options
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function addBucketCname($bucket, $cname, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'cname';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $cnameConfig = new CnameConfig();
        $cnameConfig->addCname($cname);
        $options[OssConst::OSS_CONTENT] = $cnameConfig->serializeToXml();
        $options[OssConst::OSS_COMP] = 'add';

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ????????????Bucket????????????CNAME??????
     *
     * @param string $bucket bucket??????
     * @param array  $options
     * @return CnameConfig
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getBucketCname($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'cname';
        $response = $this->auth($options);
        $result = new GetCnameResult($response);
        return $result->getData();
    }

    /**
     * ????????????Bucket???CNAME??????
     *
     * @param string      $bucket bucket??????
     * @param CnameConfig $cnameConfig
     * @param array       $options
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function deleteBucketCname($bucket, $cname, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'cname';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $cnameConfig = new CnameConfig();
        $cnameConfig->addCname($cname);
        $options[OssConst::OSS_CONTENT] = $cnameConfig->serializeToXml();
        $options[OssConst::OSS_COMP] = 'delete';

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }


    /**
     * Sets the bucket's tags
     *
     * @param string $bucket bucket name
     * @param TaggingConfig $taggingConfig
     * @param array $options
     * @throws OssException
     * @return null
     */
    public function putBucketTags($bucket, $taggingConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = OssConst::OSS_TAGGING;
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $options[OssConst::OSS_CONTENT] = $taggingConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets bucket's tags
     *
     * @param string $bucket bucket name
     * @param array $options
     * @throws OssException
     * @return TaggingConfig
     */
    public function getBucketTags($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = OssConst::OSS_TAGGING;
        $response = $this->auth($options);
        $result = new GetBucketTagsResult($response);
        return $result->getData();
    }

    /**
     * Deletes the bucket's tags
     * If want to delete specified tags for a bucket, please set the $tags
     *
     * @param string $bucket bucket name
     * @param tag[] $tags (optional)
     * @param array $options
     * @throws OssException
     * @return null
     */
    public function deleteBucketTags($bucket, $tags = NULL, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = '/';
        if (empty($tags)) {
            $options[OssConst::OSS_SUB_RESOURCE] = OssConst::OSS_TAGGING;
        } else {
            $value = '';
            foreach ($tags as $tag ) {
                $value .= $tag->getKey().',';
            }
            $value = rtrim($value, ',');
            $options[OssConst::OSS_TAGGING] = $value;
        }
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    ###########################buket??????#################################

    ###########################object??????#################################
    /**
     * ??????object???ACL??????
     *
     * @param string $bucket
     * @param string $object
     * @return string
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getObjectAcl($bucket, $object)
    {
        $options = array();
        $this->preCheckCommon($bucket, $object, $options, true);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new AclResult($response);
        return $result->getData();
    }

    /**
     * ??????object???ACL??????
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @param string $acl ???????????????????????? ['default', 'private', 'public-read', 'public-read-write']
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function putObjectAcl($bucket, $object, $acl)
    {
        $this->preCheckCommon($bucket, $object, $options, true);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_HEADERS] = array(OssConst::OSS_OBJECT_ACL => $acl);
        $options[OssConst::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Sets the object tagging
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param TaggingConfig $taggingConfig
     * @throws OssException
     * @return null
     */
    public function putObjectTagging($bucket, $object, $taggingConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options, true);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_SUB_RESOURCE] = OssConst::OSS_TAGGING;
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $options[OssConst::OSS_CONTENT] = $taggingConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets the object tagging
     *
     * @param string $bucket
     * @param string $object
     * @throws OssException
     * @return TaggingConfig
     */
    public function getObjectTagging($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options, true);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_SUB_RESOURCE] = OssConst::OSS_TAGGING;
        $response = $this->auth($options);
        $result = new GetBucketTagsResult($response);
        return $result->getData();
    }

    /**
     * Deletes the object tagging
     *
     * @param string $bucket
     * @param string $object
     * @throws OssException
     * @return TaggingConfig
     */
    public function deleteObjectTagging($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options, true);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_SUB_RESOURCE] = OssConst::OSS_TAGGING;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Processes the object
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $process process script
     * @return string process result, json format
     */
    public function processObject($bucket, $object, $process, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_SUB_RESOURCE] = 'x-oss-process';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/octet-stream';
        $options[OssConst::OSS_CONTENT] = 'x-oss-process='.$process;
        $response = $this->auth($options);
        $result = new BodyResult($response);
        return $result->getData();
    }
    ###########################object??????#################################


    /**
     * ?????????Bucket??????LiveChannel
     *
     * @param string            $bucket bucket??????
     * @param string channelName  $channelName
     * @param LiveChannelConfig $channelConfig
     * @param array             $options
     * @return LiveChannelInfo
     * @throws OssException
     * @throws InvalidUrl
     */
    public function putBucketLiveChannel($bucket, $channelName, $channelConfig, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = $channelName;
        $options[OssConst::OSS_SUB_RESOURCE] = 'live';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $options[OssConst::OSS_CONTENT] = $channelConfig->serializeToXml();

        $response = $this->auth($options);
        $result = new PutLiveChannelResult($response);
        $info = $result->getData();
        $info->setName($channelName);
        $info->setDescription($channelConfig->getDescription());

        return $info;
    }

    /**
     * ??????LiveChannel???status
     *
     * @param string $bucket bucket??????
     * @param string channelName $channelName
     * @param string channelStatus $channelStatus ???enabled???disabled
     * @param array  $options
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function putLiveChannelStatus($bucket, $channelName, $channelStatus, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = $channelName;
        $options[OssConst::OSS_SUB_RESOURCE] = 'live';
        $options[OssConst::OSS_LIVE_CHANNEL_STATUS] = $channelStatus;

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????LiveChannel??????
     *
     * @param string $bucket bucket??????
     * @param string channelName $channelName
     * @param array  $options
     * @return GetLiveChannelInfo
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getLiveChannelInfo($bucket, $channelName, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = $channelName;
        $options[OssConst::OSS_SUB_RESOURCE] = 'live';

        $response = $this->auth($options);
        $result = new GetLiveChannelInfoResult($response);
        return $result->getData();
    }

    /**
     * ??????LiveChannel????????????
     *
     * @param string $bucket bucket??????
     * @param string channelName $channelName
     * @param array  $options
     * @return GetLiveChannelStatus
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getLiveChannelStatus($bucket, $channelName, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = $channelName;
        $options[OssConst::OSS_SUB_RESOURCE] = 'live';
        $options[OssConst::OSS_COMP] = 'stat';

        $response = $this->auth($options);
        $result = new GetLiveChannelStatusResult($response);
        return $result->getData();
    }

    /**
     *??????LiveChannel????????????
     *
     * @param string $bucket bucket??????
     * @param string channelName $channelName
     * @param array  $options
     * @return GetLiveChannelHistory
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getLiveChannelHistory($bucket, $channelName, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = $channelName;
        $options[OssConst::OSS_SUB_RESOURCE] = 'live';
        $options[OssConst::OSS_COMP] = 'history';

        $response = $this->auth($options);
        $result = new GetLiveChannelHistoryResult($response);
        return $result->getData();
    }

    /**
     *????????????Bucket??????live channel??????
     *
     * @param string $bucket bucket??????
     * @param array  $options
     * @return LiveChannelListInfo
     * @throws OssException
     * @throws InvalidUrl
     */
    public function listBucketLiveChannels($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'live';
        $options[OssConst::OSS_QUERY_STRING] = array(
            'prefix'   => isset($options['prefix']) ? $options['prefix'] : '',
            'marker'   => isset($options['marker']) ? $options['marker'] : '',
            'max-keys' => isset($options['max-keys']) ? $options['max-keys'] : '',
        );
        $response = $this->auth($options);
        $result = new ListLiveChannelResult($response);
        $list = $result->getData();
        $list->setBucketName($bucket);

        return $list;
    }

    /**
     * ?????????LiveChannel??????????????????
     *
     * @param string $bucket bucket??????
     * @param string channelName $channelName
     * @param string $playlistName ?????????????????????????????????????????????????????????.m3u8?????????
     * @param array  $setTime startTime???EndTime???unix?????????????????????,????????????????????????
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function postVodPlaylist($bucket, $channelName, $playlistName, $setTime)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_OBJECT] = $channelName . '/' . $playlistName;
        $options[OssConst::OSS_SUB_RESOURCE] = 'vod';
        $options[OssConst::OSS_LIVE_CHANNEL_END_TIME] = $setTime['EndTime'];
        $options[OssConst::OSS_LIVE_CHANNEL_START_TIME] = $setTime['StartTime'];

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ????????????Bucket???LiveChannel
     *
     * @param string $bucket bucket??????
     * @param string channelName $channelName
     * @param array  $options
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function deleteBucketLiveChannel($bucket, $channelName, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = $channelName;
        $options[OssConst::OSS_SUB_RESOURCE] = 'live';

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????????????????????????????
     *
     * @param string $bucket bucket??????
     * @param string channelName $channelName
     * @param int timeout ?????????????????????????????????
     * @param array  $options
     * @return ????????????
     * @throws OssException
     * @throws InvalidUrl
     */
    public function signRtmpUrl($bucket, $channelName, $timeout = 60, $options = NULL)
    {
        $this->preCheckCommon($bucket, $channelName, $options, false);
        $expires = time() + $timeout;
        $proto = 'rtmp://';
        $hostname = $this->generateHostname($bucket);
        $cano_params = '';
        $query_items = array();
        $params = isset($options['params']) ? $options['params'] : array();
        uksort($params, 'strnatcasecmp');
        foreach ($params as $key => $value) {
            $cano_params = $cano_params . $key . ':' . $value . "\n";
            $query_items[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $resource = '/' . $bucket . '/' . $channelName;

        $string_to_sign = $expires . "\n" . $cano_params . $resource;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $this->accessKeySecret, true));

        $query_items[] = 'OSSAccessKeyId=' . rawurlencode($this->accessKeyId);
        $query_items[] = 'Expires=' . rawurlencode($expires);
        $query_items[] = 'Signature=' . rawurlencode($signature);

        return $proto . $hostname . '/live/' . $channelName . '?' . implode('&', $query_items);
    }


    /**
     * Generates the signed pushing streaming url
     *
     * @param string $bucket bucket name
     * @param string $channelName channel name
     * @param int    $expiration expiration time of the Url, unix epoch, since 1970.1.1 00.00.00 UTC
     * @param array  $options
     * @return The signed pushing streaming url
     * @throws OssException
     */
    public function generatePresignedRtmpUrl($bucket, $channelName, $expiration, $options = NULL)
    {
        $this->precheckCommon($bucket, $channelName, $options, false);
        $proto = 'rtmp://';
        $hostname = $this->generateHostname($bucket);
        $cano_params = '';
        $query_items = array();
        $params = isset($options['params']) ? $options['params'] : array();
        uksort($params, 'strnatcasecmp');
        foreach ($params as $key => $value) {
            $cano_params = $cano_params . $key . ':' . $value . "\n";
            $query_items[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $resource = '/' . $bucket . '/' . $channelName;

        $string_to_sign = $expiration . "\n" . $cano_params . $resource;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $this->accessKeySecret, true));

        $query_items[] = 'OSSAccessKeyId=' . rawurlencode($this->accessKeyId);
        $query_items[] = 'Expires=' . rawurlencode($expiration);
        $query_items[] = 'Signature=' . rawurlencode($signature);

        return $proto . $hostname . '/live/' . $channelName . '?' . implode('&', $query_items);
    }


    /**
     * ????????????????????????, ???????????????????????????????????????preflight?????????OPTIONS?????????????????????????????????
     * HTTP?????????header????????????OSS??????????????????????????????????????? OSS????????????putBucketCors??????
     * ?????????Bucket???CORS???????????????CORS???????????????OSS??????????????????preflight???????????????????????????
     * ????????????????????????????????????
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @param string $origin ???????????????
     * @param string $request_method ?????????????????????????????????HTTP??????
     * @param string $request_headers ????????????????????????????????????????????????????????????headers
     * @param array  $options
     * @return array
     * @throws InvalidUrl
     * @throws OssException
     * @link http://help.aliyun.com/document_detail/oss/api-reference/cors/OptionObject.html
     */
    public function optionsObject($bucket, $object, $origin, $request_method, $request_headers, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_OPTIONS;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_HEADERS] = array(
            OssConst::OSS_OPTIONS_ORIGIN          => $origin,
            OssConst::OSS_OPTIONS_REQUEST_HEADERS => $request_headers,
            OssConst::OSS_OPTIONS_REQUEST_METHOD  => $request_method
        );
        $response = $this->auth($options);
        $result = new HeaderResult($response);
        return $result->getData();
    }

    /**
     * ??????Bucket???Lifecycle??????
     *
     * @param string          $bucket bucket??????
     * @param LifecycleConfig $lifecycleConfig Lifecycle?????????
     * @param array           $options
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function putBucketLifecycle($bucket, $lifecycleConfig, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'lifecycle';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $options[OssConst::OSS_CONTENT] = $lifecycleConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????Bucket???Lifecycle????????????
     *
     * @param string $bucket bucket??????
     * @param array  $options
     * @return LifecycleConfig
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getBucketLifecycle($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'lifecycle';
        $response = $this->auth($options);
        $result = new GetLifecycleResult($response);
        return $result->getData();
    }

    /**
     * ????????????Bucket?????????????????????
     *
     * @param string $bucket bucket??????
     * @param array  $options
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function deleteBucketLifecycle($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'lifecycle';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ????????????bucket???referer??????????????????????????????referer???????????????????????????
     * Bucket Referer??????????????????OSS?????????
     *
     * @param string        $bucket bucket??????
     * @param RefererConfig $refererConfig
     * @param array         $options
     * @return ResponseCore
     * @throws null
     */
    public function putBucketReferer($bucket, $refererConfig, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'referer';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $options[OssConst::OSS_CONTENT] = $refererConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????Bucket???Referer????????????
     * Bucket Referer??????????????????OSS?????????
     *
     * @param string $bucket bucket??????
     * @param array  $options
     * @return RefererConfig
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getBucketReferer($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'referer';
        $response = $this->auth($options);
        $result = new GetRefererResult($response);
        return $result->getData();
    }

    /**
     * ??????bucket????????????????????????GB
     * ???bucket??????????????????????????????????????????????????????
     *
     * @param string $bucket bucket??????
     * @param int    $storageCapacity
     * @param array  $options
     * @return ResponseCore
     * @throws null
     */
    public function putBucketStorageCapacity($bucket, $storageCapacity, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'qos';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $storageCapacityConfig = new StorageCapacityConfig($storageCapacity);
        $options[OssConst::OSS_CONTENT] = $storageCapacityConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????bucket????????????????????????GB
     *
     * @param string $bucket bucket??????
     * @param array  $options
     * @return int
     * @throws OssException
     * @throws InvalidUrl
     */
    public function getBucketStorageCapacity($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'qos';
        $response = $this->auth($options);
        $result = new GetStorageCapacityResult($response);
        return $result->getData();
    }


    /**
     * Get the information of the bucket
     *
     * @param string $bucket bucket name
     * @param array  $options
     * @return BucketInfo
     * @throws OssException
     */
    public function getBucketInfo($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'bucketInfo';
        $response = $this->auth($options);
        $result = new GetBucketInfoResult($response);
        return $result->getData();
    }

    /**
     * Get the stat of the bucket
     *
     * @param string $bucket bucket name
     * @param array  $options
     * @return BucketStat
     * @throws OssException
     */
    public function getBucketStat($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'stat';
        $response = $this->auth($options);
        $result = new GetBucketStatResult($response);
        return $result->getData();
    }

    /**
     * Sets the bucket's policy
     *
     * @param string $bucket bucket name
     * @param string $policy policy json format content
     * @param array  $options
     * @return null
     * @throws OssException
     */
    public function putBucketPolicy($bucket, $policy, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'policy';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/json';
        $options[OssConst::OSS_CONTENT] = $policy;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets bucket's policy
     *
     * @param string $bucket bucket name
     * @param array  $options
     * @return string policy json content
     * @throws OssException
     */
    public function getBucketPolicy($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'policy';
        $response = $this->auth($options);
        $result = new BodyResult($response);
        return $result->getData();
    }

    /**
     * Deletes the bucket's policy
     *
     * @param string $bucket bucket name
     * @param array  $options
     * @return null
     * @throws OssException
     */
    public function deleteBucketPolicy($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'policy';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Sets the bucket's encryption
     *
     * @param string                     $bucket bucket name
     * @param ServerSideEncryptionConfig $sseConfig
     * @param array                      $options
     * @return null
     * @throws OssException
     */
    public function putBucketEncryption($bucket, $sseConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'encryption';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $options[OssConst::OSS_CONTENT] = $sseConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets bucket's encryption
     *
     * @param string $bucket bucket name
     * @param array  $options
     * @return ServerSideEncryptionConfig
     * @throws OssException
     */
    public function getBucketEncryption($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'encryption';
        $response = $this->auth($options);
        $result = new GetBucketEncryptionResult($response);
        return $result->getData();
    }

    /**
     * Deletes the bucket's encryption
     *
     * @param string $bucket bucket name
     * @param array  $options
     * @return null
     * @throws OssException
     */
    public function deleteBucketEncryption($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'encryption';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Set the request playment of the bucket, Can be BucketOwner and Requester
     *
     * @param string $bucket bucket name
     * @param string $payer
     * @param array  $options
     * @return ResponseCore
     * @throws null
     */
    public function putBucketRequestPayment($bucket, $payer, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'requestPayment';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $config = new RequestPaymentConfig($payer);
        $options[OssConst::OSS_CONTENT] = $config->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Get the request playment of the bucket
     *
     * @param string $bucket bucket name
     * @param array  $options
     * @return string
     * @throws OssException
     */
    public function getBucketRequestPayment($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'requestPayment';
        $response = $this->auth($options);
        $result = new GetBucketRequestPaymentResult($response);
        return $result->getData();
    }

    /**
     * Set the versioning of the bucket, Can be BucketOwner and Requester
     *
     * @param string $bucket bucket name
     * @param string $status
     * @param array  $options
     * @return ResponseCore
     * @throws null
     */
    public function putBucketVersioning($bucket, $status, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'versioning';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $config = new VersioningConfig($status);
        $options[OssConst::OSS_CONTENT] = $config->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Get the versioning of the bucket
     *
     * @param string $bucket bucket name
     * @param array  $options
     * @return string
     * @throws OssException
     */
    public function getBucketVersioning($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'versioning';
        $response = $this->auth($options);
        $result = new GetBucketVersioningResult($response);
        return $result->getData();
    }

    /**
     * Initialize a bucket's worm
     *
     * @param string $bucket bucket name
     * @param int    $day
     * @param array  $options
     * @return string returns uploadid
     * @throws OssException
     */
    public function initiateBucketWorm($bucket, $day, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'worm';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $config = new InitiateWormConfig($day);
        $options[OssConst::OSS_CONTENT] = $config->serializeToXml();
        $response = $this->auth($options);
        $result = new InitiateBucketWormResult($response);
        return $result->getData();
    }

    /**
     * Aborts the bucket's worm
     *
     * @param string $bucket bucket name
     * @param array  $options
     * @return null
     * @throws OssException
     */
    public function abortBucketWorm($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'worm';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Complete a bucket's worm
     *
     * @param string $bucket bucket name
     * @param string $wormId
     * @param array  $options
     * @return string returns uploadid
     * @throws OssException
     */
    public function completeBucketWorm($bucket, $wormId, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_WORM_ID] = $wormId;
        $options[OssConst::OSS_CONTENT] = '';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Extend a bucket's worm
     *
     * @param string $bucket bucket name
     * @param string $wormId
     * @param int    $day
     * @param array  $options
     * @return string returns uploadid
     * @throws OssException
     */
    public function extendBucketWorm($bucket, $wormId, $day, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_WORM_ID] = $wormId;
        $options[OssConst::OSS_SUB_RESOURCE] = 'wormExtend';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $config = new ExtendWormConfig($day);
        $options[OssConst::OSS_CONTENT] = $config->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Get a bucket's worm
     *
     * @param string $bucket bucket name
     * @param array  $options
     * @return string
     * @throws OssException
     */
    public function getBucketWorm($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'worm';
        $response = $this->auth($options);
        $result = new GetBucketWormResult($response);
        return $result->getData();
    }

    /**
     * ??????bucket??????object??????
     *
     * @param string $bucket
     * @param array  $options
     * ??????options??????????????????
     * $options = array(
     *      'max-keys'  => max-keys????????????????????????object??????????????????????????????????????????100???max-keys??????????????????1000???
     *      'prefix'    => ???????????????object key?????????prefix???????????????????????????prefix?????????????????????key???????????????prefix???
     *      'delimiter' => ??????????????????Object?????????????????????????????????????????????????????????????????????????????????delimiter???????????????object??????????????????
     *      'marker'    => ?????????????????????marker????????????????????????????????????????????????
     *)
     * ?????? prefix???marker????????????????????????????????????????????????????????????256?????????
     * @return ObjectListInfo
     * @throws OssException
     * @throws InvalidUrl
     */
    public function listObjects($bucket, $options = NULL)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $query = isset($options[OssConst::OSS_QUERY_STRING]) ? $options[OssConst::OSS_QUERY_STRING] : [];
        $options[OssConst::OSS_QUERY_STRING] = array_merge(
            $query,
            [
                OssConst::OSS_ENCODING_TYPE => OssConst::OSS_ENCODING_TYPE_URL,
                OssConst::OSS_DELIMITER     => isset($options[OssConst::OSS_DELIMITER]) ? $options[OssConst::OSS_DELIMITER] : '/',
                OssConst::OSS_PREFIX        => isset($options[OssConst::OSS_PREFIX]) ? $options[OssConst::OSS_PREFIX] : '',
                OssConst::OSS_MAX_KEYS      => isset($options[OssConst::OSS_MAX_KEYS]) ? $options[OssConst::OSS_MAX_KEYS] : OssConst::OSS_MAX_KEYS_VALUE,
                OssConst::OSS_MARKER        => isset($options[OssConst::OSS_MARKER]) ? $options[OssConst::OSS_MARKER] : ''
            ]
        );
        $response = $this->auth($options);
        $result = new ListObjectsResult($response);
        return $result->getData();
    }


    /**
     * Lists the bucket's object with version information (in ObjectListInfo)
     *
     * @param string $bucket
     * @param array $options are defined below:
     * $options = array(
     *      'max-keys'   => specifies max object count to return. By default is 100 and max value could be 1000.
     *      'prefix'     => specifies the key prefix the returned objects must have. Note that the returned keys still contain the prefix.
     *      'delimiter'  => The delimiter of object name for grouping object. When it's specified, listObjectVersions will differeniate the object and folder. And it will return subfolder's objects.
     *      'key-marker' => The key of returned object must be greater than the 'key-marker'.
     *      'version-id-marker' => The version id of returned object must be greater than the 'version-id-marker'.
     *)
     * Prefix and marker are for filtering and paging. Their length must be less than 256 bytes
     * @throws OssException
     * @return ObjectListInfo
     */
    public function listObjectVersions($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'versions';
        $query = isset($options[OssConst::OSS_QUERY_STRING]) ? $options[OssConst::OSS_QUERY_STRING] : array();
        $options[OssConst::OSS_QUERY_STRING] = array_merge(
            $query,
            array(OssConst::OSS_ENCODING_TYPE => OssConst::OSS_ENCODING_TYPE_URL,
                  OssConst::OSS_DELIMITER => isset($options[OssConst::OSS_DELIMITER]) ? $options[OssConst::OSS_DELIMITER] : '/',
                  OssConst::OSS_PREFIX => isset($options[OssConst::OSS_PREFIX]) ? $options[OssConst::OSS_PREFIX] : '',
                  OssConst::OSS_MAX_KEYS => isset($options[OssConst::OSS_MAX_KEYS]) ? $options[OssConst::OSS_MAX_KEYS] : OssConst::OSS_MAX_KEYS_VALUE,
                  OssConst::OSS_KEY_MARKER => isset($options[OssConst::OSS_KEY_MARKER]) ? $options[OssConst::OSS_KEY_MARKER] : '',
                  OssConst::OSS_VERSION_ID_MARKER => isset($options[OssConst::OSS_VERSION_ID_MARKER]) ? $options[OssConst::OSS_VERSION_ID_MARKER] : '')
        );

        $response = $this->auth($options);
        $result = new ListObjectVersionsResult($response);
        return $result->getData();
    }
    /**
     * ?????????????????? (???????????????object???????????????'/', ?????????????????????object???????????????'/'???????????????????????????????????????'//')
     *
     * ?????????????????????
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @param array  $options
     * @return null
     */
    public function createObjectDir($bucket, $object, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = $object . '/';
        $options[OssConst::OSS_CONTENT_LENGTH] = array(OssConst::OSS_CONTENT_LENGTH => 0);
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ????????????????????????
     *
     * @param string $bucket bucket??????
     * @param string $object objcet??????
     * @param string $content ???????????????
     * @param array  $options
     * @return null
     */
    public function putObject($bucket, $object, $content, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);

        $options[OssConst::OSS_CONTENT] = $content;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = $object;

        if (!isset($options[OssConst::OSS_LENGTH])) {
            $options[OssConst::OSS_CONTENT_LENGTH] = strlen($options[OssConst::OSS_CONTENT]);
        } else {
            $options[OssConst::OSS_CONTENT_LENGTH] = $options[OssConst::OSS_LENGTH];
        }

        $is_check_md5 = $this->isCheckMD5($options);
        if ($is_check_md5) {
            $content_md5 = base64_encode(md5($content, true));
            $options[OssConst::OSS_CONTENT_MD5] = $content_md5;
        }

        if (!isset($options[OssConst::OSS_CONTENT_TYPE])) {
            $options[OssConst::OSS_CONTENT_TYPE] = $this->getMimeType($object);
        }
        $response = $this->auth($options);

        if (isset($options[OssConst::OSS_CALLBACK]) && !empty($options[OssConst::OSS_CALLBACK])) {
            $result = new CallbackResult($response);
        } else {
            $result = new PutSetDeleteResult($response);
        }

        return $result->getData();
    }

    /**
     * ??????symlink
     * @param string $bucket bucket??????
     * @param string $symlink symlink??????
     * @param string $targetObject ??????object??????
     * @param array  $options
     * @return null
     */
    public function putSymlink($bucket, $symlink, $targetObject, $options = NULL)
    {
        $this->preCheckCommon($bucket, $symlink, $options);

        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = $symlink;
        $options[OssConst::OSS_SUB_RESOURCE] = OssConst::OSS_SYMLINK;
        $options[OssConst::OSS_HEADERS][OssConst::OSS_SYMLINK_TARGET] = rawurlencode($targetObject);

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????symlink
     * @param string $bucket bucket??????
     * @param string $symlink symlink??????
     * @param array $options
     * @return null
     */
    public function getSymlink($bucket, $symlink, $options = NULL)
    {
        $this->preCheckCommon($bucket, $symlink, $options);

        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = $symlink;
        $options[OssConst::OSS_SUB_RESOURCE] = OssConst::OSS_SYMLINK;

        $response = $this->auth($options);
        $result = new SymlinkResult($response);
        return $result->getData();
    }

    /**
     * ??????????????????
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @param string $file ??????????????????
     * @param array  $options
     * @return null
     * @throws InvalidUrl
     * @throws OssException
     */
    public function uploadFile($bucket, $object, $file, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        OssUtil::throwOssExceptionWithMessageIfEmpty($file, "file path is invalid");
        $file = OssUtil::encodePath($file);
        if (!file_exists($file)) {
            throw new OssException($file . " file does not exist");
        }
        $options[OssConst::OSS_FILE_UPLOAD] = $file;
        $file_size = filesize($options[OssConst::OSS_FILE_UPLOAD]);
        $is_check_md5 = $this->isCheckMD5($options);
        if ($is_check_md5) {
            $content_md5 = base64_encode(md5_file($options[OssConst::OSS_FILE_UPLOAD], true));
            $options[OssConst::OSS_CONTENT_MD5] = $content_md5;
        }
        if (!isset($options[OssConst::OSS_CONTENT_TYPE])) {
            $options[OssConst::OSS_CONTENT_TYPE] = $this->getMimeType($object, $file);
        }
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_CONTENT_LENGTH] = $file_size;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Uploads object from file handle
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param resource $handle file handle
     * @param array $options
     * @return null
     * @throws OssException
     */
    public function uploadStream($bucket, $object, $handle, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        if (!is_resource($handle)) {
            throw new OssException("The handle must be an opened stream");
        }
        $options[OssConst::OSS_FILE_UPLOAD] = $handle;
        if ($this->isCheckMD5($options)) {
            rewind($handle);
            $ctx = hash_init('md5');
            hash_update_stream($ctx, $handle);
            $content_md5 = base64_encode(hash_final($ctx, true));
            rewind($handle);
            $options[OssConst::OSS_CONTENT_MD5] = $content_md5;
        }
        if (!isset($options[OssConst::OSS_CONTENT_TYPE])) {
            $options[OssConst::OSS_CONTENT_TYPE] = $this->getMimeType($object);
        }
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        if (!isset($options[OssConst::OSS_CONTENT_LENGTH])) {
            $options[OssConst::OSS_CONTENT_LENGTH] = fstat($handle)[OssConst::OSS_SIZE];
        }
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????????????????????????????
     *
     * @param string $bucket bucket??????
     * @param string $object objcet??????
     * @param string $content ???????????????????????????
     * @param array  $options
     * @return int next append position
     * @throws InvalidUrl
     * @throws OssException
     */
    public function appendObject($bucket, $object, $content, $position, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);

        $options[OssConst::OSS_CONTENT] = $content;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_SUB_RESOURCE] = 'append';
        $options[OssConst::OSS_POSITION] = strval($position);

        if (!isset($options[OssConst::OSS_LENGTH])) {
            $options[OssConst::OSS_CONTENT_LENGTH] = strlen($options[OssConst::OSS_CONTENT]);
        } else {
            $options[OssConst::OSS_CONTENT_LENGTH] = $options[OssConst::OSS_LENGTH];
        }

        $is_check_md5 = $this->isCheckMD5($options);
        if ($is_check_md5) {
            $content_md5 = base64_encode(md5($content, true));
            $options[OssConst::OSS_CONTENT_MD5] = $content_md5;
        }

        if (!isset($options[OssConst::OSS_CONTENT_TYPE])) {
            $options[OssConst::OSS_CONTENT_TYPE] = $this->getMimeType($object);
        }
        $response = $this->auth($options);
        $result = new AppendResult($response);
        return $result->getData();
    }

    /**
     * ????????????????????????
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @param string $file ?????????????????????????????????
     * @param array  $options
     * @return int next append position
     * @throws InvalidUrl
     * @throws OssException
     */
    public function appendFile($bucket, $object, $file, $position, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);

        OssUtil::throwOssExceptionWithMessageIfEmpty($file, "file path is invalid");
        $file = OssUtil::encodePath($file);
        if (!file_exists($file)) {
            throw new OssException($file . " file does not exist");
        }
        $options[OssConst::OSS_FILE_UPLOAD] = $file;
        $file_size = filesize($options[OssConst::OSS_FILE_UPLOAD]);
        $is_check_md5 = $this->isCheckMD5($options);
        if ($is_check_md5) {
            $content_md5 = base64_encode(md5_file($options[OssConst::OSS_FILE_UPLOAD], true));
            $options[OssConst::OSS_CONTENT_MD5] = $content_md5;
        }
        if (!isset($options[OssConst::OSS_CONTENT_TYPE])) {
            $options[OssConst::OSS_CONTENT_TYPE] = $this->getMimeType($object, $file);
        }

        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_CONTENT_LENGTH] = $file_size;
        $options[OssConst::OSS_SUB_RESOURCE] = 'append';
        $options[OssConst::OSS_POSITION] = strval($position);

        $response = $this->auth($options);
        $result = new AppendResult($response);
        return $result->getData();
    }

    /**
     * ???????????????OSS??????????????????object???????????????object
     *
     * @param string $fromBucket ???bucket??????
     * @param string $fromObject ???object??????
     * @param string $toBucket ??????bucket??????
     * @param string $toObject ??????object??????
     * @param array  $options
     * @return null
     * @throws InvalidUrl
     * @throws OssException
     */
    public function copyObject($fromBucket, $fromObject, $toBucket, $toObject, $options = NULL)
    {
        $this->preCheckCommon($fromBucket, $fromObject, $options);
        $this->preCheckCommon($toBucket, $toObject, $options);
        $options[OssConst::OSS_BUCKET] = $toBucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_OBJECT] = $toObject;
        $param = '/' . $fromBucket . '/' . rawurlencode($fromObject);
        if (isset($options[OssConst::OSS_VERSION_ID])) {
            $param = $param . '?versionId='.$options[OssConst::OSS_VERSION_ID];
            unset($options[OssConst::OSS_VERSION_ID]);
        }
        if (isset($options[OssConst::OSS_HEADERS])) {
            $options[OssConst::OSS_HEADERS][OssConst::OSS_OBJECT_COPY_SOURCE] = '/' . $fromBucket . '/' . $fromObject;
        } else {
            $options[OssConst::OSS_HEADERS] = array(OssConst::OSS_OBJECT_COPY_SOURCE => '/' . $fromBucket . '/' . $fromObject);
        }
        $response = $this->auth($options);
        $result = new CopyObjectResult($response);
        return $result->getData();
    }

    /**
     * ??????Object???Meta??????
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @param string $options ????????????SDK??????
     * @return array
     */
    public function getObjectMeta($bucket, $object, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_HEAD;
        $options[OssConst::OSS_OBJECT] = $object;
        $response = $this->auth($options);
        $result = new HeaderResult($response);
        return $result->getData();
    }

    /**
     * Gets the simplified metadata of a object.
     * Simplified metadata includes ETag, Size, LastModified.
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $options Checks out the SDK document for the detail
     * @return array
     */
    public function getSimplifiedObjectMeta($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_HEAD;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_SUB_RESOURCE] = 'objectMeta';
        $response = $this->auth($options);
        $result = new HeaderResult($response);
        return $result->getData();
    }

    /**
     * ????????????Object
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @param array  $options
     * @return null
     */
    public function deleteObject($bucket, $object, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_OBJECT] = $object;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ???????????????Bucket????????????Object
     *
     * @param string $bucket bucket??????
     * @param array  $objects object??????
     * @param array  $options
     * @return ResponseCore
     * @throws null
     */
    public function deleteObjects($bucket, $objects, $options = null)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        if (!is_array($objects) || !$objects) {
            throw new OssException('objects must be array');
        }
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'delete';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $quiet = 'false';
        if (isset($options['quiet'])) {
            if (is_bool($options['quiet'])) { //Boolean
                $quiet = $options['quiet'] ? 'true' : 'false';
            } elseif (is_string($options['quiet'])) { // string
                $quiet = ($options['quiet'] === 'true') ? 'true' : 'false';
            }
        }
        $xmlBody = OssUtil::createDeleteObjectsXmlBody($objects, $quiet);
        $options[OssConst::OSS_CONTENT] = $xmlBody;
        $response = $this->auth($options);
        $result = new DeleteObjectsResult($response);
        return $result->getData();
    }

    /**
     * Deletes multiple objects with version id in a bucket
     *
     * @param string $bucket bucket name
     * @param array $objects DeleteObjectInfo list
     * @param array $options
     * @return ResponseCore
     * @throws null
     */
    public function deleteObjectVersions($bucket, $objects, $options = null)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        if (!is_array($objects) || !$objects) {
            throw new OssException('objects must be array');
        }
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'delete';
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        $quiet = 'false';
        if (isset($options['quiet'])) {
            if (is_bool($options['quiet'])) { //Boolean
                $quiet = $options['quiet'] ? 'true' : 'false';
            } elseif (is_string($options['quiet'])) { // string
                $quiet = ($options['quiet'] === 'true') ? 'true' : 'false';
            }
        }
        $xmlBody = OssUtil::createDeleteObjectVersionsXmlBody($objects, $quiet);
        $options[OssConst::OSS_CONTENT] = $xmlBody;
        $response = $this->auth($options);
        $result = new DeleteObjectVersionsResult($response);
        return $result->getData();
    }

    /**
     * ??????Object??????
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @param array  $options ????????????????????????ALIOSS::OSS_FILE_DOWNLOAD???ALIOSS::OSS_RANGE???????????????????????????????????????????????????????????????????????????????????????
     * @return string
     */
    public function getObject($bucket, $object, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_OBJECT] = $object;
        if (isset($options[OssConst::OSS_LAST_MODIFIED])) {
            $options[OssConst::OSS_HEADERS][OssConst::OSS_IF_MODIFIED_SINCE] = $options[OssConst::OSS_LAST_MODIFIED];
            unset($options[OssConst::OSS_LAST_MODIFIED]);
        }
        if (isset($options[OssConst::OSS_ETAG])) {
            $options[OssConst::OSS_HEADERS][OssConst::OSS_IF_NONE_MATCH] = $options[OssConst::OSS_ETAG];
            unset($options[OssConst::OSS_ETAG]);
        }
        if (isset($options[OssConst::OSS_RANGE])) {
            $range = $options[OssConst::OSS_RANGE];
            $options[OssConst::OSS_HEADERS][OssConst::OSS_RANGE] = "bytes=$range";
            unset($options[OssConst::OSS_RANGE]);
        }
        $response = $this->auth($options);
        $result = new BodyResult($response);
        return $result->getData();
    }

    /**
     * ??????Object????????????
     * ????????????Object???Meta???????????????Object??????????????? ????????????????????????ResponseCore??????object????????????
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @param array  $options
     * @return bool
     */
    public function doesObjectExist($bucket, $object, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_HEAD;
        $options[OssConst::OSS_OBJECT] = $object;
        $response = $this->auth($options);
        $result = new ExistResult($response);
        return $result->getData();
    }

    /**
     * ??????Archive?????????Object??????
     * ????????????Restore????????????????????????????????????
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @return null
     * @throws InvalidUrl
     * @throws OssException
     */
    public function restoreObject($bucket, $object, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_SUB_RESOURCE] = OssConst::OSS_RESTORE;
        if (isset($options[OssConst::OSS_RESTORE_CONFIG])) {
            $config = $options[OssConst::OSS_RESTORE_CONFIG];
            $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
            $options[OssConst::OSS_CONTENT] = $config->serializeToXml();
        }
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????????????????????????????????????????part_size?????????????????????????????????partsize
     *
     * @param int $partSize
     * @return int
     */
    private function computePartSize($partSize)
    {
        $partSize = (integer)$partSize;
        if ($partSize <= OssConst::OSS_MIN_PART_SIZE) {
            $partSize = OssConst::OSS_MIN_PART_SIZE;
        } elseif ($partSize > OssConst::OSS_MAX_PART_SIZE) {
            $partSize = OssConst::OSS_MAX_PART_SIZE;
        }
        return $partSize;
    }

    /**
     * ?????????????????????????????????part???????????????part???????????????????????????
     * ??????????????? <upload_part()>?????????
     *
     * @param integer $file_size ????????????
     * @param integer $partSize part??????,??????5M
     * @return array An array ?????? key-value ?????????. Key ??? `seekTo` ??? `length`.
     */
    public function generateMultiUploadParts($file_size, $partSize = 5242880)
    {
        $i = 0;
        $size_count = $file_size;
        $values = array();
        $partSize = $this->computePartSize($partSize);
        while ($size_count > 0) {
            $size_count -= $partSize;
            $values[] = array(
                OssConst::OSS_SEEK_TO => ($partSize * $i),
                OssConst::OSS_LENGTH  => (($size_count > 0) ? $partSize : ($size_count + $partSize)),
            );
            $i++;
        }
        return $values;
    }

    /**
     * ?????????multi-part upload
     *
     * @param string $bucket Bucket??????
     * @param string $object Object??????
     * @param array  $options Key-Value??????
     * @return string ??????uploadid
     * @throws OssException
     * @throws InvalidUrl
     */
    public function initiateMultipartUpload($bucket, $object, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_SUB_RESOURCE] = 'uploads';
        $options[OssConst::OSS_CONTENT] = '';

        if (!isset($options[OssConst::OSS_CONTENT_TYPE])) {
            $options[OssConst::OSS_CONTENT_TYPE] = $this->getMimeType($object);
        }
        if (!isset($options[OssConst::OSS_HEADERS])) {
            $options[OssConst::OSS_HEADERS] = array();
        }
        $response = $this->auth($options);
        $result = new InitiateMultipartUploadResult($response);
        return $result->getData();
    }

    /**
     * ??????????????????????????????
     *
     * @param string $bucket Bucket??????
     * @param string $object Object??????
     * @param string $uploadId
     * @param array  $options Key-Value??????
     * @return string eTag
     * @throws InvalidUrl
     * @throws OssException
     */
    public function uploadPart($bucket, $object, $uploadId, $options = null)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $this->preCheckParam($options, OssConst::OSS_FILE_UPLOAD, __FUNCTION__);
        $this->preCheckParam($options, OssConst::OSS_PART_NUM, __FUNCTION__);

        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_UPLOAD_ID] = $uploadId;

        if (isset($options[OssConst::OSS_LENGTH])) {
            $options[OssConst::OSS_CONTENT_LENGTH] = $options[OssConst::OSS_LENGTH];
        }
        $response = $this->auth($options);
        $result = new UploadPartResult($response);
        return $result->getData();
    }

    /**
     * ????????????????????????part
     *
     * @param string $bucket Bucket??????
     * @param string $object Object??????
     * @param string $uploadId uploadId
     * @param array  $options Key-Value??????
     * @return ListPartsInfo
     * @throws InvalidUrl
     * @throws OssException
     */
    public function listParts($bucket, $object, $uploadId, $options = null)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_UPLOAD_ID] = $uploadId;
        $options[OssConst::OSS_QUERY_STRING] = array();
        foreach (array('max-parts', 'part-number-marker') as $param) {
            if (isset($options[$param])) {
                $options[OssConst::OSS_QUERY_STRING][$param] = $options[$param];
                unset($options[$param]);
            }
        }
        $response = $this->auth($options);
        $result = new ListPartsResult($response);
        return $result->getData();
    }

    /**
     * ???????????????????????????????????????
     *
     * @param string $bucket Bucket??????
     * @param string $object Object??????
     * @param string $uploadId uploadId
     * @param array  $options Key-Value??????
     * @return null
     * @throws InvalidUrl
     * @throws OssException
     */
    public function abortMultipartUpload($bucket, $object, $uploadId, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_DELETE;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_UPLOAD_ID] = $uploadId;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * ??????????????????Part????????????????????????????????????????????????????????????
     *
     * @param string $bucket Bucket??????
     * @param string $object Object??????
     * @param string $uploadId uploadId
     * @param array  $listParts array( array("PartNumber"=> int, "ETag"=>string))
     * @param array  $options Key-Value??????
     * @return null
     * @throws OssException
     * @throws InvalidUrl
     */
    public function completeMultipartUpload($bucket, $object, $uploadId, $listParts, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_POST;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_UPLOAD_ID] = $uploadId;
        $options[OssConst::OSS_CONTENT_TYPE] = 'application/xml';
        if (!is_array($listParts)) {
            throw new OssException("listParts must be array type");
        }
        $options[OssConst::OSS_CONTENT] = OssUtil::createCompleteMultipartUploadXmlBody($listParts);
        $response = $this->auth($options);
//        var_dump($response);
        if (isset($options[OssConst::OSS_CALLBACK]) && !empty($options[OssConst::OSS_CALLBACK])) {
            $result = new CallbackResult($response);
        } else {
            $result = new PutSetDeleteResult($response);
        }

        return $result->getData();
    }

    /**
     * ???????????????????????????Multipart Upload?????????????????????????????????Multipart Upload????????????
     * Complete??????Abort???Multipart Upload??????
     *
     * @param string $bucket bucket
     * @param array  $options ????????????
     * @return ListMultipartUploadInfo
     * @throws OssException
     * @throws InvalidUrl
     */
    public function listMultipartUploads($bucket, $options = null)
    {
        $this->preCheckCommon($bucket, NULL, $options, false);
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_GET;
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = '/';
        $options[OssConst::OSS_SUB_RESOURCE] = 'uploads';

        foreach (array('delimiter', 'key-marker', 'max-uploads', 'prefix', 'upload-id-marker') as $param) {
            if (isset($options[$param])) {
                $options[OssConst::OSS_QUERY_STRING][$param] = $options[$param];
                unset($options[$param]);
            }
        }
        $query = isset($options[OssConst::OSS_QUERY_STRING]) ? $options[OssConst::OSS_QUERY_STRING] : array();
        $options[OssConst::OSS_QUERY_STRING] = array_merge(
            $query,
            array(OssConst::OSS_ENCODING_TYPE => OssConst::OSS_ENCODING_TYPE_URL)
        );

        $response = $this->auth($options);
        $result = new ListMultipartUploadResult($response);
        return $result->getData();
    }

    /**
     * ?????????????????????Object??????????????????????????????Part
     *
     * @param string $fromBucket ???bucket??????
     * @param string $fromObject ???object??????
     * @param string $toBucket ??????bucket??????
     * @param string $toObject ??????object??????
     * @param int    $partNumber ??????????????????id
     * @param string $uploadId ?????????multipart upload?????????uploadid
     * @param array  $options Key-Value??????
     * @return null
     * @throws InvalidUrl
     * @throws OssException
     */
    public function uploadPartCopy($fromBucket, $fromObject, $toBucket, $toObject, $partNumber, $uploadId, $options = NULL)
    {
        $this->preCheckCommon($fromBucket, $fromObject, $options);
        $this->preCheckCommon($toBucket, $toObject, $options);

        //??????????????????$options['isFullCopy']????????????????????????copy???????????????
        $start_range = "0";
        if (isset($options['start'])) {
            $start_range = $options['start'];
        }
        $end_range = "";
        if (isset($options['end'])) {
            $end_range = $options['end'];
        }
        $options[OssConst::OSS_METHOD] = OssConst::OSS_HTTP_PUT;
        $options[OssConst::OSS_BUCKET] = $toBucket;
        $options[OssConst::OSS_OBJECT] = $toObject;
        $options[OssConst::OSS_PART_NUM] = $partNumber;
        $options[OssConst::OSS_UPLOAD_ID] = $uploadId;

        if (!isset($options[OssConst::OSS_HEADERS])) {
            $options[OssConst::OSS_HEADERS] = array();
        }

        $options[OssConst::OSS_HEADERS][OssConst::OSS_OBJECT_COPY_SOURCE] = '/' . $fromBucket . '/' . $fromObject;
        $options[OssConst::OSS_HEADERS][OssConst::OSS_OBJECT_COPY_SOURCE_RANGE] = "bytes=" . $start_range . "-" . $end_range;
        $response = $this->auth($options);
        $result = new UploadPartResult($response);
        return $result->getData();
    }

    /**
     * multipart??????????????????????????????????????????multipart??????????????????????????????
     *
     * @param string $bucket bucket??????
     * @param string $object object??????
     * @param string $file ????????????????????????????????????
     * @param array  $options Key-Value??????
     * @return null
     * @throws InvalidUrl
     * @throws OssException
     */
    public function multiUploadFile($bucket, $object, $file, $options = null)
    {
        $this->preCheckCommon($bucket, $object, $options);
        if (isset($options[OssConst::OSS_LENGTH])) {
            $options[OssConst::OSS_CONTENT_LENGTH] = $options[OssConst::OSS_LENGTH];
            unset($options[OssConst::OSS_LENGTH]);
        }
        if (empty($file)) {
            throw new OssException("parameter invalid, file is empty");
        }
        $uploadFile = OssUtil::encodePath($file);
        if (!isset($options[OssConst::OSS_CONTENT_TYPE])) {
            $options[OssConst::OSS_CONTENT_TYPE] = $this->getMimeType($object, $uploadFile);
        }

        $upload_position = isset($options[OssConst::OSS_SEEK_TO]) ? (integer)$options[OssConst::OSS_SEEK_TO] : 0;

        if (isset($options[OssConst::OSS_CONTENT_LENGTH])) {
            $upload_file_size = (integer)$options[OssConst::OSS_CONTENT_LENGTH];
        } else {
            $upload_file_size = filesize($uploadFile);
            if ($upload_file_size !== false) {
                $upload_file_size -= $upload_position;
            }
        }

        if ($upload_position === false || !isset($upload_file_size) || $upload_file_size === false || $upload_file_size < 0) {
            throw new OssException('The size of `fileUpload` cannot be determined in ' . __FUNCTION__ . '().');
        }
        // ??????partSize
        if (isset($options[OssConst::OSS_PART_SIZE])) {
            $options[OssConst::OSS_PART_SIZE] = $this->computePartSize($options[OssConst::OSS_PART_SIZE]);
        } else {
            $options[OssConst::OSS_PART_SIZE] = OssConst::OSS_MID_PART_SIZE;
        }

        $is_check_md5 = $this->isCheckMD5($options);
        // ???????????????????????????partSize,?????????????????????????????????
        if ($upload_file_size < $options[OssConst::OSS_PART_SIZE] && !isset($options[OssConst::OSS_UPLOAD_ID])) {
            return $this->uploadFile($bucket, $object, $uploadFile, $options);
        }

        // ?????????multipart
        if (isset($options[OssConst::OSS_UPLOAD_ID])) {
            $uploadId = $options[OssConst::OSS_UPLOAD_ID];
        } else {
            // ?????????
            $uploadId = $this->initiateMultipartUpload($bucket, $object, $options);
        }
        // ???????????????
        $pieces = $this->generateMultiuploadParts($upload_file_size, (integer)$options[OssConst::OSS_PART_SIZE]);
        $response_upload_part = array();
        foreach ($pieces as $i => $piece) {
            $from_pos = $upload_position + (integer)$piece[OssConst::OSS_SEEK_TO];
            $to_pos = (integer)$piece[OssConst::OSS_LENGTH] + $from_pos - 1;
            $up_options = array(
                OssConst::OSS_FILE_UPLOAD => $uploadFile,
                OssConst::OSS_PART_NUM    => ($i + 1),
                OssConst::OSS_SEEK_TO     => $from_pos,
                OssConst::OSS_LENGTH      => $to_pos - $from_pos + 1,
                OssConst::OSS_CHECK_MD5   => $is_check_md5,
            );
            if ($is_check_md5) {
                $content_md5 = OssUtil::getMd5SumForFile($uploadFile, $from_pos, $to_pos);
                $up_options[OssConst::OSS_CONTENT_MD5] = $content_md5;
            }
            $response_upload_part[] = $this->uploadPart($bucket, $object, $uploadId, $up_options);
        }

        $uploadParts = array();
        foreach ($response_upload_part as $i => $etag) {
            $uploadParts[] = array(
                'PartNumber' => ($i + 1),
                'ETag'       => $etag,
            );
        }
        return $this->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts);
    }

    /**
     * ???????????????????????????????????????????????????bucket?????????prefix???object???
     *
     * @param string $bucket bucket??????
     * @param string $prefix ??????????????????object???key????????????????????????bucket?????????????????????????????????'/'?????????????????????'/'
     * @param string $localDirectory ???????????????????????????
     * @param string $exclude ?????????????????????
     * @param bool   $recursive ?????????????????????localDirectory?????????????????????
     * @param bool   $checkMd5
     * @return array ?????????????????? array("succeededList" => array("object"), "failedList" => array("object"=>"errorMessage"))
     * @throws InvalidUrl
     * @throws OssException
     */
    public function uploadDir($bucket, $prefix, $localDirectory, $exclude = '.|..|.svn|.git', $recursive = false, $checkMd5 = true)
    {
        $retArray = array("succeededList" => array(), "failedList" => array());
        if (empty($bucket)) throw new OssException("parameter error, bucket is empty");
        if (!is_string($prefix)) throw new OssException("parameter error, prefix is not string");
        if (empty($localDirectory)) throw new OssException("parameter error, localDirectory is empty");
        $directory = $localDirectory;
        $directory = OssUtil::encodePath($directory);
        //??????????????????
        if (!is_dir($directory)) {
            throw new OssException('parameter error: ' . $directory . ' is not a directory, please check it');
        }
        //read directory
        $file_list_array = OssUtil::readDir($directory, $exclude, $recursive);
        if (!$file_list_array) {
            throw new OssException($directory . ' is empty...');
        }
        foreach ($file_list_array as $k => $item) {
            if (is_dir($item['path'])) {
                continue;
            }
            $options = array(
                OssConst::OSS_PART_SIZE => OssConst::OSS_MIN_PART_SIZE,
                OssConst::OSS_CHECK_MD5 => $checkMd5,
            );
            $realObject = (!empty($prefix) ? $prefix . '/' : '') . $item['file'];

            try {
                $this->multiUploadFile($bucket, $realObject, $item['path'], $options);
                $retArray["succeededList"][] = $realObject;
            } catch (OssException $e) {
                $retArray["failedList"][$realObject] = $e->getMessage();
            }
        }
        return $retArray;
    }

    /**
     * ????????????get???put??????, ????????????????????????????????????????????????
     * ????????????url
     *
     * @param string $bucket
     * @param string $object
     * @param int    $timeout
     * @param string $method
     * @param array  $options Key-Value??????
     * @return string
     * @throws InvalidUrl
     * @throws OssException
     */
    public function signUrl($bucket, $object, $timeout = 60, $method = OssConst::OSS_HTTP_GET, $options = NULL)
    {
        $this->preCheckCommon($bucket, $object, $options);
        //method
        if (OssConst::OSS_HTTP_GET !== $method && OssConst::OSS_HTTP_PUT !== $method) {
            throw new OssException("method is invalid");
        }
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_METHOD] = $method;
//        if (!isset($options[OssConst::OSS_CONTENT_TYPE])) {
//            $options[OssConst::OSS_CONTENT_TYPE] = '';
//        }
        $timeout = time() + $timeout;
        $options[OssConst::OSS_PREAUTH] = $timeout;
        $options[OssConst::OSS_DATE] = $timeout;
        $this->setSignStsInUrl(true);
        return $this->auth($options);
    }

    /**
     * Sign URL with specified expiration time in seconds and HTTP method.
     * The signed URL could be used to access the object directly.
     *
     * @param string $bucket
     * @param string $object
     * @param int $expiration expiration time of the Url, unix epoch, since 1970.1.1 00.00.00 UTC
     * @param string $method
     * @param array $options Key-Value array
     * @return string
     * @throws OssException
     */
    public function generatePresignedUrl($bucket, $object, $expiration, $method = OssConst::OSS_HTTP_GET, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        //method
        if (OssConst::OSS_HTTP_GET !== $method && OssConst::OSS_HTTP_PUT !== $method) {
            throw new OssException("method is invalid");
        }
        $options[OssConst::OSS_BUCKET] = $bucket;
        $options[OssConst::OSS_OBJECT] = $object;
        $options[OssConst::OSS_METHOD] = $method;
        if (!isset($options[OssConst::OSS_CONTENT_TYPE])) {
            $options[OssConst::OSS_CONTENT_TYPE] = '';
        }
        $options[OssConst::OSS_PREAUTH] = $expiration;
        $options[OssConst::OSS_DATE] = $expiration;
        $this->setSignStsInUrl(true);
        return $this->auth($options);
    }

    ##############################????????????#######################################

    ##############################??????#########################################

    /**
     * auth
     * ?????????????????????????????????OSS Api?????????????????????
     * @param array $options
     * @return Response
     * @throws InvalidUrl
     * @throws OssException
     * @throws \EasySwoole\HttpClient\Exception\InvalidUrl
     * @author Tioncico
     * Time: 15:43
     */
    public function auth(array $options)
    {
        $config = $this->config;
        OssUtil::validateOptions($options);
        $this->authPreCheckBucket($options);
        $this->authPreCheckObject($options);
        $this->authPreCheckObjectEncoding($options);
        //??????ACL
        $this->authpreCheckAcl($options);

        //?????????
        $signature = new Signature($config);
        $queryStringParams = $signature->generateSignableQueryStringParam($options, $this->enableStsInUrl, $this->securityToken);
        //get????????????
        $signableQueryString = OssUtil::toQueryString($queryStringParams);
        //??????????????????????????????get?????????
        list($conjunction, $signableQueryString) = $this->generateRequestUrl($options, $signableQueryString);
        //??????get???????????????,?????????$conjunction?????????
        //????????????
        $httpClient = new HttpClient();
        $httpClient->setTimeout($this->timeout);;
        $httpClient->setConnectTimeout($this->connectTimeout);;
        //????????????
        $httpClient->setUrl($this->requestUrl);
        //??????http????????????
        $this->setHttpClientMethod($httpClient, $options);
        //??????http????????????
        $this->setHttpClientData($httpClient, $options);
        //????????????header?????????user-agent
        $httpClient->setHeader('User-Agent', $this->generateUserAgent(), false);
        $httpClient->setHeader('Referer', $this->requestUrl);
        //??????????????????
        $this->setHttpClientFileStream($httpClient, $options);
        //????????????
        if ($this->requestProxy) {
            $httpClient->setProxyHttp(...$this->requestProxy);
        }

        //??????headers
        $headers = new RequestHeaders($config);
        // ???????????????????????????hostname?????????????????????????????????????????????bucket??????????????????????????????
        $hostname = $this->generateHostname($options[OssConst::OSS_BUCKET]);
        $headers = $headers->generateHeaders($options, $hostname, $this);
        $this->setHttpClientHeaders($httpClient, $headers);
        //???????????????
        $stringToSign = $signature->getStringToSign($signableQueryString, $options, $headers, $this->hostType);
        //?????????????????????????string???????????????
        $stringToSignOrdered = $signature->stringToSignSorted($stringToSign);
        $signatureStr = $signature->getSign($stringToSignOrdered);
        $authorization = 'OSS ' . $this->config->getAccessKeyId() . ':' . $signatureStr;
        $httpClient->setHeader('Authorization', $authorization, false);

        //???????????????url,???????????????
        if (($url = $this->preAuth($options, $signatureStr, $conjunction)) !== null) {
            return $url;
        }

        //?????????????????????
        $redirects = 0;
        //?????????500?????????
        while ($redirects <= $this->maxRetries) {
            try {
                $response = new Response($httpClient->request($options)->toArray());
                $response->setOssRedirects($redirects);
                $response->setOssStringToSign($stringToSign);
                $response->setOssRequestUrl($this->requestUrl);
                if ($response->getStatusCode() == '500') {
                    //????????????
                    $delay = (integer)(pow(4, $redirects));
                    Coroutine::sleep($delay);
                    $redirects++;
                    continue;
                }
                $this->downFile($response, $options);
                break;
            } catch (\Throwable $throwable) {
                $redirects++;
                if ($redirects >= $this->maxRetries) {
                    throw  new OssException($throwable->getMessage(), $throwable->getCode());
                }
            }
        }
        return $response;
    }


    ##############################??????##########################################

    ###############################????????????#########################################
    /**
     * ??????endpoint?????????
     * ????????????????????????????????????
     * ?????????????????? is_cname ???endpoint?????????????????????????????????ip???cname????????????????????????????????????
     *
     * @return string ????????????????????????
     */
    private function checkEndpoint()
    {
        $retEndpoint = null;
        $config = $this->config;
        $endpoint = $config->getEndpoint();
        $isCName = $config->isCName();
        if (strpos($endpoint, 'http://') === 0) {
            $retEndpoint = substr($endpoint, strlen('http://'));
        } elseif (strpos($endpoint, 'https://') === 0) {
            $retEndpoint = substr($endpoint, strlen('https://'));
            $this->useSSL = true;
        } else {
            $retEndpoint = $endpoint;
        }

        if ($isCName) {
            $this->hostType = OssConst::OSS_HOST_TYPE_CNAME;
        } elseif (OssUtil::isIPFormat($retEndpoint)) {
            $this->hostType = OssConst::OSS_HOST_TYPE_IP;
        } else {
            $this->hostType = OssConst::OSS_HOST_TYPE_NORMAL;
        }
        return $retEndpoint;
    }

    /**
     * Sets the max retry count
     *
     * @param int $maxRetries
     * @return void
     */
    public function setMaxTries($maxRetries = 3)
    {
        $this->maxRetries = $maxRetries;
    }

    /**
     * Gets the max retry count
     *
     * @return int
     */
    public function getMaxRetries()
    {
        return $this->maxRetries;
    }

    /**
     * Enaable/disable STS in the URL. This is to determine the $sts value passed from constructor take effect or not.
     *
     * @param boolean $enable
     */
    public function setSignStsInUrl($enable)
    {
        $this->enableStsInUrl = $enable;
    }

    /**
     * @return bool
     */
    public function isEnableStsInUrl(): bool
    {
        return $this->enableStsInUrl;
    }

    /**
     * @param bool $enableStsInUrl
     */
    public function setEnableStsInUrl(bool $enableStsInUrl): void
    {
        $this->enableStsInUrl = $enableStsInUrl;
    }

    /**
     * @return null
     */
    public function getSecurityToken()
    {
        return $this->securityToken;
    }

    /**
     * @param null $securityToken
     */
    public function setSecurityToken($securityToken): void
    {
        $this->securityToken = $securityToken;
    }

    /**
     * @return boolean
     */
    public function isUseSSL()
    {
        return $this->useSSL;
    }

    /**
     * @param boolean $useSSL
     */
    public function setUseSSL($useSSL)
    {
        $this->useSSL = $useSSL;
    }

    /**
     * Validates bucket name--throw OssException if it's invalid
     *
     * @param $options
     * @throws OssException
     */
    private function authPreCheckBucket($options)
    {
        if (!(('/' == $options[OssConst::OSS_OBJECT]) && ('' == $options[OssConst::OSS_BUCKET]) && ('GET' == $options[OssConst::OSS_METHOD])) && !OssUtil::validateBucket($options[OssConst::OSS_BUCKET])) {
            throw new OssException('"' . $options[OssConst::OSS_BUCKET] . '"' . 'bucket name is invalid');
        }
    }

    /**
     *
     * Validates the object name--throw OssException if it's invalid.
     *
     * @param $options
     * @throws OssException
     */
    private function authPreCheckObject($options)
    {
        if (isset($options[OssConst::OSS_OBJECT]) && $options[OssConst::OSS_OBJECT] === '/') {
            return;
        }

        if (isset($options[OssConst::OSS_OBJECT]) && !OssUtil::validateObject($options[OssConst::OSS_OBJECT])) {
            throw new OssException('"' . $options[OssConst::OSS_OBJECT] . '"' . ' object name is invalid');
        }
    }

    /**
     * ??????object?????????????????????gbk??????gb2312????????????????????????utf8??????
     *
     * @param mixed $options ??????
     */
    private function authPreCheckObjectEncoding(&$options)
    {
        $tmpObject = $options[OssConst::OSS_OBJECT];
        try {
            if (OssUtil::isGb2312($options[OssConst::OSS_OBJECT])) {
                $options[OssConst::OSS_OBJECT] = iconv('GB2312', "UTF-8//IGNORE", $options[OssConst::OSS_OBJECT]);
            } elseif (OssUtil::checkChar($options[OssConst::OSS_OBJECT], true)) {
                $options[OssConst::OSS_OBJECT] = iconv('GBK', "UTF-8//IGNORE", $options[OssConst::OSS_OBJECT]);
            }
        } catch (\Exception $e) {
            try {
                $tmpObject = iconv(mb_detect_encoding($tmpObject), "UTF-8", $tmpObject);
            } catch (\Exception $e) {
            }
        }
        $options[OssConst::OSS_OBJECT] = $tmpObject;
    }

    /**
     * ??????ACL????????????????????????????????????????????????????????????
     *
     * @param $options
     * @throws InvalidUrl
     * @throws OssException
     */
    private function authpreCheckAcl($options)
    {
        if (isset($options[OssConst::OSS_HEADERS][OssConst::OSS_ACL]) && !empty($options[OssConst::OSS_HEADERS][OssConst::OSS_ACL])) {
            if (!in_array(strtolower($options[OssConst::OSS_HEADERS][OssConst::OSS_ACL]), OssConst::$OSS_ACL_TYPES)) {
                throw new OssException($options[OssConst::OSS_HEADERS][OssConst::OSS_ACL] . ':' . 'acl is invalid(private,public-read,public-read-write)');
            }
        }
    }

    /**
     * ??????bucket,options??????
     *
     * @param string $bucket
     * @param string $object
     * @param array  $options
     * @param bool   $isCheckObject
     * @throws OssException
     */
    private function preCheckCommon($bucket, $object, &$options, $isCheckObject = true)
    {
        if ($isCheckObject) {
            $this->preCheckObject($object);
        }
        $this->preCheckOptions($options);
        $this->preCheckBucket($bucket);
    }

    /**
     * checks parameters
     *
     * @param array  $options
     * @param string $param
     * @param string $funcName
     * @throws OssException
     */
    private function preCheckParam($options, $param, $funcName)
    {
        if (!isset($options[$param])) {
            throw new OssException('The `' . $param . '` options is required in ' . $funcName . '().');
        }
    }

    /**
     * Checks md5
     *
     * @param array $options
     * @return bool|null
     */
    private function isCheckMD5($options)
    {
        return $this->getValue($options, OssConst::OSS_CHECK_MD5, false, true, true);
    }

    /**
     * Gets value of the specified key from the options
     *
     * @param array  $options
     * @param string $key
     * @param string $default
     * @param bool   $isCheckEmpty
     * @param bool   $isCheckBool
     * @return bool|null
     */
    private function getValue($options, $key, $default = NULL, $isCheckEmpty = false, $isCheckBool = false)
    {
        $value = $default;
        if (isset($options[$key])) {
            if ($isCheckEmpty) {
                if (!empty($options[$key])) {
                    $value = $options[$key];
                }
            } else {
                $value = $options[$key];
            }
            unset($options[$key]);
        }
        if ($isCheckBool) {
            if ($value !== true && $value !== false) {
                $value = false;
            }
        }
        return $value;
    }

    /**
     * Gets mimetype
     *
     * @param string $object
     * @return string
     */
    private function getMimeType($object, $file = null)
    {
        if (!is_null($file)) {
            $type = MimeTypes::getMimetype($file);
            if (!is_null($type)) {
                return $type;
            }
        }

        $type = MimeTypes::getMimetype($object);
        if (!is_null($type)) {
            return $type;
        }

        return OssConst::DEFAULT_CONTENT_TYPE;
    }

    /**
     * ??????object??????
     *
     * @param string $object
     * @throws OssException
     */
    private function preCheckObject($object)
    {
        OssUtil::throwOssExceptionWithMessageIfEmpty($object, "object name is empty");
    }

    /**
     * ??????options??????
     *
     * @param array $options
     * @throws OssException
     */
    private function preCheckOptions(&$options)
    {
        OssUtil::validateOptions($options);
        if (!$options) {
            $options = array();
        }
    }

    /**
     * ??????option restore
     *
     * @param string $restore
     * @throws OssException
     */
    private function preCheckStorage($storage)
    {
        if (is_string($storage)) {
            switch ($storage) {
                case OssConst::OSS_STORAGE_ARCHIVE:
                    return;
                case OssConst::OSS_STORAGE_IA:
                    return;
                case OssConst::OSS_STORAGE_STANDARD:
                    return;
                case OssConst::OSS_STORAGE_COLDARCHIVE:
                    return;
                default:
                    break;
            }
        }
        throw new OssException('storage name is invalid');
    }

    /**
     * ??????bucket??????
     *
     * @param string $bucket
     * @param string $errMsg
     * @throws OssException
     */
    private function preCheckBucket($bucket, $errMsg = 'bucket is not allowed empty')
    {
        OssUtil::throwOssExceptionWithMessageIfEmpty($bucket, $errMsg);
    }

    ###############################????????????#########################################

    ###############################????????????#########################################

    /**
     * ?????????????????????????????????
     * bucket??????????????????????????????????????????????????????cname??????ip???????????????????????????
     *
     * @param $bucket
     * @return string ????????????????????????
     */
    private function generateHostname($bucket)
    {
        if ($this->hostType === OssConst::OSS_HOST_TYPE_IP) {
            $hostname = $this->hostname;
        } elseif ($this->hostType === OssConst::OSS_HOST_TYPE_CNAME) {
            $hostname = $this->hostname;
        } else {
            // ?????????????????????endpoint
            $hostname = ($bucket == '') ? $this->hostname : ($bucket . '.') . $this->hostname;
        }
        return $hostname;
    }

    /**
     * ???????????????????????????????????????
     *
     * @param $options
     * @return string ??????????????????
     */
    private function generateResourceUri($options)
    {
        $resourceUri = "";
        // resource_uri + bucket
        if (isset($options[OssConst::OSS_BUCKET]) && '' !== $options[OssConst::OSS_BUCKET]) {
            if ($this->hostType === OssConst::OSS_HOST_TYPE_IP) {
                $resourceUri = '/' . $options[OssConst::OSS_BUCKET];
            }
        }
        // resource_uri + object
        if (isset($options[OssConst::OSS_OBJECT]) && '/' !== $options[OssConst::OSS_OBJECT]) {
            $resourceUri .= '/' . str_replace(array('%2F', '%25'), array('/', '%'), rawurlencode($options[OssConst::OSS_OBJECT]));
        }
        // resource_uri + sub_resource
        $conjunction = '?';
        if (isset($options[OssConst::OSS_SUB_RESOURCE])) {
            $resourceUri .= $conjunction . $options[OssConst::OSS_SUB_RESOURCE];
        }
        return $resourceUri;
    }

    /**
     * ??????query_string
     *
     * @param mixed $options
     * @return string
     */
    private function generateQueryString($options)
    {
        $queryStringParams = [];
        //????????????
        if (isset($options[OssConst::OSS_QUERY_STRING])) {
            $queryStringParams = array_merge($queryStringParams, $options[OssConst::OSS_QUERY_STRING]);
        }
        return OssUtil::toQueryString($queryStringParams);
    }

    private function generateRequestUrl($options, $signableQueryString)
    {
        // ???????????????????????????hostname?????????????????????????????????????????????bucket??????????????????????????????
        $hostname = $this->generateHostname($options[OssConst::OSS_BUCKET]);
//        $hostname = "192.168.159.1:1123";
        //??????????????????
        $resourceUri = $this->generateResourceUri($options);
        //????????????????????????
        $queryString = $this->generateQueryString($options);

        //????????????URL
        $conjunction = '?';
        $nonSignableResource = '';
        if (isset($options[OssConst::OSS_SUB_RESOURCE])) {
            $conjunction = '&';
        }
        if ($signableQueryString !== '') {
            $signableQueryString = $conjunction . $signableQueryString;
            $conjunction = '&';
        }
        if ($queryString !== '') {
            $nonSignableResource .= $conjunction . $queryString;
            $conjunction = '&';
        }

        // ??????????????????????????????????????????https??????http
        $scheme = $this->useSSL ? 'https://' : 'http://';
        //????????????
        $this->requestUrl = $scheme . $hostname . $resourceUri . $signableQueryString . $nonSignableResource;
        return [$conjunction, $signableQueryString];
    }

    /**
     * ??????????????????UserAgent
     *
     * @return string
     */
    private function generateUserAgent()
    {
        return OssConst::OSS_NAME . "/" . OssConst::OSS_VERSION . " (" . php_uname('s') . "/" . php_uname('r') . "/" . php_uname('m') . ";" . PHP_VERSION . ")";
    }

    /**
     * ?????????????????????
     * setHttpClientFileStream
     * @param HttpClient $httpClient
     * @param            $options
     * @author Tioncico
     * Time: 9:57
     */
    private function setHttpClientFileStream(HttpClient $httpClient, $options)
    {
//        if (isset($options[OssConst::OSS_SEEK_TO])) {
//            $httpClient->setSeekPosition((integer)$options[OssConst::OSS_SEEK_TO]);
//        }

        if (isset($options[OssConst::OSS_FILE_DOWNLOAD])) {
//            $httpClient->getClient()->download($this->requestUrl ,$options[OssConst::OSS_FILE_DOWNLOAD],$options[OssConst::OSS_SEEK_TO]);
        }

        // Streaming uploads
        if (isset($options[OssConst::OSS_FILE_UPLOAD])) {
            $fileStream = new SplFileStream($options[OssConst::OSS_FILE_UPLOAD]);
            if (isset($options[OssConst::OSS_SEEK_TO])) {
                $fileStream->seek($options[OssConst::OSS_SEEK_TO]);
            }
            $httpClient->getClient()->setData($fileStream->read($options[OssConst::OSS_CONTENT_LENGTH] ?? $fileStream->getSize()));
        }
    }

    /**
     * ??????http????????????header
     * setHttpClientHeaders
     * @param HttpClient $httpClient
     * @param            $headers
     * @author Tioncico
     * Time: 14:18
     */
    protected function setHttpClientHeaders(HttpClient $httpClient, $headers)
    {
        if (!isset($headers[OssConst::OSS_ACCEPT_ENCODING])) {
//            $headers[OssConst::OSS_ACCEPT_ENCODING] = '';
        }

        uksort($headers, 'strnatcasecmp');
        foreach ($headers as $headerKey => $headerValue) {
            $headerValue = str_replace(array("\r", "\n"), '', $headerValue);
            if ($headerValue !== '' || $headerKey === OssConst::OSS_ACCEPT_ENCODING) {
                $httpClient->setHeader($headerKey, $headerValue, false);
            }
        }
    }

    /**
     * ??????????????????
     * setHttpClientMethod
     * @param HttpClient $httpClient
     * @param            $options
     * @author Tioncico
     * Time: 16:22
     */
    protected function setHttpClientMethod(HttpClient $httpClient, $options)
    {
        if (isset($options[OssConst::OSS_METHOD])) {
            $httpClient->setMethod($options[OssConst::OSS_METHOD]);
        }
    }

    /**
     * ?????????????????????
     * setHttpClientData
     * @param HttpClient $httpClient
     * @param            $options
     * @author Tioncico
     * Time: 16:23
     */
    protected function setHttpClientData(HttpClient $httpClient, $options)
    {
        if (isset($options[OssConst::OSS_CONTENT])) {
            $httpClient->getClient()->setData($options[OssConst::OSS_CONTENT]);
        }
    }

    /**
     * ???????????????auth??????,???????????????
     * preAuth
     * @param $options
     * @param $signature
     * @param $conjunction
     * @return string|null
     * @author Tioncico
     * Time: 16:28
     */
    protected function preAuth($options, $signature, $conjunction)
    {
        if (isset($options[OssConst::OSS_PREAUTH]) && (integer)$options[OssConst::OSS_PREAUTH] > 0) {
            $signedUrl = $this->requestUrl . $conjunction . OssConst::OSS_URL_ACCESS_KEY_ID . '=' . rawurlencode($this->config->getAccessKeyId()) . '&' . OssConst::OSS_URL_EXPIRES . '=' . $options[OssConst::OSS_PREAUTH] . '&' . OssConst::OSS_URL_SIGNATURE . '=' . rawurlencode($signature);
            return $signedUrl;
        } elseif (isset($options[OssConst::OSS_PREAUTH])) {
            return $this->requestUrl;
        } else {
            return null;
        }
    }

    ###############################????????????#########################################

    /**
     * downFile
     * @param Response $response
     * @param          $options
     * @author Tioncico
     * Time: 13:57
     */
    protected function downFile(Response $response, $options)
    {
        //?????????????????????,?????????
        if (isset($options[OssConst::OSS_FILE_DOWNLOAD])) {
            $file = new SplFileStream($options[OssConst::OSS_FILE_DOWNLOAD]);
            if (isset($options[OssConst::OSS_SEEK_TO])) {
                $file->truncate((int)$options[OssConst::OSS_SEEK_TO]);
            }
            $file->write($response->getBody());
        }
    }


}
