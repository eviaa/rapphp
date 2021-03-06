<?php
/**
 * User: jinghao@duohuo.net
 * Date: 18/11/28
 * Time: 下午11:13
 * Link:  http://magapp.cc
 * Copyright:南京灵衍信息科技有限公司
 */

namespace rap\util\http;

use Swoole\Coroutine\Http\Client;

class Http {

    private static function parseUrl($url) {
        $port = 80;
        if (strpos($url, 'http://') == 0) {
            $url = str_replace('http://', '', $url);
        } elseif (strpos($url, 'https://') == 0) {
            $url = str_replace('https://', '', $url);
            $port = 443;
        }
        $po = strpos($url, '/');
        if ($po) {
            $host = substr($url, 0, $po);
            $path = substr($url, $po);
        } else {
            $host = $url;
            $path = '/';
        }
        if (strpos($host, ':') > 0) {
            $hp = explode(':', $host);
            $host = $hp[ 0 ];
            $port = $hp[ 1 ];
        }
        return [$host, $path, $port];
    }

    public static function get($url, $header = []) {
        if (IS_SWOOLE && \Co::getuid()) {
            $hostPath = self::parseUrl($url);
            if(!$hostPath[0]){
                return  new HttpResponse(-1, [], '');
            }
            $cli = new Client($hostPath[ 0 ], $hostPath[ 2 ]);
            if ($header) {
                $cli->setHeaders($header);
            }
            $cli->get($hostPath[ 1 ]);
            $response = new HttpResponse($cli->statusCode, $cli->headers, $cli->body);
            $cli->close();
            return $response;
        } else {
            $response= \Unirest\Request::get($url, $header);
            return new HttpResponse($response->code, $response->headers, $response->raw_body);

        }
    }

    public static function post($url, $header = [], $data = []) {
        if (IS_SWOOLE && \Co::getuid()) {
            $hostPath = self::parseUrl($url);
            if(!$hostPath[0]){
                return  new HttpResponse(-1, [], '');
            }
            $cli = new Client($hostPath[ 0 ], $hostPath[ 2 ]);
            if ($header) {
                $cli->setHeaders($header);
            }
            $cli->post($hostPath[ 1 ], $data);
            $response = new HttpResponse($cli->statusCode, $cli->headers, $cli->body);
            $cli->close();
            return $response;
        } else {
            $data = \Unirest\Request\Body::Form($data);
            $response= \Unirest\Request::post($url, $header, $data);
            return new HttpResponse($response->code, $response->headers, $response->raw_body);
        }

    }

    public static function put($url, $header = [], $data = []) {
        //在 swoole 协程环境
        if (IS_SWOOLE && \Co::getuid()) {
            $hostPath = self::parseUrl($url);
            if(!$hostPath[0]){
                return  new HttpResponse(-1, [], '');
            }
            $cli = new Client($hostPath[ 0 ], $hostPath[ 2 ]);
            if ($header) {
                $cli->setHeaders($header);
            }
            if ($data && is_string($data)) {
                $cli->post($hostPath[ 1 ], $data);
            } else {
                $cli->post($hostPath[ 1 ], json_encode($data));
            };
            $response = new HttpResponse($cli->statusCode, $cli->headers, $cli->body);
            $cli->close();
            return $response;
        } else {
            if(!is_string($data)){
                $data= json_encode($data);
            }
            $response= \Unirest\Request::post($url, $header, $data);
            return new HttpResponse($response->code, $response->headers, $response->raw_body);
        }
    }

    public static function upload($url, $header = [], $data = [],$files=[]){
        if (IS_SWOOLE && \Co::getuid()) {
            $hostPath = self::parseUrl($url);
            if(!$hostPath[0]){
                return  new HttpResponse(-1, [], '');
            }
            $cli = new Client($hostPath[ 0 ], $hostPath[ 2 ]);
            if ($header) {
                $cli->setHeaders($header);
            }
            foreach ($files as $file=>$name) {
                $cli->addFile($file,$name);
            }
            $cli->post($hostPath[ 1 ], $data);
            $response = new HttpResponse($cli->statusCode, $cli->headers, $cli->body);
            $cli->close();
            return $response;
        } else {
            $body = \Unirest\Request\Body::Multipart($data, $files);
            $response = \Unirest\Request::post($url, $header, $body);
            return new HttpResponse($response->code, $response->headers, $response->raw_body);
        }

    }

}