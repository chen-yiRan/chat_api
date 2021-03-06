<?php

namespace EasySwoole\Oss\Tests\Tencent;

use EasySwoole\Oss\Tencent\Config;
use EasySwoole\Oss\Tencent\Exception\ServiceResponseException;
use EasySwoole\Oss\Tencent\Http\HttpClient;
use EasySwoole\Oss\Tencent\OssClient;
use PHPUnit\Framework\TestCase;

class COSTest extends TestCase
{
    const SYNC_TIME = 2;
    /**
     * @var $cosClient OssClient
     */
    private $cosClient;
    /**
     * @var $client \Qcloud\Cos\Client
     */
    private $client;
    private $bucket;
    private $bucket2;
    private $region;

    protected function setUp():void
    {
        $config = new Config([
            'appId'     => TX_APP_ID,
            'secretId'  => TX_SECRETID,
            'secretKey' => TX_SECRETKEY,
            'region'    => TX_REGION,
            'bucket'    => TX_BUCKET,
        ]);

        $this->cosClient = new \EasySwoole\Oss\Tencent\OssClient($config);
        try {
//            $this->cosClient->createBucket(['Bucket' => TX_BUCKET]);
        } catch (\Exception $e) {

        }

        $this->bucket = TX_BUCKET;
        $this->region = TX_REGION;
        $this->bucket2 = "tmp" . $this->bucket;
        $this->client = new \Qcloud\Cos\Client(
            [
                'region'      => $this->region,
                'credentials' => [
                    'appId'     => TX_APP_ID,
                    'secretId'  => TX_SECRETID,
                    'secretKey' => TX_SECRETKEY
                ]
            ]
        );
        try {
//            $this->client->createBucket(['Bucket' => $this->bucket2]);
        } catch (\Exception $e) {
        }

    }

