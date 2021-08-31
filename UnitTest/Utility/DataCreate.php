<?php
namespace UnitTest\Utility;
use UnitTest\Utility\DataTrait\UserTraint;

class DataCreate
{
    use UserTraint;

    protected $data;

    public function __destruct()
    {
        $this->destroy();
    }
    function destroy()
    {
        if(empty($this->data)){
            return ;
        }
        foreach ($this->data as $key => $model){
            unset($this->data[$key]);
            $model->destroy();
        }
    }
}