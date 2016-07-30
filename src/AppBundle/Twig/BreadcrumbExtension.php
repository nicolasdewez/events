<?php

namespace AppBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BreadcrumbExtension.
 */
class BreadcrumbExtension extends \Twig_Extension
{
    /** @var Router */
    private $router;

    /** @var array */
    private $attributes;

    /**
     * @param Router       $router
     * @param RequestStack $request
     */
    public function __construct(Router $router, RequestStack $request)
    {
        $this->router = $router;
        $this->attributes = [];
        if (null !== $request->getMasterRequest()) {
            $this->attributes = $request->getMasterRequest()->attributes->all();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('pathForBreadcrumb', array($this, 'getPath')),
            new \Twig_SimpleFunction('titleForBreadcrumb', array($this, 'getTitle')),
        );
    }

    /**
     * @param string $routePath
     * @param array  $routeParameters
     *
     * @return string
     */
    public function getPath($routePath, $routeParameters)
    {
        $parameters = [];
        foreach ($routeParameters as $key => $routeParameter) {
            $splitParameters = explode('.', $routeParameter);
            if (!isset($this->attributes[$splitParameters[0]])) {
                return '#';
            }

            $element = $this->attributes[$splitParameters[0]];
            $method = 'get'.ucfirst($splitParameters[1]);
            $value = $element->$method();
            $parameters[$key] = $value;
        }

        return $this->router->generate($routePath, $parameters, Router::RELATIVE_PATH);
    }

    /**
     * @param string $title
     * @param array  $titleParameters
     *
     * @return string
     */
    public function getTitle($title, $titleParameters)
    {
        foreach ($titleParameters as $key => $titleParameter) {
            $splitParameters = explode('.', $titleParameter);
            if (!isset($this->attributes[$splitParameters[0]])) {
                return '';
            }

            $element = $this->attributes[$splitParameters[0]];
            $method = 'get'.ucfirst($splitParameters[1]);
            $value = $element->$method();

            $title = str_replace('{'.$key.'}', $value, $title);
        }

        return $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app.breadcrumb';
    }
}
