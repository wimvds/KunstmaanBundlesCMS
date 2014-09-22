<?php

namespace Kunstmaan\PagePartBundle\Service;

use Kunstmaan\PagePartBundle\Entity\PageTemplateConfiguration;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Kunstmaan\PagePartBundle\Helper\PageTemplateConfigurationReader;
use Kunstmaan\PagePartBundle\Repository\PageTemplateConfigurationRepository;
use Kunstmaan\UtilitiesBundle\Helper\ClassLookup;

class PageTemplateService
{
    /**
     * @var PageTemplateConfigurationReader
     */
    private $configurationReader;

    /**
     * @var PageTemplateConfigurationRepository
     */
    private $configurationRepository;

    /**
     * @param PageTemplateConfigurationReader     $configurationReader
     * @param PageTemplateConfigurationRepository $configurationRepository
     */
    public function __construct(
	PageTemplateConfigurationReader $configurationReader,
	PageTemplateConfigurationRepository $configurationRepository
    ) {
	$this->configurationReader     = $configurationReader;
	$this->configurationRepository = $configurationRepository;
    }

    /**
     * @param HasPageTemplateInterface $page
     *
     * @return PageTemplateConfiguration
     */
    public function findFor(HasPageTemplateInterface $page)
    {
	return $this->configurationRepository->findFor($page);
    }

    /**
     * @param HasPageTemplateInterface $page The page
     *
     * @return PageTemplateConfiguration
     */
    public function findOrCreateFor(HasPageTemplateInterface $page)
    {
	$pageTemplateConfiguration = $this->configurationRepository->findFor($page);

	if (is_null($pageTemplateConfiguration)) {
	    $pageTemplates       = $this->getPageTemplates($page);
	    $names               = array_keys($pageTemplates);
	    $defaultPageTemplate = $pageTemplates[$names[0]];

	    $pageTemplateConfiguration = new PageTemplateConfiguration();
	    $pageTemplateConfiguration
		->setPageId($page->getId())
		->setPageEntityName(ClassLookup::getClass($page))
		->setPageTemplate($defaultPageTemplate->getName());
	}

	return $pageTemplateConfiguration;
    }

    /**
     * @param HasPageTemplateInterface $page The page
     *
     * @return array
     */
    public function getPageTemplates(HasPageTemplateInterface $page)
    {
	return $this->configurationReader->getPageTemplates($page);
    }

    /**
     * @param PageTemplateConfiguration $configuration
     */
    public function saveConfiguration(PageTemplateConfiguration $configuration)
    {
	$this->configurationRepository->save($configuration);
    }
}
