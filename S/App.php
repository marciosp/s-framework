<?php

/**
 * 
 * This file is part of the S framework for PHP, a framework based on the O framework.
 * 
 * @license http://opensource.org/licenses/GPL-3.0 GPL-3.0
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 17/07/2013
 * 
 */

namespace S;

// V.Hook
use \V\Hook\Manager as HookManager;
use \V\Hook\Hook;
// V.Http
use \V\Http\Header;
use \V\Http\Message;
use \V\Http\Response;
// V.Session
use \V\Session\Manager as SessionManager;
use \V\Session\Segment;
// V.Router
use \V\Router\Request;
use \V\Router\RequestInterface;
use \V\Router\RouteInterface;

/**
 * 
 * Your bootstrap file must instanciate this class
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 17/07/2013
 * 
 */
class App
{

    /**
     * 
     * The config passed in the constructor
     * 
     * @var array
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 17/07/2013
     * 
     */
    private static $cfg;

    /**
     * 
     * Sets the config array
     * 
     * @param array $cfg App configuration
     * 
     * @return App
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 17/07/2013
     */
    public function __construct(array $cfg)
    {
        self::$cfg = $cfg;
    }

    /**
     * 
     * Starts the App
     * 
     * @return void
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 17/07/2013
     */
    public function run()
    {

        // loads O framework
        $this->loadO();
    }

