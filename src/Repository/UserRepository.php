<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;
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

    public $_doctrine;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
        $this->_doctrine = $registry;
    }

    public function findUser($email, $pwd)
    {
        $result = null;
        $conn = $this->_doctrine->getConnection();

        $sql = 'SELECT *
                FROM user u
                WHERE u.email = :email
                AND u.password = :pwd';

        $stmt = $conn->prepare($sql);
        //var_dump($conn);
        $stmt->execute([
            'email' => $email,
            'pwd' => $pwd,
        ]);

        $result = $stmt->fetch();
        return $result;
    }

    public function findUsers()
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *
                FROM user u';

        $stmt = $conn->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result;
    }

    public function findUserById($id)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *
                FROM user u
                WHERE u.id = :id';

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $result = $stmt->fetch();
        return $result;
    }

    public function checkEmailContactUnicity($email, $contact)
    {
        $result = null;$usersLength = 0;$isUnique = false;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(u.id) as nombre
                FROM user u
                WHERE u.contact = :contact
                OR u.email = :email';

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'contact' => $contact,
            'email' => $email,
        ]);

        $result = $stmt->fetch();
        $usersLength = (int) $result["nombre"];
        if($usersLength > 0) {
            $isUnique = false;
        }
        else if($usersLength == 0)
        {
            $isUnique = true;
        }
        return $isUnique;
    }
}
