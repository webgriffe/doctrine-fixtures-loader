<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com> 
 */

namespace Webgriffe\DoctrineFixturesLoader;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;

class Loader
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Doctrine\Common\DataFixtures\ReferenceRepository
     */
    protected $referenceRepository;

    public function __construct(ObjectManager $objectManager, ReferenceRepository $referenceRepository)
    {
        $this->objectManager = $objectManager;
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * @param string $referenceName
     * @param bool $persist
     * @param callable $objectLoader
     * @return mixed
     */
    protected function load($referenceName, $persist, \Closure $objectLoader)
    {
        if ($this->referenceRepository->hasReference($referenceName)) {
            return $this->referenceRepository->getReference($referenceName);
        }

        $object = $objectLoader($this);

        if ($persist) {
            $this->objectManager->persist($object);
        }

        $this->referenceRepository->setReference($referenceName, $object);

        return $object;
    }
}
