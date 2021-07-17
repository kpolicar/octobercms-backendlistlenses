<?php namespace Kpolicar\BackendListLenses\Extensions;


use Backend\Widgets\Filter;

class ExtendFilter
{
    public function __invoke(Filter $filter)
    {
        if ($this->shouldExtendWithListLens($filter)) {
            $this->overrideViews($filter);
        }
    }

    private function shouldExtendWithListLens(Filter $filter)
    {
        return optional($filter->getController())->isClassExtendedWith('Kpolicar.BackendListLenses.Behaviors.ListLensController')
            && $filter->getConfig('lenses') !== false;
    }

    private function overrideViews(Filter $filter)
    {
        $privatePropertyReader = function & ($object, $property) {
            $propertyReference = & \Closure::bind(function & () use ($property) {
                return $this->$property;
            }, $object, $object)->__invoke();

            return $propertyReference;
        };

        $viewPath = & $privatePropertyReader($filter, 'viewPath');
        $viewPath = array_merge([
            $filter->getViewPath('$/kpolicar/backendlistlenses/layouts/filter'),
        ], (array) $viewPath);
    }
}
