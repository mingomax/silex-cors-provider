<?php

namespace MrPrompt\Silex\Cors;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silex\Api\BootableProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cross-Origin Resource Sharing
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 * @author Marcel Araujo <admin@marcelaraujo.me>
 * @author Domingos Teruel <mingomax@dteruel.net.br>
 */
final class Cors implements CorsInterface, ServiceProviderInterface, BootableProviderInterface
{
    /**
     * (non-PHPdoc)
     * @see \Pimple\ServiceProviderInterface::register()
     */
    public function register(Container $container)
    {
        /**
         * Add the
         */
        $container[self::HTTP_CORS] = $container->protect(
            function (Request $request, Response $response) use ($container) {
                $response->headers->set("Access-Control-Max-Age", "86400");
                $response->headers->set("Access-Control-Allow-Origin", "*");

                return $response;
            }
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Silex\Api\BootableProviderInterface::boot()
     */
    public function boot(Application $app)
    {
        /* @var $routes \Symfony\Component\Routing\RouteCollection */
        $routes = $app['routes'];

        /* @var $route \Silex\Route */
        foreach ($routes->getIterator() as $id => $route) {
            $path = $route->getPath();

            $headers = implode(',', [
                'Authorization',
                'Accept',
                'X-Request-With',
                'Content-Type',
                'X-Session-Token',
                'X-Hmac-Hash',
                'X-Time',
                'X-Url'
            ]);

            /* @var $controller \Silex\Controller */
            $controller = $app->match(
                $path,
                function () use ($headers) {
                    return new Response(
                        null,
                        204,
                        [
                            "Allow" => "GET,POST,PUT,DELETE",
                            "Access-Control-Max-Age" => 84600,
                            "Access-Control-Allow-Origin" => "*",
                            "Access-Control-Allow-Credentials" => "false",
                            "Access-Control-Allow-Methods" => "GET,POST,PUT,DELETE",
                            "Access-Control-Allow-Headers" => $headers
                        ]
                    );
                }
            );

            $controller->method('OPTIONS');

            /* @var $controllerRoute \Silex\Route */
            $controllerRoute = $controller->getRoute();
            $controllerRoute->setCondition($route->getCondition());
            $controllerRoute->setSchemes($route->getSchemes());
            $controllerRoute->setMethods('OPTIONS');
        }
    }
}
