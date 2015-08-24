<?php

namespace App\Presenters;

use Kdyby\Doctrine\EntityManager;
use Instante\RequireJS\JSModuleContainer;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{

    /** @var EntityManager @inject */
    public $em;

    /** @var  JSModuleContainer @inject */
    public $jsModuleContainer;

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

    /**
     * Common render method.
     * @return void
     */
    protected function beforeRender()
    {
        parent::beforeRender();
        $this->template->jsContainer = $this->jsModuleContainer;
    }
}
