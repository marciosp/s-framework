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
 * Your view must extend this class
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 31/07/2013
 * 
 */
abstract class View
{

    /**
     * 
     * The Controller
     * 
     * @var Controller
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    private $controller;

    /**
     * 
     * A Widget
     * 
     * @param Widget $el The Widget
     * 
     * @return string The view filename (the compiled view)
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    final protected function show(Widget $el)
    {

        // convert the PHP $el to Json
        $el = str_replace(array('"%', '%"'), '', Encoder::encode($el));

        // create the compiled view file content
        $file = <<<FILE
<?php

use O\UI\Plugins\ExtJS\Manager as m;
?>

<?php m::start(); ?>
<script>
<?= m::cb(); ?>
    ($el);
</script>
<?php m::end(); ?>
FILE;

        // create the compiled view file in the config tpl folder
        $cfg = \S\App::cfg();
        file_put_contents($filename = rtrim($cfg['plugins']['PhpExtJS']['compiled_tpl_folder'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . sha1(get_class($this)) . '.php', $file);

        // return filename so the view can be fetched in the controller
        return $filename;
    }

    /**
     * 
     * Acessor for private properties
     * 
     * @param string $name The property
     * 
     * @return mixed
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * 
     * $controller mutator
     * 
     * @param Controller $controller A Controller object
     * 
     * @return View
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 13/08/2013
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
        return $this;
    }

}