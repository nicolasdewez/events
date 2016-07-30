<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class BreadcrumbListener.
 */
class BreadcrumbListener
{
    /** @var array */
    private $parameters;

    /**
     * @param array $parameters
     */
    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        $session = $request->getSession();
        $session->remove('breadcrumb');

        $route = $request->attributes->get('_route');

        $breadcrumb = $this->buildBreadcrumb($route, $route);
        if (!count($breadcrumb)) {
            return;
        }

        $session->set('breadcrumb', array_reverse($breadcrumb));
    }

    /**
     * @param string $searchedRoute
     * @param string $actualRoute
     *
     * @return array
     */
    private function buildBreadcrumb($searchedRoute, $actualRoute)
    {
        if (!isset($this->parameters[$searchedRoute])) {
            return [];
        }

        $routeParameters = $this->parameters[$searchedRoute];
        $elements = [];
        $elements[] = [
            'title' => $routeParameters['title'],
            'title_parameters' => isset($routeParameters['title_parameters']) ? $routeParameters['title_parameters'] : null,
            'path' => $searchedRoute,
            'path_parameters' => isset($routeParameters['route_parameters']) ? $routeParameters['route_parameters'] : null,
            'active' => $searchedRoute === $actualRoute,
        ];

        if (isset($routeParameters['parent'])) {
            $elements = array_merge($elements, $this->buildBreadcrumb($this->parameters[$searchedRoute]['parent'], $actualRoute));
        }

        return $elements;
    }
}
