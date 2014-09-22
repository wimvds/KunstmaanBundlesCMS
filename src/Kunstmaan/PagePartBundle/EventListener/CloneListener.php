<?php

namespace Kunstmaan\PagePartBundle\EventListener;

use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Kunstmaan\AdminBundle\Event\DeepCloneAndSaveEvent;
use Kunstmaan\PagePartBundle\Service\PagePartService;
use Kunstmaan\PagePartBundle\Service\PageTemplateService;

/**
 * This event will make sure pageparts are being copied when deepClone is done on an entity implementing hasPagePartsInterface
 */
class CloneListener
{
    /**
     * @var PageTemplateService
     */
    private $pageTemplateService;

    /**
     * @var PagePartService
     */
    private $pagePartService;

    /**
     * @param PageTemplateService $pageTemplateService The page template service
     * @param PagePartService     $pagePartService     The page template service
     */
    public function __construct(
	PageTemplateService $pageTemplateService,
	PagePartService $pagePartService
    ) {
	$this->pageTemplateService = $pageTemplateService;
	$this->pagePartService     = $pagePartService;
    }

    /**
     * @param DeepCloneAndSaveEvent $event
     */
    public function postDeepCloneAndSave(DeepCloneAndSaveEvent $event)
    {
        $originalEntity = $event->getEntity();

        if ($originalEntity instanceof HasPagePartsInterface) {
            $clonedEntity = $event->getClonedEntity();

	    $contexts = $this->pagePartService->getPagePartContexts($originalEntity);
            foreach ($contexts as $context) {
		$this->pagePartService->copyPageParts($originalEntity, $clonedEntity, $context);
            }
        }

        if ($originalEntity instanceof HasPageTemplateInterface) {
	    $clonedEntity                 = $event->getClonedEntity();
	    $newPageTemplateConfiguration = clone $this->pageTemplateService->findOrCreateFor($originalEntity);
	    $newPageTemplateConfiguration
		->setId(null)
		->setPageId($clonedEntity->getId());

	    $this->pageTemplateService->saveConfiguration($newPageTemplateConfiguration);
        }
    }
}
