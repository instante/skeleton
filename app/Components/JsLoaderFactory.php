<?php

namespace App\Components;

use Instante\RequireJS\JSModuleContainer;

/**
 * Temporarily residing in skeleton - will move to frontend package in future
 */
class JsLoaderFactory
{

    /** @var bool */
    private $dist;

    /** @var JSModuleContainer */
    private $jsModuleContainer;

    /**
     * @param bool $dist - use compiled dist files (fal
     * @param JSModuleContainer $jsModuleContainer
     */
    public function __construct($dist = TRUE, JSModuleContainer $jsModuleContainer)
    {
        $this->dist = $dist;
        $this->jsModuleContainer = $jsModuleContainer;
    }

    /** @return JsLoader */
    public function create()
    {
        return new JsLoader($this->dist, $this->jsModuleContainer);
    }
}
