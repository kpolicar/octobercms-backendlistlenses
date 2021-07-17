<?php namespace Kpolicar\BackendListLenses;

use Backend;
use Backend\Widgets\Filter;
use Closure;
use System\Classes\PluginBase;

/**
 * BackendLenses Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'kpolicar.backendlenses::lang.plugin.name',
            'description' => 'kpolicar.backendlenses::lang.plugin.description',
            'author'      => 'Klemen Janez Poličar',
            'icon'        => 'icon-search'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        Filter::extend(function ($filter) {
            if ($filter->getConfig('lenses')) {
                $privatePropertyReader = function & ($object, $property) {
                    $propertyReference = & Closure::bind(function & () use ($property) {
                        return $this->$property;
                    }, $object, $object)->__invoke();

                    return $propertyReference;
                };

                $viewPath = & $privatePropertyReader($filter, 'viewPath');
                $viewPath = array_merge([
                    $filter->getViewPath('$/kpolicar/backendlistlenses/layouts/filter'),
                ], (array) $viewPath);
            }
        });
    }
}
