<?php

namespace App\HttpController\Api\User\Chat\Group;

use App\HttpController\Api\User\Base;
use App\Model\Chat\Group\GroupSettingModel;
use App\Service\Chat\Group\GroupService;
use App\Service\Chat\Group\GroupSettingService;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccessParam;
use EasySwoole\HttpAnnotation\AnnotationTag\InjectParamsContext;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\Http\Message\Status;
use EasySwoole\Validate\Validate;

/**
 * GroupSetting
 * Class GroupSetting
 * Create With ClassGeneration
 * @ApiGroup(groupName="群组配置")
 * @ApiGroupAuth(name="")
 * @ApiGroupDescription("")
 */
class GroupSetting extends Base
{
	/**
	 * @Api(name="更新群组配置",path="/Api/User/Chat/Group/GroupSetting/updateSetting")
	 * @ApiDescription("更新群组配置")
	 * @Method(allow={GET,POST})
	 * @InjectParamsContext(key="param")
	 * @ApiSuccessParam(name="code",description="状态码")
	 * @ApiSuccessParam(name="result",description="api请求结果")
	 * @ApiSuccessParam(name="msg",description="api提示信息")
	 * @ApiSuccess({"code":200,"result":{"groupId":1151,"key":"USER_INVITE_CHECK","value":"1","settingId":30},"msg":"更新数据成功"})
	 * @ApiFail({"code":400,"result":[],"msg":"更新失败"})
	 * @Param(name="groupHash",optional="")
	 * @Param(name="key",lengthMax="32",optional="")
	 * @Param(name="value",lengthMax="64",optional="")
	 */
	public function updateSetting()
	{
		$param = ContextManager::getInstance()->get('param');
        $info = GroupSettingService::getInstance($this->who)->updateSetting($param['groupHash'],$param['key'],$param['value']);
        $this->writeJson(Status::CODE_OK, $info, "更新数据成功");
	}

	/**
	 * @Api(name="获取群组配置项",path="/Api/User/Chat/Group/GroupSetting/getGroupSetting")
	 * @ApiDescription("获取群组配置项")
	 * @Method(allow={GET,POST})
	 * @InjectParamsContext(key="param")
	 * @ApiSuccessParam(name="code",description="状态码")
	 * @ApiSuccessParam(name="result",description="api请求结果")
	 * @ApiSuccessParam(name="msg",description="api提示信息")
	 * @ApiSuccess({"code":200,"result":{"USER_INVITE":0,"USER_INVITE_CHECK":1,"USER_ADD_FRIEND":0,"GROUP_URL_SHARE":0},"msg":"获取配置成功!"})
	 * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
	 * @Param(name="groupHash", from={GET,POST}, alias="页数", optional="")
	 * @ApiSuccessParam(name="result[].settingId",description="")
	 * @ApiSuccessParam(name="result[].groupId",description="")
	 * @ApiSuccessParam(name="result[].key",description="")
	 * @ApiSuccessParam(name="result[].value",description="")
	 */
	public function getGroupSetting()
	{
		$param = ContextManager::getInstance()->get('param');
        $info = GroupSettingService::getInstance($this->who)->getGroupSetting($param['groupHash']);
        $this->writeJson(Status::CODE_OK, $info, '获取配置成功!');
	}

}

