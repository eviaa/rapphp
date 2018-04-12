<?php
/**
 * 南京灵衍信息科技有限公司
 * User: jinghao@duohuo.net
 * Date: 17/9/1
 * Time: 下午4:18
 */

namespace rap\web\mvc;


use rap\exception\MsgException;
use rap\ioc\Ioc;
use rap\web\HttpRequest;
use rap\web\HttpResponse;

class ControllerHandlerAdapter extends HandlerAdapter{

    private $controllerClass;

    private $method;

    private $viewBase;
    /**
     * @param $controllerClass
     * @param $method
     */
    public function __construct($controllerClass,$method){
        $this->controllerClass=$controllerClass;
        $this->method=$method;
    }

    public function handle(HttpRequest $request, HttpResponse $response){
        try{
            $clazzInstance=Ioc::get($this->controllerClass);
        }catch (\Error $exception){
            throw new MsgException("对应的路径不存在控制器");
        }
        $value=$this->invokeRequest($clazzInstance,$this->method,$request,$response);
        return $value;
    }

    public function viewBase(){
        if(!$this->viewBase){
            $func = new \ReflectionClass($this->controllerClass);
            $this->viewBase=substr(substr($func->getFileName(),0,stripos( $func->getFileName(),DIRECTORY_SEPARATOR."controller".DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR,strlen($_SERVER['DOCUMENT_ROOT'])+1);
        }
        return $this->viewBase;
    }

}