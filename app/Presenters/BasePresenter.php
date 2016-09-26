<?php

namespace App\Presenters;

use Instante\RequireJS\Components\JsLoaderFactory;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{

    /** @var EntityManager @inject */
    public $em;

    /** @var JsLoaderFactory @inject */
    public $jsLoaderFactory;

    /**
     * @param string $module
     * @return boolean
     */
    public function isModuleCurrent($module)
    {
        if (!$lastSeparatorPosition = strrpos($this->name, ':')) { // not in module
            return FALSE;
        }

        return ltrim($module, ':') === substr($this->name, 0, $lastSeparatorPosition);
    }

    public function flashInfo($message)
    {
        return $this->flashMessage($message, 'info');
    }

    public function flashWarning($message)
    {
        return $this->flashMessage($message, 'warning');
    }

    public function flashSuccess($message)
    {
        return $this->flashMessage($message, 'success');
    }

    public function flashDanger($message)
    {
        return $this->flashMessage($message, 'danger');
    }

    protected function createComponentJsLoader()
    {
        return $this->jsLoaderFactory->create();
    }
}
