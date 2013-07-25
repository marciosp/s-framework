<?php

namespace S\Main;

class Controller extends \O\Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        return array(
            'body' => $this->getTemplate()->fetch(__DIR__ . '/Views/Index.php')
        );
    }

}