<?php

namespace Kunstmaan\PagePartBundle\Service;

use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;
use Kunstmaan\PagePartBundle\Helper\PagePartConfigurationReader;
use Kunstmaan\PagePartBundle\Helper\PagePartInterface;
use Kunstmaan\PagePartBundle\Repository\PagePartRefRepository;

class PagePartService
{
    /**
     * @var PagePartConfigurationReader
     */
    private $configurationReader;

    /**
     * @var PagePartRefRepository
     */
    private $refRepository;

    public function __construct(PagePartConfigurationReader $configurationReader, PagePartRefRepository $refRepository)
    {
	$this->configurationReader = $configurationReader;
	$this->refRepository       = $refRepository;
    }

    /**
     * @param HasPagePartsInterface $page
     *
     * @return AbstractPagePartAdminConfigurator[]
     */
    public function getPagePartAdminConfigurators(HasPagePartsInterface $page)
    {
	return $this->configurationReader->getPagePartAdminConfigurators($page);
    }

    /**
     * @param HasPagePartsInterface $page
     * @param string                $context
     *
     * @return AbstractPagePartAdminConfigurator|null
     */
    public function getPagePartAdminConfigurator(HasPagePartsInterface $page, $context = 'main')
    {
	$pagePartAdminConfigurators = $this->getPagePartAdminConfigurators($page);

	$pagePartAdminConfigurator = null;
	foreach ($pagePartAdminConfigurators as $configurator) {
	    if ($context == $configurator->getContext()) {
		$pagePartAdminConfigurator = $configurator;
		break;
	    }
	}

	return $pagePartAdminConfigurator;
    }

    /**
     * @param HasPagePartsInterface $page
     *
     * @return string[]
     */
    public function getPagePartContexts(HasPagePartsInterface $page)
    {
	return $this->configurationReader->getPagePartContexts($page);
    }

    /**
     * @param HasPagePartsInterface $fromPage
     * @param HasPagePartsInterface $toPage
     * @param string                $context
     */
    public function copyPageParts(HasPagePartsInterface $fromPage, HasPagePartsInterface $toPage, $context = 'main')
    {
	$this->refRepository->copyPageParts($fromPage, $toPage, $context);
    }

    /**
     * @param HasPagePartsInterface $page    The page
     * @param string                $context The pagePart context
     *
     * @return PagePartInterface[]
     */
    public function getPageParts(HasPagePartsInterface $page, $context = 'main')
    {
	return $this->refRepository->getPageParts($page, $context);
    }

    /**
     * @param HasPagePartsInterface $page    The page
     * @param string                $context The string
     *
     * @return PagePartRef[]
     */
    public function getPagePartRefs(HasPagePartsInterface $page, $context = 'main')
    {
	return $this->refRepository->getPagePartRefs($page, $context);
    }

    /**
     * @param string $pagePartClass
     * @param array  $ids
     *
     * @return PagePartInterface[]
     */
    public function getPagePartsByClassAndIds($pagePartClass, array $ids)
    {
	return $this->refRepository->getPagePartsByClassAndIds($pagePartClass, $ids);
    }

    /**
     * @param mixed $object
     */
    public function persist($object)
    {
	$this->refRepository->persist($object);
    }

    /**
     * @param mixed $object
     */
    public function remove($object)
    {
	$this->refRepository->remove($object);
    }

    public function flush()
    {
	$this->refRepository->flush();
    }

    /**
     * @param HasPagePartsInterface $page               The page
     * @param PagePartInterface     $pagePart           The pagePart
     * @param integer               $sequencenumber     The sequence number
     * @param string                $context            The context
     * @param bool                  $pushOtherPageParts Push other pageparts (sequence + 1)
     *
     * @return \Kunstmaan\PagePartBundle\Entity\PagePartRef
     */
    public function addPagePart(
	HasPagePartsInterface $page,
	PagePartInterface $pagePart,
	$sequencenumber,
	$context = 'main',
	$pushOtherPageParts = true
    ) {
	return $this->refRepository->addPagePart($page, $pagePart, $sequencenumber, $context, $pushOtherPageParts);
    }

    /**
     * @param HasPagePartsInterface $page              The page
     * @param string                $pagePartClassName The classname of the pagePart
     * @param string                $context           The context
     *
     * @return mixed
     */
    public function countPagePartsOfType(HasPagePartsInterface $page, $pagePartClassName, $context = 'main')
    {
	return $this->refRepository->countPagePartsOfType($page, $pagePartClassName, $context);
    }
}