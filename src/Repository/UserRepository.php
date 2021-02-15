<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByUsernameOrEmail($username): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username OR u.email = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return User[] Returns an array of User objects
    */
    public function findOldsToRemove()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isActive = 0')
            ->andWhere('u.registerDate < :registerDate')
            ->setParameter('registerDate', \DateTime::createFromFormat('Y-m-d',date('Y-m-d', strtotime('-1 day'))))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
      * @return User[] Returns an array of User objects
    */
    public function listQuery(array $search = [])
    {
        $query = $this->createQueryBuilder('u');

        if(!empty($search['username'])){
            $query = $query->andWhere("u.username LIKE :username")
                ->setParameter('username', '%'.$search['username'].'%');
        }

        if(!empty($search['email'])){
            $query = $query->andWhere("u.email LIKE :email")
                ->setParameter('email', '%'.$search['email'].'%');
        }

        if(!empty($search['is_active'])){
            if($search['is_active'] == 'no'){
                $query = $query->andWhere("u.isActive = 0");
            }else{
                $query = $query->andWhere("u.isActive = 1");
            }
        }

        if(!empty($search['role'])){
            if($search['role'] == 'moderator'){
                $query = $query->where("u.roles LIKE :roles")
                ->setParameter('roles', '%"ROLE_MODERATOR"%');
            }elseif($search['role'] == 'admin'){
                $query = $query->where("u.roles LIKE :roles")
                ->setParameter('roles', '%"ROLE_ADMIN"%');
            }
        }

        if(!empty($search['date_from'])){
            $query = $query->andWhere("u.registerDate >= :date_from")
                ->setParameter('date_from', $search['date_from']);
        }

        if(!empty($search['date_to'])){
            $query = $query->andWhere("u.registerDate <= :date_to")
                ->setParameter('date_to', $search['date_to']);
        }

        if(!empty($search['register_ip'])){
            $query = $query->andWhere("u.registerIp LIKE :register_ip")
                ->setParameter('register_ip', '%'.$search['register_ip'].'%');
        }

        $query = $query->getQuery();

        return $query;
    }

}
