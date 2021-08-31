<?php

namespace App\Model\Area;

use App\Model\BaseModel;
use App\Utility\Bean\ListBean;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\Utility\Schema\Table;

/**
 * AreaModel
 * Class AreaModel
 * Create With ClassGeneration
 * @property int    $id // 地区id
 * @property int    $code // 行政区划代码
 * @property string $name // 名字
 * @property string $province //省
 * @property string $city // 市
 * @property string $area //区
 * @property string $town //城镇地区
 */
class AreaModel extends BaseModel
{
    protected $tableName = 'area_list';


    public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): ListBean
    {
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
    }

    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colInt('id')->setIsPrimaryKey(true)->setIsUnsigned()->getIsAutoIncrement();
        $table->colBigInt('code', 2)->setColumnComment('行政区划代码');
        $table->colVarChar('name', 32)->setColumnComment('名称');
        $table->colVarChar('province', 32)->setColumnComment('省/直辖市');
        $table->colVarChar('city', 32)->setColumnComment('市');
        $table->colVarChar('area', 32)->setColumnComment('区');
        $table->colVarChar('town', 32)->setColumnComment('城镇地区');
        return $table;
    }
}

