<?php

/**
 * 
 * This file is part of the PhpExtJS plugin for S framework.
 * 
 * @license http://opensource.org/licenses/GPL-3.0 GPL-3.0
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 15/08/2013
 * 
 */

namespace S\Plugins\PhpExtJS;

/**
 * 
 * Conversion from Json already applying ISO-8859-1 treatment
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 15/08/2013
 * 
 */
class Decoder
{

    /**
     * 
     * Decodes any JSON to PHP
     * 
     * @param string $json The JSON string
     * 
     * @return mixed The PHP JSon representation
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 15/08/2013
     * 
     */
    public static function decode($json)
    {
        $r = function($j) use(&$r) {
                    if (is_array($j) || is_object($j))
                        foreach ($j as &$v)
                            $v = $r($v);
                    elseif (is_string($j)) {

                        // iso-8859-1
                        $j = utf8_decode($j);
                    }
                    return $j;
                };
        return $r(json_decode($json));
    }

}