<?php namespace Kpolicar\BackendListLenses\Behaviors;

use Str;
use File;
use Event;
use Backend\Classes\ControllerBehavior;
use October\Rain\Extension\ExtendableTrait;

class ListLensController extends ControllerBehavior
{
    protected $listLensConfig = [];
    protected $requiredProperties = ['listLensConfig'];
    protected $activeLens;


    public function __construct($controller)
    {
        parent::__construct($controller);
        $this->config = $this->makeConfig($controller->listLensConfig);

        $this->actions = array_keys($this->config->lenses);
        foreach ($this->actions as $lens) {
            $controller->addDynamicMethod($lens, function () use ($lens) {
                return $this->listLensRun($lens);
            });
        }
    }

    public function listLensLenses()
    {
        return $this->config->lenses;
    }

    public function listLensActiveLens()
    {
        return $this->activeLens;
    }

    public function listLensActiveConfiguration($key=null)
    {
        return array_get(
            $this->controller->listLensLenses(),
            $this->controller->listLensActiveLens().($key ? '.'.$key:''),
            object_get($this->config, $key));
    }

    protected function listLensRun($lens)
    {
        $this->activeLens = $lens;
        Event::listen('system.extendConfigFile', function ($file, $config) {
            $controllerFileToOverride = $this->controller->getConfigPath($this->controller->listConfig);
            if ($file != File::localToPublic($controllerFileToOverride))
                return;

            $config['toolbar']['buttons'] = '$/kpolicar/backendlistlenses/layouts/toolbar/_buttons.htm';
            return $config;
        });

        $this->controller->index();
        $this->controller->pageTitle =
            trans($this->listLensActiveConfiguration('title')) ?: $this->controller->pageTitle;
        $this->controller->listGetWidget()->bindEvent(
            'list.extendQuery',
            [$this->controller, $this->getMethodNameForListLensQuery($lens)]);
        return $this->controller->listRender();
    }

    protected function getMethodNameForListLensQuery($lens)
    {
        return 'lens'.Str::studly($lens);
    }
}
