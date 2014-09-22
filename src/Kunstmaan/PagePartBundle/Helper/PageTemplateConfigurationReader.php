<?php

namespace Kunstmaan\PagePartBundle\Helper;

use Kunstmaan\PagePartBundle\PageTemplate\PageTemplate;
use Symfony\Component\Yaml\Yaml;
use Kunstmaan\PagePartBundle\PageTemplate\Row;
use Kunstmaan\PagePartBundle\PageTemplate\Region;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * PageTemplateConfigurationReader
 */
class PageTemplateConfigurationReader
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @var array
     */
    private $pageTemplates;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
	$this->pageTemplates = array();
    }

    /**
     * This will read the $name file and parse it to the PageTemplate
     *
     * @param string $name
     *
     * @throws \Exception
     * @return PageTemplate
     */
    public function parse($name)
    {
        if (false === $pos = strpos($name, ':')) {
	    throw new \Exception(sprintf(
		'Malformed namespaced configuration name "%s" (expecting "namespace:pagename.yml").',
		$name
	    ));
        }
        $namespace = substr($name, 0, $pos);
	$name      = substr($name, $pos + 1);
	$path      = $this->kernel->locateResource('@' . $namespace . '/Resources/config/pagetemplates/' . $name . '.yml');
	$rawData   = Yaml::parse($path);
	$rows      = array();
	foreach ($rawData['rows'] as $rawRow) {
            $regions = array();
	    foreach ($rawRow['regions'] as $rawRegion) {
		$regions[] = new Region($rawRegion['name'], $rawRegion['span']);
            }
            $rows[] = new Row($regions);
        }

	$result = new PageTemplate();
	$result
	    ->setName($rawData['name'])
	    ->setRows($rows)
	    ->setTemplate($rawData['template']);

        return $result;
    }

    /**
     * @param HasPageTemplateInterface $page
     *
     * @throws \Exception
     * @return array(string => PageTemplate)
     */
    public function getPageTemplates(HasPageTemplateInterface $page)
    {
	if (!isset($this->pageTemplates[get_class($page)])) {
	    $this->parsePageTemplatesFor($page);
	}

	return $this->pageTemplates[get_class($page)];
    }

    /**
     * @param HasPageTemplateInterface $page
     *
     * @throws \Exception
     */
    private function parsePageTemplatesFor(HasPageTemplateInterface $page)
    {
        $pageTemplates = array();
        foreach ($page->getPageTemplates() as $pageTemplate) {
            $pt = null;
            if (is_string($pageTemplate)) {
                $pt = $this->parse($pageTemplate);
            } else {
		if (is_object($pageTemplate) && $pageTemplate instanceof PageTemplate) {
		    $pt = $pageTemplate;
		} else {
		    throw new \Exception("don't know how to handle the pageTemplate " . get_class($pageTemplate));
		}
            }
            $pageTemplates[$pt->getName()] = $pt;
        }
	$this->pageTemplates[get_class($page)] = $pageTemplates;
    }
}
