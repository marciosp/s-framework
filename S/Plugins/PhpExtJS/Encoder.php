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

/**
 * 
 * Conversion to Json already applying ISO-8859-1 treatment and JS functions treatment
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 31/07/2013
 * 
 */
class Encoder
{

    /**
     * 
     * Encodes anything to JSON
     * 
     * @param mixed $anything Anything
     * 
     * @return string The JSON
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    public static function encode($anything)
    {

        // will store IDs and correspondents JS Functions
        $store = array();

        // recursive function to apply some treatments to every element of $anything
        $r = function($j) use(&$r, &$store) {
                    if (is_array($j) || is_object($j))
                        foreach ($j as &$v)
                            $v = $r($v);
                    elseif (is_string($j)) {

                        // iso-8859-1
                        $j = utf8_encode($j);

                        // JS functions
                        if (substr($j, 0, 1) === '%' && substr($j, -1) === '%') {

                            // generates an unique ID to replace the JS function in the code, before json_encode (after json_encode, will replace it back)
                            $id = sha1(uniqid(mt_rand(), true));

                            // take off enter, because javascript doesn't handle well this kind of things
                            $store[$id] = str_replace(array("\n", "\r\n"), '', $j);
                            $j = $id;
                        }
                    }
                    return $j;
                };

        // replace the functions back and take the double quotes of them
        return str_replace(array('"%', '%"'), '', strtr(json_encode($r($anything)), $store));
    }

}