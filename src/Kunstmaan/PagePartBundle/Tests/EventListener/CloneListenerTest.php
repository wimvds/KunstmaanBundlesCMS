<?php

namespace Kunstmaan\PagePartBundle\Tests\EventListener;

use Kunstmaan\PagePartBundle\EventListener\CloneListener;
use Kunstmaan\PagePartBundle\Entity\PageTemplateConfiguration;
use Kunstmaan\AdminBundle\Event\DeepCloneAndSaveEvent;

/**
 * CloneListenerTest
 */
class CloneListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Kunstmaan\PagePartBundle\PagePartAdmin\AbstractPagePartAdminConfigurator
     */
    protected $configurator;

    /**
     * @var CloneListener
     */
    protected $object;

    protected $pageTemplateService;
    protected $pagePartService;

    /**
     * Sets up the fixture.
     *
     * @covers Kunstmaan\PagePartBundle\EventListener\CloneListener::__construct
     */
    protected function setUp()
    {
	$this->pagePartService = $this->getMockBuilder('Kunstmaan\PagePartBundle\Service\PagePartService')
            ->disableOriginalConstructor()
            ->getMock();
	$this->pagePartService
	    ->expects($this->any())
	    ->method('getPagePartContexts')
	    ->will($this->returnValue(array('main')));

	$this->pageTemplateService = $this->getMockBuilder('Kunstmaan\PagePartBundle\Service\PageTemplateService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurator = $this->getMock('Kunstmaan\PagePartBundle\PagePartAdmin\AbstractPagePartAdminConfigurator');
	$this->configurator
	    ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue('main'));

	$this->object = new CloneListener($this->pageTemplateService, $this->pagePartService);
    }

    /**
     * Tears down the fixture.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Kunstmaan\PagePartBundle\EventListener\CloneListener::postDeepCloneAndSave
     */
    public function testClonePagePart()
    {
        $entity = $this->getMockBuilder('Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface')
            ->setMethods(array('getId', 'getPagePartAdminConfigurations'))
            ->getMock();

        $entity->expects($this->any())
            ->method('getPagePartAdminConfigurations')
            ->will($this->returnValue(array($this->configurator)));

        $clone = clone $entity;

	$this->pagePartService
	    ->expects($this->once())
            ->method('copyPageParts')
	    ->with($entity, $clone, 'main');

        $event = new DeepCloneAndSaveEvent($entity, $clone);
        $this->object->postDeepCloneAndSave($event);
    }

    /**
     * @covers Kunstmaan\PagePartBundle\EventListener\CloneListener::postDeepCloneAndSave
     */
    public function testClonePageTemplate()
    {
        $entity = $this->getMockBuilder('Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface')
            ->setMethods(array('getId', 'getPageTemplates', 'getPagePartAdminConfigurations'))
            ->getMock();

	$entity
	    ->expects($this->any())
            ->method('getPagePartAdminConfigurations')
            ->will($this->returnValue(array($this->configurator)));

        $clone = clone $entity;

	$entity
	    ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

	$clone
	    ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));

	$this->pagePartService
	    ->expects($this->once())
            ->method('copyPageParts')
	    ->with($entity, $clone, 'main');

        $configuration = new PageTemplateConfiguration();
	$configuration
	    ->setId(1)
	    ->setPageId(1);

	$this->pageTemplateService
	    ->expects($this->once())
            ->method('findOrCreateFor')
            ->with($this->identicalTo($entity))
            ->will($this->returnValue($configuration));

        $newConfiguration = clone $configuration;
	$newConfiguration
	    ->setId(null)
	    ->setPageId($clone->getId());

	$this->pageTemplateService
	    ->expects($this->once())
	    ->method('saveConfiguration')
            ->with($newConfiguration);

        $event = new DeepCloneAndSaveEvent($entity, $clone);
        $this->object->postDeepCloneAndSave($event);
    }
}