    /**
     * 
     * Loads O framework correctly
     * 
     * @return void
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 17/07/2013
     */
    private function loadO()
    {
        $cfg = self::$cfg;

        // includes the O App file
        include rtrim($cfg['paths']['O'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'App.php';

        // creates the O App
        $app = new \O\App(array(
                    'paths' => array(
                        'V' => $cfg['paths']['V']
                    ),
                    'router' => array(
                        'base_path' => $cfg['paths']['base_path'],
                        'routes' => array(
                            //
                            // Webservices
                            'webservices/{api}[/*]' => array(
                                'do' => function($api, $params = '') use($cfg) {

                                    // V classes
                                    $request = new Request;

                                    // get the API LOCATOR
                                    if ($request->params['module'] && $request->params['module_name']) {
                                        $module_cfg = include rtrim($cfg['paths']['modules_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $request->params['module_name'] . DIRECTORY_SEPARATOR . 'Module.php';
                                        $locator = $module_cfg['locator']['apis']();
                                    } else {
                                        $locator = $cfg['locator']['apis']();
                                    }

                                    // O classes

                                    $manager = new \O\Manager(
                                                    $locator, #
                                                    $api, #
                                                    //
                                                // check for custom services that are not named "get", "post", "put" and "delete"
                                                    isset($request->params['__METHOD']) ? $request->params['__METHOD'] : strtolower($request->getMethod()) #
                                    );
                                    try {
                                        $response = $manager->exec($request, explode('/', $params));
                                    }
                                    // check for HTTP errors
                                    catch (\O\Exceptions\E501 $e) {
                                        $cfg['REST']['errors']['501']($e);
                                    } catch (\O\Exceptions\E500 $e) {
                                        $cfg['REST']['errors']['500']($e);
                                    } catch (\O\Exceptions\E404 $e) {
                                        $cfg['REST']['errors']['404']($e);
                                    }
                                    $response->send();
                                },
                                //
                                // route filters
                                'filters' => array('auth_apis')
                            ),
                            //
                            // Systems
                            'systems/{page}[/{action}]' => array(
                                'do' => function($page, $action = 'init') use($cfg) {

                                    // V classes
                                    $request = new Request;

                                    // get the SYSTEM LOCATOR
                                    if ($request->params['module'] && $request->params['module_name']) {
                                        $module_cfg = include rtrim($cfg['paths']['modules_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $request->params['module_name'] . DIRECTORY_SEPARATOR . 'Module.php';
                                        $locator = $module_cfg['locator']['systems']();
                                    } else {
                                        $locator = $cfg['locator']['systems']();
                                    }

                                    // O classes
                                    $manager = new \O\Manager($locator, $page, $action);
                                    try {
                                        $response = $manager->exec($request);
                                    }
                                    // check for HTTP errors
                                    catch (\O\Exceptions\E501 $e) {
                                        $cfg['REST']['errors']['501']($e);
                                    } catch (\O\Exceptions\E500 $e) {
                                        $cfg['REST']['errors']['500']($e);
                                    } catch (\O\Exceptions\E404 $e) {
                                        $cfg['REST']['errors']['404']($e);
                                    }
                                    is_object($response) || die();
                                    $response->send();
                                },
                                //
                                // route filters
                                'filters' => array('auth_systems')
                            ),
                            //
                            // Main Page
                            '/' => array(
                                'do' => function() {
                                    // if we have to open the login form
                                    $controller = new Main\Controller;
                                    $content = $controller->init();

                                    $message = new Message();
                                    $message->setStatusCode(200);
                                    $message->setBody($content['body']);

                                    $response = new Response($message);
                                    $response->send();
                                },
                                //
                                // route filters
                                'filters' => array('auth_systems')
                            ),
                            //
                            // Logout
                            '/logout' => array(
                                'do' => function() use($cfg) {

                                    // starts and destroy the session
                                    $manager = new SessionManager();
                                    $manager->start();
                                    $manager->destroy();

                                    // send back to login page
                                    $message = new Message();

                                    $header = new Header('Location', $cfg['paths']['base_path']);
                                    $message->addHeader($header);

                                    $response = new Response($message);
                                    $response->send();
                                }
                            )
                        ),
                        // 
                        // route filters
                        'filters' => array(
                            'auth_apis' => function(RouteInterface $route, RequestInterface $request) use($cfg) {
                                //
                                // call user API auth closure, passing HTTP BASIC USER and HTTP BASIC PASSWORD
                                return $cfg['auth']['apis'](
                                                $request->basic->user, #
                                                $request->basic->pass, #
                                                $route, #
                                                $request
                                );
                            },
                            'auth_systems' => function(RouteInterface $route, RequestInterface $request) use($cfg) {
                                // check for "return true", in the cases the App has no login page
                                if ($cfg['auth']['systems']('', '', $route, $request))
                                    return true;

                                // checks the Session (if we are logged)
                                $manager = new SessionManager();
                                $user = new Segment($manager, 'user');
                                return isset($user->login);
                            }
                        )
                    ),
                    //
                    // UI config
                    'UI' => $cfg['UI'],
                    //
                    // REST config
                    'REST' => array(
                        'base_url' => $cfg['REST']['base_url']
                    )
                ));

        // some events
        $register_s = new Hook($app, 'before_route', function($app) {
                            $app->getLoader()->registerNamespace('S', __DIR__);
                        });
        $invalid_route = new Hook($app, 'invalid_route', function($app, $e, $router, $routes, $request, $dispatcher) use($cfg) {
                            // webservices auth
                            if (0 === strpos($request->getUri(), rtrim($cfg['paths']['base_path'], '/') . '/webservices/')) {
                                $message = new Message();
                                $message->setStatusCode(401);

                                $header = new Header('WWW-Authenticate', 'Basic realm="Password protected webservices!"');
                                $message->addHeader($header);

                                $response = new Response($message);
                                $response->send();
                            }
                            // login page
                            elseif (rtrim($request->getUri(), '/') === rtrim($cfg['paths']['base_path'], '/')) {
                                //
                                // if we submitted the login form
                                if ($request->getMethod() === 'post') {
                                    if ($cfg['auth']['systems']($request->params['user'], $request->params['pass'])) {
                                        //
                                        // store the user in the session (log the user)
                                        $manager = new SessionManager();
                                        $user = new Segment($manager, 'user');
                                        $user->login = $request->params['user'];
                                        die(json_encode(array(
                                                    'success' => true
                                                )));
                                    } else {
                                        die(json_encode(array(
                                                    'failure' => true,
                                                    'msg' => 'Wrong user or password'
                                                )));
                                    }
                                } else {
                                    // if we have to open the login form
                                    $controller = new Login\Controller;
                                    $content = $controller->init();

                                    $message = new Message();
                                    $message->setStatusCode(200);
                                    $message->setBody($content['body']);

                                    $response = new Response($message);
                                    $response->send();
                                }
                            }
                            // systems auth
                            else {
                                // if we got here, so we'll present to the user a login/pass form (redirecting to the login page)
                                $message = new Message();

                                $header = new Header('Location', $cfg['paths']['base_path']);
                                $message->addHeader($header);

                                $response = new Response($message);
                                $response->send();
                            }
                        });
        $route_not_found = new Hook($app, 'route_not_found', function($app, $e, $router, $routes, $request, $dispatcher) use($cfg) {
                            $cfg['REST']['errors']['404']($app, $e, $router, $routes, $request, $dispatcher);
                        });
        HookManager::hook($invalid_route, $route_not_found, $register_s);

        // init the O application
        $app->init();
    }

    /**
     * 
     * CFG Acessor
     * 
     * @return array
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 19/07/2013
     */
    public static function cfg()
    {
        return self::$cfg;
    }

}