<?php

namespace Kunstmaan\PagePartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\PagePartBundle\Form\TocPagePartAdminType;

/**
 * TocPagePart
 *
 * @ORM\Entity
 * @ORM\Table(name="kuma_toc_page_parts")
 */
class TocPagePart extends AbstractPagePart
{
    /**
     * @var int
     */
    private $level;

    /**
     * @var string
     */
    private $context;

    /**
     * @param string $context
     *
     * @return TocPagePart
     */
    public function setContext($context)
    {
	$this->context = $context;

	return $this;
    }

    /**
     * @return string
     */
    public function getContext()
    {
	return $this->context;
    }

    /**
     * @param int $level
     *
     * @return TocPagePart
     */
    public function setLevel($level)
    {
	$this->level = $level;

	return $this;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
	return $this->level;
    }

    /**
     * @return string
     */
    public function __toString()
    {
	return 'TocPagePart';
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
	return 'KunstmaanPagePartBundle:TocPagePart:view.html.twig';
    }

    /**
     * @return TocPagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return new TocPagePartAdminType();
    }
}
