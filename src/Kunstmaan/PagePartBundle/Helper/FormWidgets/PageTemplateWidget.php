<?php

namespace Kunstmaan\PagePartBundle\Helper\FormWidgets;

use Doctrine\ORM\EntityManager;

use Kunstmaan\PagePartBundle\Service\PagePartService;
use Kunstmaan\PagePartBundle\Service\PageTemplateService;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

use Kunstmaan\NodeBundle\Entity\PageInterface;
use Kunstmaan\PagePartBundle\PagePartAdmin\PagePartAdmin;
use Kunstmaan\PagePartBundle\Entity\PageTemplateConfiguration;
use Kunstmaan\PagePartBundle\PagePartAdmin\PagePartAdminFactory;
use Kunstmaan\PagePartBundle\PagePartAdmin\AbstractPagePartAdminConfigurator;
use Kunstmaan\AdminBundle\Helper\FormWidgets\FormWidget;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Kunstmaan\PagePartBundle\PageTemplate\PageTemplate;

/**
 * PageTemplateWidget
 */
class PageTemplateWidget extends FormWidget
{

    /**
     * @var AbstractPagePartAdminConfigurator
     */
    protected $pagePartAdminConfigurator;

    /**
     * @var PagePartAdminFactory
     */
    protected $pagePartAdminFactory;

    /**
     * @var PagePartAdmin
     */
    protected $pagePartAdmin;

    /**
     * @var PageInterface
     */
    protected $page;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $widgets = array();

    /**
     * @var PageTemplate[]
     */
    protected $pageTemplates = array();

    /**
     * @var AbstractPagePartAdminConfigurator[]
     */
    protected $pagePartAdminConfigurations = array();

    /**
     * @var PageTemplateConfiguration
     */
    protected $pageTemplateConfiguration;

    /**
     * @var PageTemplateService
     */
    protected $templateService;

    /**
     * @var PagePartService
     */
    protected $pagePartService;

    /**
     * @param HasPageTemplateInterface $page                 The page
     * @param Request                  $request              The request
     * @param FormFactoryInterface     $formFactory          The form factory
     * @param PagePartAdminFactory     $pagePartAdminFactory The page part admin factory
     * @param PageTemplateService      $templateService      The page template service
     * @param PagePartService          $pagePartService      The page part service
     */
    public function __construct(
	HasPageTemplateInterface $page,
	Request $request,
	FormFactoryInterface $formFactory,
	PagePartAdminFactory $pagePartAdminFactory,
	PageTemplateService $templateService,
	PagePartService $pagePartService
    ) {
        parent::__construct();

	$this->page                        = $page;
	$this->request                     = $request;
	$this->templateService             = $templateService;
	$this->pagePartService             = $pagePartService;
	$this->pageTemplates               = $this->templateService->getPageTemplates($page);
	$this->pagePartAdminConfigurations = $this->pagePartService->getPagePartAdminConfigurators($page);
	$this->pageTemplateConfiguration   = $this->templateService->findOrCreateFor($page);

        foreach ($this->getPageTemplate()->getRows() as $row) {
            foreach ($row->getRegions() as $region) {
                $pagePartAdminConfiguration = null;
		foreach ($this->pagePartAdminConfigurations as $configuration) {
		    if ($configuration->getContext() == $region->getName()) {
			$pagePartAdminConfiguration = $configuration;
                    }
                }
                if ($pagePartAdminConfiguration != null) {
		    $pagePartWidget                    = new PagePartWidget($page, $this->request, $pagePartAdminConfiguration, $formFactory, $pagePartAdminFactory);
                    $this->widgets[$region->getName()] = $pagePartWidget;
                }
            }
        }
    }

    /**
     * @return PageTemplate
     */
    public function getPageTemplate()
    {
        return $this->pageTemplates[$this->pageTemplateConfiguration->getPageTemplate()];
    }

    /**
     * @return PageTemplate
     */
    public function getPageTemplates()
    {
        return $this->pageTemplates;
    }

    /**
     * @return PageInterface
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param FormBuilderInterface $builder The form builder
     */
    public function buildForm(FormBuilderInterface $builder)
    {
        foreach ($this->widgets as $widget) {
            $widget->buildForm($builder);
        }
    }

    /**
     * @param Request $request
     */
    public function bindRequest(Request $request)
    {
	$pageTemplate = $request->get('pagetemplate_template');
	$this->pageTemplateConfiguration->setPageTemplate($pageTemplate);
        foreach ($this->widgets as $widget) {
            $widget->bindRequest($request);
        }
    }

    /**
     * @param EntityManager $em The entity manager
     */
    public function persist(EntityManager $em)
    {
        $em->persist($this->pageTemplateConfiguration);
        foreach ($this->widgets as $widget) {
            $widget->persist($em);
        }
    }

    /**
     * @param FormView $formView
     *
     * @return array
     */
    public function getFormErrors(FormView $formView)
    {
        $errors = array();

        foreach ($this->widgets as $widget) {
            $errors = array_merge($errors, $widget->getFormErrors($formView));
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return 'KunstmaanPagePartBundle:FormWidgets\PageTemplateWidget:widget.html.twig';
    }

    /**
     * @param string $name
     *
     * @return PagePartAdmin
     */
    public function getFormWidget($name)
    {
        if (array_key_exists($name, $this->widgets)) {
            return $this->widgets[$name];
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getExtraParams(Request $request)
    {
        $params = array();

        /*$editPagePart = $request->get('edit');
        if (isset($editPagePart)) {
            $params['editpagepart'] = $editPagePart;
        }*/

        return $params;
    }
}
