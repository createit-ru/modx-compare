<?php

/* An autoloader for Compare\Foo classes. This should be required()
 * by the user before attempting to instantiate any of the Compare
 * classes.
 */

spl_autoload_register(function ($class) {
    if (substr($class, 0, 8) !== 'Compare\\') {
        /* If the class does not lie under the "Compare" namespace,
         * then we can exit immediately.
         */
        return;
    }

    /* All of the classes have names like "Compare\Foo", so we need
     * to replace the backslashes with frontslashes if we want the
     * name to map directly to a location in the filesystem.
     */
    $class = str_replace('\\', '/', $class);
    $class = substr($class, 8);

    /* First, check under the current directory. It is important that
     * we look here first, so that we don't waste time searching for
     * test classes in the common case.
     */
    $path = dirname(__FILE__).'/src/'.$class.'.php';
    if (is_readable($path)) {
        require_once $path;
    }
});