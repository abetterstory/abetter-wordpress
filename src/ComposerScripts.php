<?php

namespace ABetter\Wordpress;

use Composer\Script\Event;

class ComposerScripts {

    public static function renameHelperFunctions(Event $event) {

        $vendorDir   = $event->getComposer()->getConfig()->get('vendor-dir');
        $helpersPath = $vendorDir . '/laravel/framework/src/Illuminate/Foundation/helpers.php';

        if (!file_exists($helpersPath)) return;

        $content = file_get_contents($helpersPath);
        $content = str_replace("function_exists('__')", "function_exists('___')", $content);
        $content = str_replace('function __', 'function ___', $content);

        file_put_contents($helpersPath, $content);

    }
}
