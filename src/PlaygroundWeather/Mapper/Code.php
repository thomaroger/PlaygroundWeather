<?php

namespace PlaygroundWeather\Mapper;

class Code
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

     /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $er;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em      = $em;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('\PlaygroundWeather\Entity\Code');
        }

        return $this->er;
    }

    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findBy($array = array(), $sortArray = array())
    {
        return $this->getEntityRepository()->findBy($array, $sortArray);
    }

    public function findOneBy($array = array(), $sortArray = array())
    {
        return $this->getEntityRepository()->findOneBy($array, $sortArray);
    }

    public function insert($entity)
    {
        return $this->persist($entity);
    }

    public function update($entity)
    {
        return $this->persist($entity);
    }

    protected function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function findDefaultByCode($code)
    {
        return $this->getEntityRepository()->findOneBy(
            array(
                'value' => $code,
                'isDefault' => 1,
            ));
    }

    public function findLastAssociatedCode($codeObj)
    {
        while ($codeObj->getAssociatedCode()) {
            $codeObj = $codeObj->getAssociatedCode();
        }
        return $codeObj;
    }

    public function assertNoOther($codeObj)
    {
        $results = $this->getEntityRepository()->findBy(
            array(
                'value' => $codeObj->getValue(),
                'isDefault' => $codeObj->getIsDefault(),
                'description' => $codeObj->getDescription()
            ));
        return (empty($results)) ? true : false;
    }
}