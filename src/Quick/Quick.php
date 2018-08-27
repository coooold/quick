<?php
declare(strict_types=1);

/**
 * 框架初始化，为了保证框架运行，至少需要配置以下两个参数
 * router 路由表
 * runtime_path 运行时目录，用于存放缓存和日志文件
 * @package Quick
 */

namespace Quick;

// 引入一些全局函数，方便使用
require __DIR__ . '/Utils/functions.php';

use DI\ContainerBuilder;
use Aura\Router\RouterContainer;
use Quick\Middlewares\Dispatcher;
use Zend\Diactoros\ServerRequestFactory;
use Quick\Middlewares\RequestHandler;
use Quick\Middlewares\ResponseEmitter;

/**
 * Class Quick
 * @package Quick
 */
class Quick {
    /**
     * @var \DI\Container
     */
    static protected $container;

    /**
     * 框架初始化
     * @param array $config di配置
     */
    static public function bootstrap($config = array()) {
        $containerBuilder = new ContainerBuilder();
        //$containerBuilder->enableCompilation(__DIR__ . '/cache');
        $containerBuilder->useAutowiring(false);
        $containerBuilder->useAnnotations(true);

        $default = include(__DIR__ . '/conf/default.php');
        $merged = array_merge($default, $config);
        $containerBuilder->addDefinitions($merged);
        self::$container = $containerBuilder->build();
    }

    /**
     * 开始服务
     */
    static public function serve() {
        $queue = [
            \di(ResponseEmitter::class),
            \di(RequestHandler::class),
        ];

        $dispatcher = new Dispatcher($queue);

        // 如果传入类型是application/json，那么重置$_POST
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'json') !== false) {
            $_POST = \json_decode(file_get_contents("php://input"), true);
        }

        $dispatcher->handle(ServerRequestFactory::fromGlobals());
    }

    ///////////////// 下面是一些工具函数

    /**
     * 判断是否在命令行执行
     * @return bool
     */
    static public function isCli() {
        return php_sapi_name() == 'cli';
    }

    /**
     * 用于获取容器中配置或者实例的函数
     * @param $className
     * @return mixed
     */
    static public function di($className) {
        return self::$container->get($className);
    }

    /**
     * 根据路由生成url，使用query的形式，如果第一个字符是!，那么不对query内容进行转义
     * @param   string $name 路由名称和参数
     * @param  boolean $raw  不进行urlencode编码
     * @return false|string
     */
    static public function makeUrl($name, $raw = false) {
        /** @var RouterContainer $routerContainer */
        $routerContainer = self::di(RouterContainer::class);

        $info = parse_url($name);
        $path = $info['path'];
        if (isset($info['query'])) {
            parse_str($info['query'], $query);
        } else {
            $query = [];
        }

        if ($raw) {
            return $routerContainer->getGenerator()->generateRaw($path, $query);
        } else {
            return $routerContainer->getGenerator()->generate($path, $query);
        }
    }

    /**
     * 创建排他文件锁
     * @param $name
     * @throws \Exception
     */
    static public function flock($name) {
        $runtimePath = \di('runtime_path');
        if (!$runtimePath || !is_dir($runtimePath)) {
            throw new \Exception('runtime_path does not exist');
        }

        $lockPath = "{$runtimePath}/locks";
        if (!file_exists($lockPath)) {
            mkdir($lockPath);
        }

        static $fps = [];
        if (!isset($fps[$name])) {
            $lockFile = "{$lockPath}/{$name}.lock";
            $fps[$name] = fopen($lockFile, 'a');
        }

        if (!flock($fps[$name], LOCK_EX | LOCK_NB)) {
            throw new \Exception('无法创建排他锁 ' . $lockFile);
        }
    }
}