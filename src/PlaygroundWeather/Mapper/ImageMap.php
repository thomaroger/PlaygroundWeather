<?php

namespace PlaygroundWeather\Mapper;

use Doctrine\ORM\AbstractQuery as Query;

class ImageMap
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
            $this->er = $this->em->getRepository('\PlaygroundWeather\Entity\ImageMap');
        }

        return $this->er;
    }

    public function queryAll($sortArray = array())
    {
        $query = $this->em->createQuery(
            'SELECT m FROM PlaygroundWeather\Entity\ImageMap m '
            .( ! empty($sortArray) ? 'ORDER BY m.'.key($sortArray).' '.current($sortArray) : '' )
        );
        return $query;
    }

    public function getDefault()
    {
        $query = $this->em->createQuery(
            'SELECT m FROM PlaygroundWeather\Entity\ImageMap AS m '
            .( ! empty($sortArray) ? 'ORDER BY m.'.key($sortArray).' '.current($sortArray) : '' )
        );
        $query->setMaxResults(1);
        return current($query->getResult(Query::HYDRATE_OBJECT));
    }

    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findBy($array = array(), $sortArray = array())
    {
        return $this->getEntityRepository()->findBy($array, $sortArray);
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

//     public function findLocationsId($imageMap)
//     {
//         $query = $this->em->createQuery(
//             'SELECT DISTINCT l.id FROM PlaygroundWeather\Entity\ImageMap AS m
//                 JOIN m.locations AS l
//                 WHERE m.id = :imageMapId '
//             .( ! empty($sortArray) ? 'ORDER BY m.'.key($sortArray).' '.current($sortArray) : '' )
//         );
//         $query->setParameter('imageMapId', $imageMap->getId());
//         return $query->getResult(Query::HYDRATE_SCALAR);
//     }
}