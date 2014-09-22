<?php

namespace Kunstmaan\PagePartBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Kunstmaan\AdminBundle\Entity\DeepCloneInterface;
use Kunstmaan\UtilitiesBundle\Helper\ClassLookup;
use Kunstmaan\PagePartBundle\Helper\PagePartInterface;
use Kunstmaan\PagePartBundle\Entity\PagePartRef;
use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;

/**
 * PagePartRefRepository
 */
class PagePartRefRepository extends EntityRepository
{

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
        if ($pushOtherPageParts) {
	    $pagePartRefs = $this->getPagePartRefs($page);
	    foreach ($pagePartRefs as $pagePartRef) {
		if ($pagePartRef->getSequencenumber() >= $sequencenumber) {
		    $pagePartRef->setSequencenumber($pagePartRef->getSequencenumber() + 1);
		    $this->getEntityManager()->persist($pagePartRef);
                }
            }
        }

	$pageClass     = ClassLookup::getClass($page);
	$pagePartClass = ClassLookup::getClass($pagePart);

	$pagePartRef = new \Kunstmaan\PagePartBundle\Entity\PagePartRef();
	$pagePartRef
	    ->setContext($context)
	    ->setPageEntityname($pageClass)
	    ->setPageId($page->getId())
	    ->setPagePartEntityname($pagePartClass)
	    ->setPagePartId($pagePart->getId())
	    ->setSequencenumber($sequencenumber);

	$this->getEntityManager()->persist($pagePartRef);
        $this->getEntityManager()->flush();

	return $pagePartRef;
    }

    /**
     * @param HasPagePartsInterface $page    The page
     * @param string                $context The string
     *
     * @return PagePartRef[]
     */
    public function getPagePartRefs(HasPagePartsInterface $page, $context = 'main')
    {
	return $this->findBy(
	    array(
		'pageId'         => $page->getId(),
		'pageEntityname' => ClassLookup::getClass($page),
		'context'        => $context
	    ),
	    array('sequencenumber' => 'ASC')
	);
    }

    /**
     * @param HasPagePartsInterface $page    The page
     * @param string                $context The pagePart context
     *
     * @return PagePartInterface[]
     */
    public function getPageParts(HasPagePartsInterface $page, $context = 'main')
    {
	$pagePartRefs = $this->getPagePartRefs($page, $context);

	// Group page part refs per type and remember the sorting order
	$types   = $order = array();
        $counter = 1;
	foreach ($pagePartRefs as $pagePartRef) {
	    $entityName                                         = $pagePartRef->getPagePartEntityname();
	    $types[$entityName][]                               = $pagePartRef->getPagePartId();
	    $order[$entityName . $pagePartRef->getPagePartId()] = $counter;
            $counter++;
        }

	// Fetch all the pageparts (only one query per pagePart type)
        $pageparts = array();
	foreach ($types as $className => $ids) {
	    $result    = $this->getEntityManager()->getRepository($className)->findBy(array('id' => $ids));
            $pageparts = array_merge($pageparts, $result);
        }

        // Order the pageparts
	usort(
	    $pageparts,
	    function ($a, $b) use ($order) {
		$aPosition = $order[get_class($a) . $a->getId()];
		$bPosition = $order[get_class($b) . $b->getId()];

		if ($aPosition < $bPosition) {
		    return -1;
		} elseif ($aPosition > $bPosition) {
		    return 1;
		}

		return 0;
            }
	);

        return $pageparts;
    }

    /**
     * @param HasPagePartsInterface $fromPage The page from where you copy the pageparts
     * @param HasPagePartsInterface $toPage   The page to where you want to copy the pageparts
     * @param string                $context  The pagePart context
     */
    public function copyPageParts(HasPagePartsInterface $fromPage, HasPagePartsInterface $toPage, $context = 'main')
    {
	$em             = $this->getEntityManager();
	$fromPageParts  = $this->getPageParts($fromPage, $context);
        $sequenceNumber = 1;
        foreach ($fromPageParts as $fromPagePart) {
            $toPagePart = clone $fromPagePart;
            $toPagePart->setId(null);
            if ($toPagePart instanceof DeepCloneInterface) {
                $toPagePart->deepClone();
            }
            $em->persist($toPagePart);
	    $em->flush();
            $this->addPagePart($toPage, $toPagePart, $sequenceNumber, $context, false);
            $sequenceNumber++;
        }
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
	$pageClassName = ClassLookup::getClass($page);

	$qb    = $this->createQueryBuilder('pp');
	$query = $qb->select('COUNT(pp.id)')
	    ->where('pp.pageEntityname = :pageEntityname')
	    ->andWhere('pp.pageId = :pageId')
	    ->andWhere('pp.pagePartEntityname = :pagePartEntityname')
	    ->andWhere('pp.context = :context')
	    ->setParameter('pageEntityname', $pageClassName)
	    ->setParameter('pageId', $page->getId())
	    ->setParameter('pagePartEntityname', $pagePartClassName)
	    ->setParameter('context', $context)
	    ->getQuery();

	return $query->getSingleScalarResult();
    }

    /**
     * Test if entity has pageparts for the specified context
     *
     * @param HasPagePartsInterface $page    The page
     * @param string                $context The context
     *
     * @return bool
     */
    public function hasPageParts(HasPagePartsInterface $page, $context = 'main')
    {
	$pageClassName = ClassLookup::getClass($page);

	$qb    = $this->createQueryBuilder('pp');
	$query = $qb->select('COUNT(pp.id)')
	    ->where('pp.pageEntityname = :pageEntityname')
	    ->andWhere('pp.pageId = :pageId')
	    ->andWhere('pp.context = :context')
	    ->setParameter('pageEntityname', $pageClassName)
	    ->setParameter('pageId', $page->getId())
	    ->setParameter('context', $context)
	    ->getQuery();

	return $query->getSingleScalarResult() != 0;

    }

    /**
     * @param string $pagePartClass
     * @param array  $ids
     *
     * @return PagePartInterface[]
     */
    public function getPagePartsByClassAndIds($pagePartClass, array $ids)
    {
	return $this->getEntityManager()->getRepository($pagePartClass)->findBy(array('id' => $ids));
    }

    /**
     * @param mixed $object
     */
    public function persist($object)
    {
	$this->getEntityManager()->persist($object);
    }

    /**
     * @param mixed $object
     */
    public function remove($object)
    {
	$this->getEntityManager()->remove($object);
    }

    public function flush()
    {
	$this->getEntityManager()->flush();
    }
}