    protected function tearDown():void
    {
//       $data =  $this->cosClient->deleteBucket(['Bucket' => $this->bucket2]);

    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    function generateRandomFile($size = 10, $filename = 'random-file')
    {
        exec("dd if=/dev/urandom of=" . $filename . " bs=1 count=" . (string)$size);
    }

    /**********************************
     * TestBucket
     **********************************/

    /*
    * put bucket,bucket????????????
    * BucketAlreadyOwnedByYou
    * 409
    */
    public function testCreateExistingBucket()
    {
        try {
            $data = $this->cosClient->createBucket(['Bucket' => $this->bucket]);
//           $data =  $this->client->createBucket(['Bucket' => $this->bucket2]);
//           var_dump($data);
        } catch (ServiceResponseException $e) {
            var_dump((string)$e);
            $this->assertTrue($e->getExceptionCode() === 'BucketAlreadyOwnedByYou' && $e->getStatusCode() === 409);
        }
    }

    /*
     * put bucket, ????????????region???bucket
     * 409
     */
    public function testValidRegionBucket()
    {
        $regionlist = array('cn-east', 'ap-shanghai',
            'cn-south', 'ap-guangzhou',
            'cn-north', 'ap-beijing-1',
            'cn-south-2', 'ap-guangzhou-2',
            'cn-southwest', 'ap-chengdu',
            'sg'  => 'ap-singapore',
            'tj'  => 'ap-beijing-1',
            'bj'  => 'ap-beijing',
            'sh'  => 'ap-shanghai',
            'gz'  => 'ap-guangzhou',
            'cd'  => 'ap-chengdu',
            'sgp' => 'ap-singapore');
        foreach ($regionlist as $region) {
            try {
                $config = new Config([
                    'appId'     => TX_APP_ID,
                    'secretId'  => TX_SECRETID,
                    'secretKey' => TX_SECRETKEY,
                    'region'    => $region,
                    'bucket'    => $this->bucket,
                ]);

                $this->cosClient = new \EasySwoole\Oss\Tencent\OssClient($config);

                $this->cosClient->createBucket(['Bucket' => $this->bucket]);
            } catch (ServiceResponseException $e) {
                $this->assertEquals([$e->getStatusCode()], [409]);
            }
        }
    }

    /*
     * put bucket, ????????????region???
     * 409
     */
    public function testInvalidRegionBucket()
    {
        $regionlist = array('cn-east-2', 'ap-shanghai-3');
        foreach ($regionlist as $region) {
            try {

                $config = new Config([
                    'appId'     => TX_APP_ID,
                    'secretId'  => TX_SECRETID,
                    'secretKey' => TX_SECRETKEY,
                    'region'    => $region,
                    'bucket'    => $this->bucket,
                ]);

                $this->cosClient = new \EasySwoole\Oss\Tencent\OssClient($config);

                $this->cosClient->createBucket(['Bucket' => $this->bucket]);
            } catch (ServiceResponseException $e) {
                $this->assertEquals(-1, $e->getStatusCode());
                $this->assertEquals('DNS Lookup resolve failed', $e->getResponse()->getErrMsg());
            }
        }
    }

    /*
     * get Service
     * 200
     */
    public function testGetService()
    {
        try {
            $data = $this->cosClient->ListBuckets();
//            $data = $this->client->ListBuckets();
//            var_dump($data);
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket,bucket????????????
     * InvalidBucketName
     * 400
     */
    public function testCreateInvalidBucket()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => 'qwe_123' . $this->bucket));
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertTrue($e->getExceptionCode() === 'InvalidBucketName' && $e->getStatusCode() === 400);
        }
    }

    /*
     * put bucket?????????bucket???????????????private
     * 200
     */
    public function testCreatePrivateBucket()
    {
        try {
            $this->cosClient->createBucket(
                array(
                    'Bucket' => "tmp" . TX_BUCKET,
                    'ACL'    => 'private'
                ));
            sleep(COSTest::SYNC_TIME);
            TestHelper::nuke("tmp" . TX_BUCKET);
            $this->assertTrue(TRUE);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket?????????bucket???????????????public-read
     * 200
     */
    public function testCreatePublicReadBucket()
    {
        try {
            TestHelper::nuke($this->bucket2);
            sleep(COSTest::SYNC_TIME);
            $this->cosClient->createBucket(
                array(
                    'Bucket' => $this->bucket2,
                    'ACL'    => 'public-read'
                )
            );
            sleep(COSTest::SYNC_TIME);
            TestHelper::nuke($this->bucket2);
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket?????????????????????
     * InvalidArgument
     * 400
     */
    public function testCreateInvalidACLBucket()
    {
        try {
            TestHelper::nuke($this->bucket2);
            sleep(COSTest::SYNC_TIME);
            $this->cosClient->createBucket(
                array(
                    'Bucket' => $this->bucket2,
                    'ACL'    => 'public'
                )
            );
            sleep(COSTest::SYNC_TIME);
            TestHelper::nuke($this->bucket2);
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            $this->assertTrue($e->getExceptionCode() === 'InvalidArgument' && $e->getStatusCode() === 400);
        }
    }

    /*
     * put bucket acl?????????bucket???????????????private
     * 200
     */
    public function testPutBucketAclPrivate()
    {
        try {
//            var_dump($this->bucket);
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket' => $this->bucket,
                    'ACL'    => 'private'
                )
            );

            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
//            var_dump($e->getResponse());
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket acl?????????bucket???????????????public-read
     * 200
     */
    public function testPutBucketAclPublicRead()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket' => $this->bucket,
                    'ACL'    => 'public-read'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket acl?????????????????????
     * InvalidArgument
     * 400
     */
    public function testPutBucketAclInvalid()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket' => $this->bucket,
                    'ACL'    => 'public'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            $this->assertTrue($e->getExceptionCode() === 'InvalidArgument' && $e->getStatusCode() === 400);
        }
    }

    /*
     * put bucket acl?????????bucket???????????????grant-read
     * 200
     */
    public function testPutBucketAclReadToUser()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket'    => $this->bucket,
                    'GrantRead' => 'id="qcs::cam::uin/2779643970:uin/2779643970"'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket acl?????????bucket???????????????grant-write
     * 200
     */
    public function testPutBucketAclWriteToUser()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket'     => $this->bucket,
                    'GrantWrite' => 'id="qcs::cam::uin/2779643970:uin/2779643970"'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket acl?????????bucket???????????????grant-full-control
     * 200
     */
    public function testPutBucketAclFullToUser()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket'           => $this->bucket,
                    'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/2779643970"'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket acl?????????bucket??????????????????????????????????????????
     * 200
     */
    public function testPutBucketAclToUsers()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket'           => $this->bucket,
                    'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/2779643970",id="qcs::cam::uin/2779643970:uin/2779643970",id="qcs::cam::uin/2779643970:uin/2779643970"'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket acl?????????bucket?????????????????????????????????
     * 200
     */
    public function testPutBucketAclToSubuser()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket'           => $this->bucket,
                    'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/2779643970"'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket acl?????????bucket???????????????????????????read???write???fullcontrol
     * 200
     */
    public function testPutBucketAclReadWriteFull()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket'           => $this->bucket,
                    'GrantRead'        => 'id="qcs::cam::uin/123:uin/123"',
                    'GrantWrite'       => 'id="qcs::cam::uin/2779643970:uin/2779643970"',
                    'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/2779643970"'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket acl?????????bucket???????????????grant?????????
     * InvalidArgument
     * 400
     */
    public function testPutBucketAclInvalidGrant()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket'           => $this->bucket,
                    'GrantFullControl' => 'id="qcs::camuin/321023:uin/2779643970"'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            $this->assertTrue($e->getExceptionCode() === 'InvalidArgument' && $e->getStatusCode() === 400);
        }
    }

    /*
     * todo
     * put bucket acl?????????bucket?????????????????????body????????????
     * 200
     */
    public function testPutBucketAclByBody()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket' => $this->bucket2,
                    'Grants' => array(
                        array(
                            'Grantee'    => array(
                                'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                                'ID'          => 'qcs::cam::uin/2779643970:uin/2779643970',
                                'Type'        => 'CanonicalUser',
                            ),
                            'Permission' => 'FULL_CONTROL',
                        ),
                    ),
                    'Owner'  => array(
                        'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                        'ID'          => 'qcs::cam::uin/2779643970:uin/2779643970',
                    )
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            var_dump($e->getMessage());
            $this->assertFalse(TRUE);
        }
    }

    /*
     * todo
   * put bucket acl?????????bucket?????????????????????body???????????????anyone
     * 200
     */
    public function testPutBucketAclByBodyToAnyone()
    {
        try {
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket' => $this->bucket2,
                    'Grants' => array(
                        array(
                            'Grantee'    => array(
                                'DisplayName' => 'qcs::cam::anyone:anyone',
                                'ID'          => 'qcs::cam::anyone:anyone',
                                'Type'        => 'CanonicalUser',
                            ),
                            'Permission' => 'FULL_CONTROL',
                        ),
                    ),
                    'Owner'  => array(
                        'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                        'ID'          => 'qcs::cam::uin/2779643970:uin/2779643970',
                    )
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
   * put bucket acl???bucket?????????
     * NoSuchBucket
     * 404
     */
    public function testPutBucketAclBucketNonexisted()
    {
        try {
            TestHelper::nuke($this->bucket2);
            sleep(COSTest::SYNC_TIME);
            $this->cosClient->PutBucketAcl(
                array(
                    'Bucket'           => $this->bucket2,
                    'GrantFullControl' => 'id="qcs::cam::uin/321023:uin/2779643970"'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            var_dump($e->getMessage());
            $this->assertTrue($e->getExceptionCode() === 'NoSuchBucket' && $e->getStatusCode() === 404);
        }
    }

    /*
     * put bucket acl???????????????
     * x200
     */
    public function testPutBucketAclCover()
    {
        try {
            $this->cosClient->PutBucketAcl(array(
                'Bucket'           => $this->bucket,
                'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/2779643970"',
                'GrantRead'        => 'id="qcs::cam::uin/2779643970:uin/2779643970"',
                'GrantWrite'       => 'id="qcs::cam::uin/2779643970:uin/2779643970"'));
            $this->cosClient->PutBucketAcl(array(
                'Bucket'     => $this->bucket,
                'GrantWrite' => 'id="qcs::cam::uin/2779643970:uin/2779643970"'));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * ??????head bucket
     * 200
     */
    public function testHeadBucket()
    {
        try {
            $this->cosClient->HeadBucket(array(
                'Bucket' => $this->bucket));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * head bucket???bucket?????????
     * NoSuchBucket
     * 404
     */
    public function testHeadBucketNonexisted()
    {
        try {
            TestHelper::nuke($this->bucket2);
            sleep(COSTest::SYNC_TIME);
            $this->cosClient->HeadBucket(array(
                'Bucket' => $this->bucket2));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            $this->assertTrue($e->getStatusCode() === 404);
        }
    }

    /*
     * get bucket,bucket??????
     * 200
     */
    public function testGetBucketEmpty()
    {
        try {
            $this->cosClient->ListObjects(array(
                'Bucket' => $this->bucket));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * get bucket???bucket?????????
     * NoSuchBucket
     * 404
     */
    public function testGetBucketNonexisted()
    {
        try {
            TestHelper::nuke($this->bucket2);
            sleep(COSTest::SYNC_TIME);
            $this->cosClient->ListObjects(
                array(
                    'Bucket' => $this->bucket2
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            $this->assertTrue($e->getExceptionCode() === 'NoSuchBucket' && $e->getStatusCode() === 404);
        }
    }


    /*
     * put bucket cors???cors??????????????????
     * 200
     */
    public function testPutBucketCors()
    {
        try {
            $this->cosClient->putBucketCors(
                array(
                    'Bucket'    => $this->bucket,
                    'CORSRules' => array(
                        array(
                            'ID'             => '1234',
                            'AllowedHeaders' => array('*',),
                            'AllowedMethods' => array('PUT',),
                            'AllowedOrigins' => array('*',),
                            'ExposeHeaders'  => array('*',),
                            'MaxAgeSeconds'  => 1,
                        ),
                        array(
                            'ID'             => '12345',
                            'AllowedHeaders' => array('*',),
                            'AllowedMethods' => array('GET',),
                            'AllowedOrigins' => array('*',),
                            'ExposeHeaders'  => array('*',),
                            'MaxAgeSeconds'  => 1,
                        ),
                    ),
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }


    /*
     * ??????get bucket cors
     * 200
     */
    public function testGetBucketCors()
    {
        try {
            $this->cosClient->putBucketCors(
                array(
                    'Bucket'    => $this->bucket,
                    'CORSRules' => array(
                        array(
                            'ID'             => '1234',
                            'AllowedHeaders' => array('*',),
                            'AllowedMethods' => array('PUT',),
                            'AllowedOrigins' => array('*',),
                            'ExposeHeaders'  => array('*',),
                            'MaxAgeSeconds'  => 1,
                        ),
                        array(
                            'ID'             => '12345',
                            'AllowedHeaders' => array('*',),
                            'AllowedMethods' => array('GET',),
                            'AllowedOrigins' => array('*',),
                            'ExposeHeaders'  => array('*',),
                            'MaxAgeSeconds'  => 1,
                        ),
                    ),
                )
            );
            $this->cosClient->getBucketCors(
                array(
                    'Bucket' => $this->bucket
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * bucket?????????cors???????????????get bucket cors
     * NoSuchCORSConfiguration
     * 404
     */
    public function testGetBucketCorsNull()
    {
        try {
            $this->cosClient->getBucketCors(
                array(
                    'Bucket' => $this->bucket
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            $this->assertTrue($e->getExceptionCode() === 'NoSuchCORSConfiguration' && $e->getStatusCode() === 404);
        }
    }

    /*
     * ??????get bucket lifecycle
     * 200
     */
    public function testGetBucketLifecycle()
    {
        try {
            $result = $this->cosClient->putBucketLifecycle(
                array(
                    'Bucket' => $this->bucket2,
                    'Rules'  => array(
                        array(
                            'Status'      => 'Enabled',
                            'Filter'      => array(
                                'Tag' => array(
                                    'Key'   => 'datalevel',
                                    'Value' => 'backup'
                                )
                            ),
                            'Transitions' => array(
                                array(
                                    # 30???????????????Standard_IA
                                    'Days'         => 30,
                                    'StorageClass' => 'Standard_IA'),
                                array(
                                    # 365???????????????Archive
                                    'Days'         => 365,
                                    'StorageClass' => 'Archive')
                            ),
                            'Expiration'  => array(
                                # 3650??????????????????
                                'Days' => 3650,
                            )
                        )
                    )
                )
            );
            $result = $this->cosClient->getBucketLifecycle(array(
                'Bucket' => $this->bucket,
            ));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * ??????delete bucket lifecycle
     * 200
     */
    public function testDeleteBucketLifecycle()
    {
        try {
            $result = $this->cosClient->putBucketLifecycle(
                array(
                    'Bucket' => $this->bucket2,
                    'Rules'  => array(
                        array(
                            'Status'      => 'Enabled',
                            'Filter'      => array(
                                'Tag' => array(
                                    'Key'   => 'datalevel',
                                    'Value' => 'backup'
                                )
                            ),
                            'Transitions' => array(
                                array(
                                    # 30???????????????Standard_IA
                                    'Days'         => 30,
                                    'StorageClass' => 'Standard_IA'),
                                array(
                                    # 365???????????????Archive
                                    'Days'         => 365,
                                    'StorageClass' => 'Archive')
                            ),
                            'Expiration'  => array(
                                # 3650??????????????????
                                'Days' => 3650,
                            )
                        )
                    )
                )
            );
//            $result = $this->cosClient->deleteBucketLifecycle(array(
//                // Bucket is required
//                'Bucket' => $this->bucket,
//            ));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
             print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket lifecycle?????????body????????????filter
     * 200
     */
    public function testPutBucketLifecycleNonFilter()
    {
        try {
            $result = $this->cosClient->putBucketLifecycle(
                array(
                    'Bucket' => $this->bucket,
                    'Rules'  => array(
                        array(
                            'Expiration'  => array(
                                'Days' => 1000,
                            ),
                            'ID'          => 'id1',
                            'Status'      => 'Enabled',
                            'Transitions' => array(
                                array(
                                    'Days'         => 100,
                                    'StorageClass' => 'Standard_IA'),
                            ),
                        ),
                    )
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            $this->assertTrue($e->getExceptionCode() === 'NoSuchBucket' && $e->getStatusCode() === 404);

        }
    }

    /*
     * put bucket,bucket????????????-
     * 200
     */
    public function testPutBucket2()
    {
        try {
            try {
                $this->cosClient->deleteBucket(array('Bucket' => '12345-' . $this->bucket));
            } catch (\Exception $e) {
            }
            $this->cosClient->createBucket(array('Bucket' => '12345-' . $this->bucket));
            $this->cosClient->deleteBucket(array('Bucket' => '12345-' . $this->bucket));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put bucket,bucket??????????????????-
     * 200
     */
    public function testPutBucket3()
    {
        try {
            $this->cosClient->createBucket(array('Bucket' => '12-333-4445' . $this->bucket));
            $this->cosClient->deleteBucket(array('Bucket' => '12-333-4445' . $this->bucket));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * ??????get bucket location
     * 200
     */
    public function testGetBucketLocation()
    {
        try {
            $this->cosClient->getBucketLocation(array('Bucket' => $this->bucket));
            $this->assertTrue(true);
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * bucket??????????????????get bucket location??????
     * NoSuchBucket
     * 404
     */
    public function testGetBucketLocationNonExisted()
    {
        try {
            TestHelper::nuke($this->bucket2);
            sleep(COSTest::SYNC_TIME);
            $this->cosClient->getBucketLocation(array('Bucket' => $this->bucket2));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            //            echo($e->getExceptionCode());
            //            echo($e->getStatusCode());
            $this->assertTrue($e->getExceptionCode() === 'NoSuchBucket' && $e->getStatusCode() === 404);
        }
    }

    /**********************************
     * TestObject
     **********************************/

    /*
     * put object, ?????????????????????
     * 200
     */
    public function testPutObjectLocalObject()
    {
        try {
            $key = '??????.txt';
            $body = $this->generateRandomString(1024 + 1023);
            $md5 = base64_encode(md5($body, true));
            $local_test_key = "local_test_file";
            $f = fopen($local_test_key, "wb");
            fwrite($f, $body);
            fclose($f);
            $this->cosClient->putObject(['Bucket' => $this->bucket,
                                         'Key'    => $key,
                                         'Body'   => fopen($local_test_key, "rb")]);
            $rt = $this->cosClient->getObject(['Bucket' => $this->bucket, 'Key' => $key]);
            $download_md5 = base64_encode(md5($rt['Body'], true));
            $this->assertEquals($md5, $download_md5);
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * upload, ???????????????
     * 200
     */
    public function testUploadLocalObject()
    {
        try {
            $key = '??????.txt';
            $body = $this->generateRandomString(1024 + 1023);
            $md5 = base64_encode(md5($body, true));
            $local_test_key = "local_test_file";
            $f = fopen($local_test_key, "wb");
            fwrite($f, $body);
            fclose($f);
            $this->cosClient->upload($bucket = $this->bucket,
                $key = $key,
                $body = fopen($local_test_key, "rb"),
                $options = ['PartSize' => 1024 * 1024 + 1]);
            $rt = $this->cosClient->getObject(['Bucket' => $this->bucket, 'Key' => $key]);
            $download_md5 = base64_encode(md5($rt['Body'], true));
            $this->assertEquals($md5, $download_md5);
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put object,???????????????????????????????????????
     * 200
     */
    public function testPutObjectEncryption()
    {
        try {
            $this->cosClient->putObject(
                array(
                    'Bucket'               => $this->bucket,
                    'Key'                  => '11//32//43',
                    'Body'                 => 'Hello World!',
                    'ServerSideEncryption' => 'AES256'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * ????????????Bucket?????????
     * NoSuchBucket
     * 404
     */
    public function testPutObjectIntoNonexistedBucket()
    {
        try {
            TestHelper::nuke($this->bucket2);
            sleep(COSTest::SYNC_TIME);
            $this->cosClient->putObject(
                array(
                    'Bucket' => $this->bucket, 'Key' => 'hello.txt', 'Body' => 'Hello World'
                )
            );
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            $this->assertTrue($e->getExceptionCode() === 'NoSuchBucket');
            $this->assertTrue($e->getStatusCode() === 404);
        }
    }


    /*
     * ???????????????
     * 200
     */
    public function testUploadSmallObject()
    {
        try {
            $this->cosClient->upload($this->bucket, '??????.txt', 'Hello World 11111111111');
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * ???????????????
     * 200
     */
    public function testPutObjectEmpty()
    {
        try {
            $this->cosClient->upload($this->bucket, '??????.txt', '');
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * ????????????????????????
     * 200
     */
    public function testPutObjectExisted()
    {
        try {
            $this->cosClient->upload($this->bucket, '??????.txt', '1234124');
            $this->cosClient->upload($this->bucket, '??????.txt', '?????????qwe');
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put object????????????????????????????????????x-cos-meta-
     * 200
     */
    public function testPutObjectMeta()
    {
        try {
            $this->cosClient->putObject(array(
                'Bucket'   => $this->bucket,
                'Key'      => '??????.txt',
                'Body'     => '1234124',
                'Metadata' => array(
                    'lew' => str_repeat('a', 1 * 20),
                )));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put object????????????????????????????????????x-cos-meta-
     * KeyTooLong
     * 400
     */
    public function testPutObjectMeta2K()
    {
        try {
            $this->cosClient->putObject(array(
                'Bucket'   => $this->bucket,
                'Key'      => '??????.txt',
                'Body'     => '1234124',
                'Metadata' => array(
                    'lew' => str_repeat('a', 3 * 1024),
                )));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            $this->assertEquals(
                [$e->getStatusCode(), $e->getExceptionCode()],
                [400, 'KeyTooLong']
            );
            print $e;
        }
    }

    /*
     * ??????????????????????????????
     * 200
     */
    public function testUploadComplexObject()
    {
        try {
            $key = '????????????????????????! \"#$%&\'()*+,-./0123456789:;<=>@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~';
            $this->cosClient->upload($this->bucket, $key, 'Hello World');
            $this->cosClient->headObject(array(
                'Bucket' => $this->bucket,
                'Key'    => $key
            ));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * ???????????????
     * 200
     */
    public function testUploadLargeObject()
    {
        try {
            $key = '??????123.txt';
            $body ="?????????888". $this->generateRandomString(2 * 1024 * 1024 + 1023)."?????????666";
            $md5 = base64_encode(md5($body, true));
            $this->cosClient->upload($bucket = $this->bucket,
                $key = $key,
                $body = $body,
                $options = ['PartSize' => 1024 * 1024 + 1]);

            $rt = $this->cosClient->getObject(['Bucket' => $this->bucket, 'Key' => $key]);
            $download_md5 = base64_encode(md5($rt['Body'], true));
            $this->assertEquals($md5, $download_md5);
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
//            var_dump($e->getResponse());
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * ????????????
     * 200
     */
    public function testResumeUpload()
    {
        try {
            $key = '??????.txt';
            $body = $this->generateRandomString(3 * 1024 * 1024 + 1023);
            $partSize = 1024 * 1024 + 1;
            $md5 = base64_encode(md5($body, true));
            $rt = $this->cosClient->CreateMultipartUpload(['Bucket' => $this->bucket,
                                                           'Key'    => $key]);
            $uploadId = $rt['UploadId'];
            $this->cosClient->uploadPart(['Bucket'     => $this->bucket,
                                          'Key'        => $key,
                                          'Body'       => substr($body, 0, $partSize),
                                          'UploadId'   => $uploadId,
                                          'PartNumber' => 1]);
            $this->cosClient->resumeUpload($bucket = $this->bucket,
                $key = $key,
                $body = $body,
                $uploadId = $uploadId,
                $options = ['PartSize' => $partSize]);

            $rt = $this->cosClient->getObject(['Bucket' => $this->bucket, 'Key' => $key]);
            $download_md5 = base64_encode(md5($rt['Body'], true));
            $this->assertEquals($md5, $download_md5);
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * ????????????
     * 200
     */
    public function testGetObject()
    {
        try {
            $this->cosClient->upload($this->bucket, '??????.txt', 'Hello World');
            $this->cosClient->getObject(array(
                'Bucket' => $this->bucket,
                'Key'    => '??????.txt',));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * get object???object????????????????????????
     * 200
     */
    public function testGetObjectSpecialName()
    {
        try {
            $this->cosClient->upload($this->bucket, '??????<>!@#^%^&*&(&^!@#@!.txt', 'Hello World');
            $this->cosClient->getObject(array(
                'Bucket' => $this->bucket,
                'Key'    => '??????<>!@#^%^&*&(&^!@#@!.txt',));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * get object??????????????????if-match???????????????true
     * 200
     */
    public function testGetObjectIfMatchTrue()
    {
        try {
            $this->cosClient->upload($this->bucket, '??????.txt', 'Hello World');
            $this->cosClient->getObject(array(
                'Bucket'  => $this->bucket,
                'Key'     => '??????.txt',
                'IfMatch' => '"b10a8db164e0754105b7a99be72e3fe5"'));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }


    /*
     * get object??????????????????if-match???????????????false
     * PreconditionFailed
     * 412
     */
    public function testGetObjectIfMatchFalse()
    {
        try {
            $this->cosClient->upload($this->bucket, '??????.txt', 'Hello World');
            $this->cosClient->getObject(array(
                'Bucket'  => $this->bucket,
                'Key'     => '??????.txt',
                'IfMatch' => '""'));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            $this->assertEquals(
                [$e->getStatusCode(), $e->getExceptionCode()],
                [412, 'PreconditionFailed']
            );
            print $e;
        }
    }

    /*
     * get object??????????????????if-none-match???????????????true
     * 200
     */
    public function testGetObjectIfNoneMatchTrue()
    {
        try {
            $this->cosClient->upload($this->bucket, '??????.txt', 'Hello World');
            $rt = $this->cosClient->getObject(array(
                'Bucket'      => $this->bucket,
                'Key'         => '??????.txt',
                'IfNoneMatch' => '"b10a8db164e0754105b7a99be72e3fe15"'));
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }


    /*
     * get object??????????????????if-none-match???????????????false
     * PreconditionFailed
     * 412
     */
    public function testGetObjectIfNoneMatchFalse()
    {
        try {
            $this->cosClient->upload($this->bucket, '??????.txt', 'Hello World');
            $this->cosClient->getObject(array(
                'Bucket'      => $this->bucket,
                'Key'         => '??????.txt',
                'IfNoneMatch' => '""'));

            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * ????????????url
     * 200
     */
    public function testGetObjectUrl()
    {
        try {
            $this->cosClient->getObjectUrl($this->bucket, 'hello.txt', '+10 minutes');
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * todo
     * ???????????????
     * 200
     */
    public function testCopySmallObject()
    {
        try {
            $this->cosClient->upload($this->bucket, '??????.txt', 'Hello World');
            $this->cosClient->copy($bucket = $this->bucket,
                $key = 'hi.txt',
                $copySource = ['Bucket' => $this->bucket,
                               'Region' => $this->region,
                               'Key'    => '??????.txt']);
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * todo
     * ???????????????
     * 200
     */
    public function testCopyLargeObject()
    {
        try {
            $src_key = '??????.txt';
            $dst_key = 'hi.txt';
            $body = $this->generateRandomString(2 * 1024 * 1024 + 333);
            $md5 = base64_encode(md5($body, true));
            $this->cosClient->upload($bucket = $this->bucket,
                $key = $src_key,
                $body = $body,
                $options = ['PartSize' => 1024 * 1024 + 1]);
            $this->cosClient->copy($bucket = $this->bucket,
                $key = $dst_key,
                $copySource = ['Bucket' => $this->bucket,
                               'Region' => $this->region,
                               'Key'    => $src_key],
                $options = ['PartSize' => 1024 * 1024 + 1]);

            $rt = $this->cosClient->getObject(['Bucket' => $this->bucket, 'Key' => $dst_key]);
            $download_md5 = base64_encode(md5($rt['Body'], true));
            $this->assertEquals($md5, $download_md5);
            $this->assertTrue(true);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * todo
     * ??????objectacl
     * 200
     */
    public function testPutObjectACL()
    {
        try {
            $this->cosClient->upload($this->bucket, '11', 'hello.txt');
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket' => $this->bucket,
                    'Key'    => '11',
                    'Grants' => array(
                        array(
                            'Grantee'    => array(
                                'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                                'ID'          => 'qcs::cam::uin/2779643970:uin/2779643970',
                                'Type'        => 'CanonicalUser',
                            ),
                            'Permission' => 'FULL_CONTROL',
                        ),
                        // ... repeated
                    ),
                    'Owner'  => array(
                        'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                        'ID'          => 'qcs::cam::uin/2779643970:uin/2779643970',
                    )
                )
            );
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }

    }


    /*
     * todo
     * ??????objectacl
     * 200
     */
    public function testGetObjectACL()
    {
        try {
            $this->cosClient->upload($this->bucket, '11', 'hello.txt');
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket' => $this->bucket,
                    'Key'    => '11',
                    'Grants' => array(
                        array(
                            'Grantee'    => array(
                                'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                                'ID'          => 'qcs::cam::uin/2779643970:uin/2779643970',
                                'Type'        => 'CanonicalUser',
                            ),
                            'Permission' => 'FULL_CONTROL',
                        ),
                    ),
                    'Owner'  => array(
                        'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                        'ID'          => 'qcs::cam::uin/2779643970:uin/2779643970',
                    )
                )
            );
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
        * put object acl?????????object???????????????private
        * 200
        */
    public function testPutObjectAclPrivate()
    {
        try {
            $this->cosClient->putObject(array('Bucket' => $this->bucket, 'Key' => '??????.txt', 'Body' => '123'));
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket' => $this->bucket,
                    'Key'    => '??????.txt',
                    'ACL'    => 'private'
                )
            );
            $this->assertTrue(TRUE);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put object acl?????????object???????????????public-read
     * 200
     */
    public function testPutObjectAclPublicRead()
    {
        try {
            $this->cosClient->putObject(array('Bucket' => $this->bucket, 'Key' => '??????.txt', 'Body' => '123'));
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket' => $this->bucket,
                    'Key'    => '??????.txt',
                    'ACL'    => 'public-read'
                )
            );
            $this->assertTrue(TRUE);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put object acl?????????????????????
     * InvalidArgument
     * 400
     */
    public function testPutObjectAclInvalid()
    {
        try {
            $this->cosClient->putObject(array('Bucket' => $this->bucket, 'Key' => '??????.txt', 'Body' => '123'));
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket' => $this->bucket,
                    'Key'    => '??????.txt',
                    'ACL'    => 'public'
                )
            );
        } catch (ServiceResponseException $e) {
            $this->assertTrue($e->getExceptionCode() === 'InvalidArgument' && $e->getStatusCode() === 400);
        }
    }

    /*
     * put object acl?????????object???????????????grant-read
     * 200
     */
    public function testPutObjectAclReadToUser()
    {
        try {
            $this->cosClient->putObject(array('Bucket' => $this->bucket, 'Key' => '??????.txt', 'Body' => '123'));
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket'    => $this->bucket,
                    'Key'       => '??????.txt',
                    'GrantRead' => 'id="qcs::cam::uin/2779643970:uin/2779643970"'
                )
            );
            $this->assertTrue(TRUE);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put object acl?????????object???????????????grant-full-control
     * 200
     */
    public function testPutObjectAclFullToUser()
    {
        try {
            $this->cosClient->putObject(array('Bucket' => $this->bucket, 'Key' => '??????.txt', 'Body' => '123'));
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket'           => $this->bucket,
                    'Key'              => '??????.txt',
                    'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/2779643970"'
                )
            );
            $this->assertTrue(TRUE);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put object acl?????????object??????????????????????????????????????????
     * 200
     */
    public function testPutObjectAclToUsers()
    {
        try {
            $this->cosClient->putObject(array('Bucket' => $this->bucket, 'Key' => '??????.txt', 'Body' => '123'));
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket'           => $this->bucket,
                    'Key'              => '??????.txt',
                    'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/2779643970",id="qcs::cam::uin/2779643970:uin/2779643970",id="qcs::cam::uin/2779643970:uin/2779643970"'
                )
            );
            $this->assertTrue(TRUE);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put object acl?????????object?????????????????????????????????
     * 200
     */
    public function testPutObjectAclToSubuser()
    {
        try {
            $this->cosClient->putObject(array('Bucket' => $this->bucket, 'Key' => '??????.txt', 'Body' => '123'));
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket'           => $this->bucket,
                    'Key'              => '??????.txt',
                    'GrantFullControl' => 'id="qcs::cam::uin/2779643970:uin/2779643970"'
                )
            );
            $this->assertTrue(TRUE);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * put object acl?????????object???????????????grant?????????
     * InvalidArgument
     * 400
     */
    public function testPutObjectAclInvalidGrant()
    {
        try {
            $this->cosClient->putObject(array('Bucket' => $this->bucket, 'Key' => '??????.txt', 'Body' => '123'));
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket'           => $this->bucket,
                    'Key'              => '??????.txt',
                    'GrantFullControl' => 'id="qcs::camuin/321023:uin/2779643970"'
                )
            );
            $this->assertTrue(TRUE);
        } catch (ServiceResponseException $e) {
            $this->assertTrue($e->getExceptionCode() === 'InvalidArgument' && $e->getStatusCode() === 400);
        }
    }

    /*
     * todo
     * put object acl?????????object?????????????????????body????????????
     * 200
     */
    public function testPutObjectAclByBody()
    {
        try {
            $this->cosClient->putObject(array('Bucket' => $this->bucket, 'Key' => '??????.txt', 'Body' => '123'));
            $this->cosClient->PutObjectAcl(
                array(
                    'Bucket' => $this->bucket,
                    'Key'    => '??????.txt',
                    'Grants' => array(
                        array(
                            'Grantee'    => array(
                                'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                                'ID'          => 'qcs::cam::uin/2779643970:uin/2779643970',
                                'Type'        => 'CanonicalUser',
                            ),
                            'Permission' => 'FULL_CONTROL',
                        ),
                        // ... repeated
                    ),
                    'Owner'  => array(
                        'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                        'ID'          => 'qcs::cam::uin/2779643970:uin/2779643970',
                    )
                )
            );
            $this->assertTrue(TRUE);
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

    /*
     * todo
     * put object acl?????????object?????????????????????body???????????????anyone
     * 200
     */
    public function testPutObjectAclByBodyToAnyone()
    {
        try {
            $this->cosClient->putObject(array('Bucket' => $this->bucket, 'Key' => '??????.txt', 'Body' => '123'));
            $this->cosClient->putObjectAcl(
                array(
                    'Bucket' => $this->bucket,
                    'Key'    => '??????.txt',
                    'Grants' => array(
                        array(
                            'Grantee'    => array(
                                'DisplayName' => 'qcs::cam::anyone:anyone',
                                'ID'          => 'qcs::cam::anyone:anyone',
                                'Type'        => 'CanonicalUser',
                            ),
                            'Permission' => 'FULL_CONTROL',
                        ),
                        // ... repeated
                    ),
                    'Owner'  => array(
                        'DisplayName' => 'qcs::cam::uin/2779643970:uin/2779643970',
                        'ID'          => 'qcs::cam::uin/2779643970:uin/2779643970',
                    )
                )
            );
        } catch (ServiceResponseException $e) {
            print $e;
            $this->assertFalse(TRUE);
        }
    }

}
