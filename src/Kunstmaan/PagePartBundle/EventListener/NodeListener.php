<?php

namespace Kunstmaan\PagePartBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Kunstmaan\AdminBundle\Helper\FormWidgets\ListWidget;
use Kunstmaan\AdminBundle\Helper\FormWidgets\Tabs\Tab;
use Kunstmaan\NodeBundle\Event\AdaptFormEvent;
use Kunstmaan\PagePartBundle\Helper\FormWidgets\PagePartWidget;
use Kunstmaan\PagePartBundle\Helper\FormWidgets\PageTemplateWidget;
use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Kunstmaan\PagePartBundle\PagePartAdmin\PagePartAdminFactory;
use Kunstmaan\PagePartBundle\Service\PagePartService;
use Kunstmaan\PagePartBundle\Service\PageTemplateService;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * NodeListener
 */
class NodeListener
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var PagePartAdminFactory
     */
    private $pagePartAdminFactory;

    /**
     * @var PageTemplateService
     */
    private $templateService;

    /**
     * @var PagePartService
     */
    private $pagePartService;

    /**
     * @param FormFactoryInterface $formFactory          The form factory
     * @param PagePartAdminFactory $pagePartAdminFactory The page part admin factory
     * @param PageTemplateService  $templateService
     * @param PagePartService      $pagePartService
     */
    public function __construct(
	FormFactoryInterface $formFactory,
	PagePartAdminFactory $pagePartAdminFactory,
	PageTemplateService $templateService,
	PagePartService $pagePartService
    ) {
	$this->formFactory          = $formFactory;
        $this->pagePartAdminFactory = $pagePartAdminFactory;
	$this->templateService      = $templateService;
	$this->pagePartService      = $pagePartService;
    }

    /**
     * @param AdaptFormEvent $event
     */
    public function adaptForm(AdaptFormEvent $event)
    {
	$page    = $event->getPage();
        $tabPane = $event->getTabPane();

        if ($page instanceof HasPageTemplateInterface) {
	    $pageTemplateWidget = new PageTemplateWidget(
		$page,
		$event->getRequest(),
		$this->formFactory,
		$this->pagePartAdminFactory,
		$this->templateService,
		$this->pagePartService
	    );

            /* @var Tab $propertiesTab */
            $propertiesTab = $tabPane->getTabByTitle('Properties');
            if (!is_null($propertiesTab)) {
                $propertiesWidget = $propertiesTab->getWidget();
                $tabPane->removeTab($propertiesTab);
		$tabPane->addTab(new Tab('Content', new ListWidget(array($propertiesWidget, $pageTemplateWidget))), 0);
            } else {
		$tabPane->addTab(new Tab('Content', $pageTemplateWidget), 0);
            }
	} else {
	    if ($page instanceof HasPagePartsInterface) {
		$pagePartAdminConfigurators = $this->pagePartService->getPagePartAdminConfigurators($page);

		foreach ($pagePartAdminConfigurators as $index => $pagePartAdminConfiguration) {
		    $pagePartWidget = new PagePartWidget(
			$page,
			$event->getRequest(),
			$pagePartAdminConfiguration,
			$this->formFactory,
			$this->pagePartAdminFactory
		    );

		    if ($index == 0) {
			/* @var Tab $propertiesTab */
			$propertiesTab = $tabPane->getTabByTitle('Properties');

			if (!is_null($propertiesTab)) {
			    $propertiesWidget = $propertiesTab->getWidget();
			    $tabPane->removeTab($propertiesTab);
			    $tabPane->addTab(
				new Tab(
				    $pagePartAdminConfiguration->getName(),
				    new ListWidget(array($propertiesWidget,$pagePartWidget))
				),
				0
			    );

			    continue;
			}
                    }
		    $tabPane->addTab(
			new Tab($pagePartAdminConfiguration->getName(), $pagePartWidget),
			sizeof($tabPane->getTabs())
		    );
                }
            }
        }
    }
}
