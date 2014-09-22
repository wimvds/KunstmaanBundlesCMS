<?php

namespace Kunstmaan\PagePartBundle\PagePartAdmin;

use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;
use Kunstmaan\PagePartBundle\Service\PagePartService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * PagePartAdminFactory
 */
class PagePartAdminFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var PagePartService
     */
    private $pagePartService;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param PagePartService    $pagePartService
     */
    public function __construct(ContainerInterface $container, PagePartService $pagePartService)
    {
        $this->container = $container;
	$this->pagePartService = $pagePartService;
    }

    /**
     * @param AbstractPagePartAdminConfigurator $configurator The configurator
     * @param HasPagePartsInterface             $page         The page
     * @param string|null                       $context      The context
     *
     * @return PagePartAdmin
     */
    public function createList(
	AbstractPagePartAdminConfigurator $configurator,
	HasPagePartsInterface $page,
	$context = null
    ) {
	return new PagePartAdmin($configurator, $page, $context, $this->pagePartService, $this->container);
    }
}
