<?php
namespace App\Utility\Bean;

use EasySwoole\Spl\SplBean;

class ListBean extends SplBean{
    protected $page;//当前页数
    protected $pageSize;//每页总数
    protected $list;//数组
    protected $total;//总条数
    protected $pageCount;//总页数

    public function listChunk(callable $callback){
        foreach ($this->list as $key=>$value){
            $this->list[$key] = call_user_func($callback,$value);
        }
        return $this;
    }

    public function getList(){
        return $this->list;
    }
    public function setList($list): void
    {
        $this->list = $list;
    }
    public function getTotal()
    {
        return $this->total;
    }
    public function setTotal($total):void{
        $this->total = $total;
    }
    public function getPage(){
        return $this->page;
    }
    public function setPage($page):void
    {
        $this->page = $page;
    }
    public function getPageCount(){
        return $this->pageCount;
    }
    public function setPageCount($pageCount): void{
        $this->pageCount = $pageCount;
    }
    public function getPageSize(){
        return $this->pageSize;
    }
    public function setPageSize($pageSize): void
    {
        $this->pageSize = $pageSize;
    }
}