<?php

namespace Quick\Middlewares;

use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Quick\Utils\FISResource;

class Controller {

    /**
     * @Inject
     * @var \Zend\Diactoros\Response
     */
    protected $response;
    /** @var \Zend\Diactoros\ServerRequest */
    protected $request;

    /** @var array [RequestHandler] */
    protected $middlewares = [];

    /**
     * 返回控制器的前置中间件
     * @return array [RequestHandler]
     */
    public function getMiddlewares() {
        return $this->middlewares;
    }

    /**
     * 由于不能使用__construct，改用initialize
     * @return bool true继续执行，false停止执行
     */
    protected function initialize(): bool {
        return true;
    }

    /**
     * 输出json格式
     * @param $data
     */
    protected function json($data) {
        $this->response = $this->response->withHeader('Content-type', 'application/json');
        $this->response->getBody()->write(json_encode($data));
    }

    /**
     * 模板渲染
     * @param string $template
     * @param array  $data
     * @throws \Exception
     */
    protected function fis3($template, $data = []) {
        $fisPath = \di('runtime_path') . '/tpl_dist';
        if (!$fisPath || !is_dir($fisPath)) {
            throw new \Exception('tpl_dist does not exist');
        }

        FISResource::setConfig(array(
            'config_dir' => $fisPath,
            'template_dir' => $fisPath,
        ));

        $html = render($template, $data);

        $this->response->getBody()->write($html);
    }

    /**
     * 跳转
     * @param $path
     */
    protected function redirect($path) {
        $this->response = $this->response->withHeader('Location', $path);
    }

    /**
     * 给中间件执行的方法
     * @param ServerRequest $request
     * @return Response
     * @throws \Exception
     */
    public function __invoke(ServerRequest $request): Response {
        $this->request = $request;

        // 执行初始化方法，如果返回false，那么停止执行后续方法
        if (!$this->initialize()) {
            return $this->response;
        }

        $method = $request->getAttribute('method');

        if (!method_exists($this, $method)) {
            throw new \Exception('404');
        }

        $args = [];
        $params = $request->getAttributes();
        // $params = $request->getQueryParams();

        $methodReflection = new \ReflectionMethod($this, $method);
        foreach ($methodReflection->getParameters() as $param) {
            $default = null;
            $name = $param->getName();
            if ($param->isDefaultValueAvailable()) {
                $default = $param->getDefaultValue();
            }
            if (isset($params[$name])) {
                $args[$name] = $params[$name];
            } else {
                $args[$name] = $default;
            }
        }

        call_user_func_array([$this, $method], $args);

        return $this->response;
    }
}