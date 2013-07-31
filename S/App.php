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
// V.I18n
use V\I18n\Manager as I18nManager;
use V\I18n\Locale;
use V\I18n\LocaleLocator;
use V\I18n\Translation;

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
     * The V\I18n\Translator object
     * 
     * @var V\I18n\Translator
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 25/07/2013
     * 
     */
    private static $t;

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

    private function loadTranslations()
    {
        $cfg = self::$cfg;

        // init the translator
        $locale_locator = new LocaleLocator;
        $manager = new I18nManager($locale_locator);

        // Portuguese translations
        $locale1 = new Locale('pt_BR');
        $locale1->addTranslations(array(
            'WRONG_CREDENTIALS' => new Translation('Usu&aacute;rio ou senha inv&aacute;lidos!'),
            'SIGNIN' => new Translation('Entrar'),
            'RESET' => new Translation('Limpar'),
            'TRYAGAINLATER' => new Translation('Tente novamente mais tarde.'),
            'FAILED' => new Translation('Falha'),
            'USERFIELD' => new Translation('Usu&aacute;rio'),
            'PASSWORDFIELD' => new Translation('Senha'),
            'LOGOUT' => new Translation('Sair')
        ));

        // English translations
        $locale2 = new Locale('en_US');
        $locale2->addTranslations(array(
            'WRONG_CREDENTIALS' => new Translation('Wrong user or password!'),
            'SIGNIN' => new Translation('Sign in'),
            'RESET' => new Translation('Reset'),
            'TRYAGAINLATER' => new Translation('Try again later.'),
            'FAILED' => new Translation('Failed'),
            'USERFIELD' => new Translation('User'),
            'PASSWORDFIELD' => new Translation('Password'),
            'LOGOUT' => new Translation('Logout')
        ));

        // set the translations
        $manager->set('S', $locale1);
        $manager->set('S', $locale2);

        // default en_US locale
        $manager->setLocale($cfg['locale'] ? $cfg['locale'] : 'en_US');

        // get the translator object
        self::$t = $manager->get('S');
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

        // includes the O App file (because autoload is started by O)
        include rtrim($cfg['paths']['O'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'App.php';

        $me = $this;

        // creates the O App
        $app = new \O\App(array(
                    'paths' => array(
                        // V Path
                        'V' => $cfg['paths']['V']
                    ),
                    'router' => array(
                        // S config base path
                        'base_path' => $cfg['paths']['base_path'],
                        //
                        // Default S Routes
                        'routes' => array(
                            //
                            // Webservices
                            'webservices/{api}[/*]' => array(
                                'do' => function($api, $params = '') use($cfg) {
                                    $request = new Request;

                                    // get the API LOCATOR (check whether the request is coming from a module)
                                    if (isset($request->params['module']) && $request->params['module'] #
                                            && isset($request->params['module_name']) && $request->params['module_name']) {
                                        $module_cfg = include rtrim($cfg['paths']['modules_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $request->params['module_name'] . DIRECTORY_SEPARATOR . 'Module.php';
                                        $locator = $module_cfg['locator']['apis']();
                                    } else {
                                        $locator = $cfg['locator']['apis']();
                                    }

                                    // creates the O\Manager
                                    $manager = new \O\Manager(
                                                    $locator, #
                                                    $api, #
                                                    // check for custom services that are not named "get", "post", "put" and "delete" in the __METHOD parameter
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

                                    // if your HTTP errors treatment don't stop the script, we stop here in case of a failure
                                    is_object($response) || die();

                                    // send the response
                                    $response->send();
                                },
                                //
                                // route filters
                                'filters' => array('auth_apis')
                            ),
                            //
                            // Systems
                            'systems/{page}[/{action}]' => array(
                                'do' => function($page, $action = 'init') use($cfg, $me) {
                                    $request = new Request;

                                    // get the SYSTEM LOCATOR (check whether the request is coming from a module)
                                    if (isset($request->params['module']) && $request->params['module'] #
                                            && isset($request->params['module_name']) && $request->params['module_name']) {
                                        $module_cfg = include rtrim($cfg['paths']['modules_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $request->params['module_name'] . DIRECTORY_SEPARATOR . 'Module.php';
                                        $locator = $module_cfg['locator']['systems']();
                                    } else {
                                        $locator = $cfg['locator']['systems']();
                                    }

                                    // Create the O\Manager
                                    $manager = new \O\Manager($locator, $page, $action);

                                    // fires before execute the controller action, so plugins can configure somethings
                                    HookManager::fire($me, 'before_action', array($this, $manager, $request));

                                    // execute the action
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

                                    // if your HTTP errors treatment don't stop the script, we stop here in case of a failure
                                    is_object($response) || die();

                                    // send the response
                                    $response->send();
                                },
                                //
                                // route filters
                                'filters' => array('auth_systems')
                            ),
                            //
                            // Main Page (default S main page)
                            '/' => array(
                                'do' => function() {

                                    // if we got here, so we have to open the S main view
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
                            // Logout (default S logout)
                            '/logout' => array(
                                'do' => function() use($cfg) {

                                    // starts and destroy the session (S login control is made upon session)
                                    $manager = SessionManager::instance();
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
                        // route filters (default lock for access to systems and webservices)
                        'filters' => array(
                            //
                            // this one protects your webservices/apis (if you don't want to protect it, just create a function that returns true in your config :D)
                            'auth_apis' => function(RouteInterface $route, RequestInterface $request) use($cfg) {
                                //
                                // call user API auth closure (expects return of true or false), passing HTTP BASIC USER and HTTP BASIC PASSWORD 
                                // (by default, S expects you'll use basic authentication with your webservices)
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
                                $manager = SessionManager::instance();
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

        // load translations stuff
        $this->loadTranslations();

        // some events (Right before route anything)
        $register_s = new Hook($app, 'before_route', function($app) use($cfg) {

                            // the Loader
                            $loader = $app->getLoader();

                            // register S in the Autoload, so we can create S classes as well as we create O and V classes
                            $loader->registerNamespace('S', __DIR__);

                            // if we've passed some extra namespace to add to our Autoloader, here we configure them
                            if (isset($cfg['autoload'])) {
                                if (isset($cfg['autoload']['prefixes'])) {
                                    $loader->registerPrefixes($cfg['autoload']['prefixes']);
                                } elseif (isset($cfg['autoload']['namespaces'])) {
                                    $loader->registerNamespaces($cfg['autoload']['namespaces']);
                                }
                            }
                        });

        // some events (When we have a route that exists but there is a filter that invalidated it)
        $invalid_route = new Hook($app, 'invalid_route', function($app, $e, $router, $routes, $request, $dispatcher) use($cfg) {

                            // webservices auth
                            if (0 === strpos($request->getUri(), rtrim($cfg['paths']['base_path'], '/') . '/webservices/')) {

                                // HTTP BASIC AUTH
                                $message = new Message();
                                $message->setStatusCode(401);

                                $header = new Header('WWW-Authenticate', 'Basic realm="Password protected webservices!"');
                                $message->addHeader($header);

                                $response = new Response($message);
                                $response->send();
                            }
                            // login page
                            elseif (rtrim($request->getUri(), '/') === rtrim($cfg['paths']['base_path'], '/')) {

                                // if we submitted the login form
                                if ($request->getMethod() === 'post' && isset($request->params['user']) && isset($request->params['pass'])) {

                                    // check the user and password with your auth function
                                    if ($cfg['auth']['systems']($request->params['user'], $request->params['pass'])) {
                                        //
                                        // store the user in the session (log in the user)
                                        $manager = SessionManager::instance();
                                        $user = new Segment($manager, 'user');
                                        $user->login = $request->params['user'];

                                        // tell extjs everything is fine
                                        die(json_encode(array('success' => true)));
                                    } else {

                                        // tell extjs that we have wrong user or password
                                        die(json_encode(array(
                                                    'failure' => true,
                                                    'msg' => (string) App::t('WRONG_CREDENTIALS')
                                                )));
                                    }
                                } else {

                                    // if we got here, so we have to open the login form
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

                                // if we got here we are trying to enter a URL we do not have access to, so we'll redirect the user to the login form
                                $message = new Message();

                                $header = new Header('Location', $cfg['paths']['base_path']);
                                $message->addHeader($header);

                                $response = new Response($message);
                                $response->send();
                            }
                        });

        // some events (NOT FOUND treatment)
        $route_not_found = new Hook($app, 'route_not_found', function($app, $e, $router, $routes, $request, $dispatcher) use($cfg) {
                            $cfg['REST']['errors']['404']($app, $e, $router, $routes, $request, $dispatcher);
                        });

        // Hook the hooks :D
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

    /**
     * 
     * Get the translation of a given text
     * 
     * @param $text string The ID of the text to be translated
     * 
     * @return \V\I18n\Translation
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 25/07/2013
     */
    public static function t($text)
    {
        return self::$t->get($text);
    }

}