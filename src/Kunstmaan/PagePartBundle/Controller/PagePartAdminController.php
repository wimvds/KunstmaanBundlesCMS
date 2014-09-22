<?php

namespace Kunstmaan\PagePartBundle\Controller;

use Kunstmaan\PagePartBundle\PagePartAdmin\PagePartAdmin;
use Kunstmaan\PagePartBundle\PagePartAdmin\PagePartAdminFactory;
use Kunstmaan\PagePartBundle\Service\PagePartService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the page part administration
 */
class PagePartAdminController extends Controller
{
    /**
     * @Route("/newPagePart", name="KunstmaanPagePartBundle_admin_newpagepart")
     * @Template("KunstmaanPagePartBundle:PagePartAdminTwigExtension:pagepart.html.twig")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function newPagePartAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $pageId        = $request->get('pageid');
        $pageClassName = $request->get('pageclassname');
        $context       = $request->get('context');
        $pagePartClass = $request->get('type');

        $page = $em->getRepository($pageClassName)->findOneById($pageId);

	/**
	 * @var PagePartService $pagePartService
	 */
	$pagePartService = $this->get('kunstmaan_pagetemplate.pagepart_service');
	$configurator    = $pagePartService->getPagePartAdminConfigurator($page, $context);

	if (is_null($configurator)) {
            throw new \RuntimeException(sprintf('No page part admin configurator found for context "%s".', $context));
        }

	/**
	 * @var PagePartAdminFactory $pagePartAdminFactory
	 */
	$pagePartAdminFactory = $this->get('kunstmaan_pagepartadmin.factory');

	/**
	 * @var PagePartAdmin $pagePartAdmin
	 */
	$pagePartAdmin = $pagePartAdminFactory->createList($configurator, $page, $context);
	$pagePart      = new $pagePartClass();
	$formFactory   = $this->get('form.factory');
	$formBuilder   = $formFactory->createBuilder('form');
        $pagePartAdmin->adaptForm($formBuilder);
        $id = 'newpp_' . time();

        $data                         = $formBuilder->getData();
        $data['pagepartadmin_' . $id] = $pagePart;
        $adminType                    = $pagePart->getDefaultAdminType();
        if (!is_object($adminType) && is_string($adminType)) {
	    $adminType = $this->get($adminType);
        }
	$formBuilder
	    ->add('pagepartadmin_' . $id, $adminType)
	    ->setData($data);
        $form     = $formBuilder->getForm();
	$formView = $form->createView();

        return array(
            'id'            => $id,
	    'form'          => $formView,
            'pagepart'      => $pagePart,
            'pagepartadmin' => $pagePartAdmin,
            'editmode'      => true
        );
    }
}
