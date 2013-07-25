<?php

/**
 * 
 * This file is part of the S framework for PHP, a framework based on the O framework.
 * 
 * @license http://opensource.org/licenses/GPL-3.0 GPL-3.0
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 21/07/2013
 * 
 */

namespace S\SystemsWindow;

/**
 * 
 * This controller and its views can be used when we have menus that have big depth (we break it, first level of menus
 * appear in the S main toolbar, the other level will be shown in this Window toolbar)
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 21/07/2013
 * 
 */
class Controller extends \O\Controller
{

    /**
     * 
     * This Window's title
     * 
     * @var string
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 20/07/2013
     * 
     */
    private $window_title;

    /**
     * 
     * The menus this window will show in its toolbar
     * 
     * @var array
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 20/07/2013
     * 
     */
    private $menus;

    /**
     * 
     * Set the window's title and toolbar's menus
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 20/07/2013
     * 
     */
    public function __construct($window_title, array $menus)
    {
        parent::__construct();

        $this->window_title = $window_title;
        $this->menus = $menus;
    }

    /**
     * 
     * Send data to template and fetches the window's view
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 20/07/2013
     * 
     */
    public function init()
    {
        $this->getTemplate()->title = $this->window_title;
        $this->getTemplate()->menus = $this->menus;
        return array(
            'body' => $this->getTemplate()->fetch(__DIR__ . '/Views/Index.php')
        );
    }

}