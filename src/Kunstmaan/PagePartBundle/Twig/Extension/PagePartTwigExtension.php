<?php

namespace Kunstmaan\PagePartBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;
use Kunstmaan\PagePartBundle\Repository\PagePartRefRepository;
use Kunstmaan\PagePartBundle\Helper\PagePartInterface;
use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;
use Kunstmaan\PagePartBundle\Service\PagePartService;

/**
 * PagePartTwigExtension
 */
class PagePartTwigExtension extends \Twig_Extension
{
    /**
     * @var PagePartService
     */
    protected $pagePartService;

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @param PagePartService $pagePartService
     */
    public function __construct(PagePartService $pagePartService)
    {
	$this->pagePartService = $pagePartService;
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
	    'render_pageparts' => new \Twig_Function_Method($this, 'renderPageParts', array(
		    'needs_context' => true,
		    'is_safe'       => array('html')
		)),
	    'getpageparts'     => new \Twig_Function_Method($this, 'getPageParts'),
        );
    }

    /**
     * @param array                 $twigContext The twig context
     * @param HasPagePartsInterface $page        The page
     * @param string                $context     The page part context
     * @param array                 $parameters  Some extra parameters
     *
     * @return string
     */
    public function renderPageParts(
	array $twigContext,
	HasPagePartsInterface $page,
	$context = 'main',
	array $parameters = array()
    ) {
	$template       = $this->environment->loadTemplate(
	    'KunstmaanPagePartBundle:PagePartTwigExtension:widget.html.twig'
	);
	$pageparts      = $this->pagePartService->getPageParts($page, $context);
	$newTwigContext = array_merge(
	    $parameters,
	    array(
		'pageparts' => $pageparts
	    ),
	    $twigContext
	);

        return $template->render($newTwigContext);
    }

    /**
     * @param HasPagePartsInterface $page    The page
     * @param string                $context The pagepart context
     *
     * @return PagePartInterface[]
     */
    public function getPageParts(HasPagePartsInterface $page, $context = "main")
    {
	$pageparts = $this->pagePartService->getPageParts($page, $context);

        return $pageparts;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pageparts_twig_extension';
    }
}
