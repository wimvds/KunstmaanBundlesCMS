<?php

namespace Kunstmaan\PagePartBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Kunstmaan\PagePartBundle\Entity\PageTemplateConfiguration;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Kunstmaan\PagePartBundle\PageTemplate\PageTemplate;
use Kunstmaan\PagePartBundle\Service\PageTemplateService;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * PageTemplateTwigExtension
 */
class PageTemplateTwigExtension extends \Twig_Extension
{
    /**
     * @var PageTemplateService
     */
    protected $templateService;

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @param PageTemplateService    $templateService
     */
    public function __construct(PageTemplateService $templateService)
    {
	$this->templateService = $templateService;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
	    'render_pagetemplate' => new \Twig_Function_Method($this, 'renderPageTemplate', array(
		    'needs_context' => true,
		    'is_safe'       => array('html')
		)),
	    'get_page_template'   => new \Twig_Function_Method($this, 'getPageTemplate')
        );
    }

    /**
     * @param array                    $twigContext The twig context
     * @param HasPageTemplateInterface $page        The page
     *
     * @return string
     */
    public function renderPageTemplate(array $twigContext, HasPageTemplateInterface $page)
    {
        /* @var $pageTemplate PageTemplate */
	$pageTemplate = $this->getPageTemplate($page);
	$template     = $this->environment->loadTemplate($pageTemplate->getTemplate());

        return $template->render($twigContext);
    }

    /**
     * @param HasPageTemplateInterface $page The page
     *
     * @return PageTemplate
     */
    public function getPageTemplate(HasPageTemplateInterface $page)
    {
	$pageTemplates             = $this->templateService->getPageTemplates($page);
	$pageTemplateConfiguration = $this->getPageTemplateConfiguration($page);

	return $pageTemplates[$pageTemplateConfiguration->getPageTemplate()];
    }

    /**
     * @param HasPageTemplateInterface $page The page
     *
     * @return PageTemplateConfiguration
     */
    private function getPageTemplateConfiguration(HasPageTemplateInterface $page)
    {
	$pageTemplateConfiguration = $this->templateService->findOrCreateFor($page);

	return $pageTemplateConfiguration;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pagetemplate_twig_extension';
    }
}
