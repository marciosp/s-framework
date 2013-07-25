<?php

namespace S\SystemsWindow;

class Controller extends \O\Controller
{

    private $window_title;

    private $menus;

    public function __construct($window_title, array $menus)
    {
        parent::__construct();

        $this->window_title = $window_title;
        $this->menus = $menus;
    }

    public function init()
    {
        $this->getTemplate()->title = $this->window_title;
        $this->getTemplate()->menus = $this->menus;
        return array(
            'body' => $this->getTemplate()->fetch(__DIR__ . '/Views/Index.php')
        );
    }

}