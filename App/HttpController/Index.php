<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\HttpAnnotation\Utility\AnnotationDoc;

class Index extends Controller
{
    /**
     * 管理员文档
     */
    function adminDoc()
    {
        $parser = new AnnotationDoc();
        $string = $parser->scan2Html(EASYSWOOLE_ROOT . '/App/HttpController/Api/Admin');
        $this->response()->withAddedHeader('Content-type', "text/html");
        $this->response()->write($string);
    }
    public function index()
    {
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/welcome.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    function test()
    {
        $this->response()->write('this is test');
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }
}