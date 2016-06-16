<?php

namespace App\Components;

use Instante\Application\UI\Control;
use Instante\RequireJS\JSModuleContainer;

/**
 * Temporarily residing in skeleton - will move to frontend package in future
 */
class JsLoader extends Control
{
    /** @var bool @template */
    private $source;

    /** @var JSModuleContainer @template */
    private $jsContainer;

    /**
     * @param bool $source - use source assets? (for development purposes)
     * @param JSModuleContainer $jsModuleContainer
     */
    public function __construct($source, JSModuleContainer $jsModuleContainer)
    {
        parent::__construct();
        $this->source = $source;
        $this->jsContainer = $jsModuleContainer;
    }

    public function beforeRender($args = [])
    {
        $this->template->{'module'} = isset($args['module']) ? $args['module'] : 'script';
    }
}
