<?php

/**
 * 
 * This file is part of the PhpExtJS plugin for S framework.
 * 
 * @license http://opensource.org/licenses/GPL-3.0 GPL-3.0
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 31/07/2013
 * 
 */

namespace S\Plugins\PhpExtJS;

// Use V.Session
use V\Session\Manager as SessionManager;
use V\Session\Segment;

/**
 * 
 * A repo to store things during requests
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 31/07/2013
 * 
 */
class Repo
{

    /**
     * 
     * Store anything
     * 
     * @param string $id The ID to store the thing, it will be used later to get it back
     * @param mixed $anything Anything you want to store
     * 
     * @return void
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    public static function store($id, $anything)
    {
        $manager = SessionManager::instance();
        $segment = new Segment($manager, 'repo');

        // the storage part
        isset($segment->things) || $segment->things = array();
        $segment->things[$id] = $anything;
    }

    /**
     * 
     * Get something
     * 
     * @param string $id The ID used to store the thing
     * 
     * @return mixed The thing
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    public static function get($id)
    {
        $manager = SessionManager::instance();
        $segment = new Segment($manager, 'repo');

        // get the thing
        if (isset($segment->things))
            return $segment->things[$id];
    }

    /**
     * 
     * Destroy something
     * 
     * @param string $id The ID used to store the thing
     * 
     * @return mixed The thing
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 01/08/2013
     * 
     */
    public static function destroy($id)
    {
        $manager = SessionManager::instance();
        $segment = new Segment($manager, 'repo');

        // destroy the thing
        if (isset($segment->things))
            unset($segment->things[$id]);
    }

}