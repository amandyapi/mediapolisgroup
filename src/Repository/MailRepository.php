<?php

namespace App\Repository;

use App\Entity\Mail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mail|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mail|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mail[]    findAll()
 * @method Mail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mail::class);
    }

    public function findMails()
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT m.id, m.senderFullName as user, m.senderContact as contact, m.senderMail as userMail, m.title, m.createTime 
                FROM mail m
                ORDER BY m.createTime DESC';

        $stmt = $conn->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result;
    }

    public function findMail($id)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *
                FROM mail m
                WHERE m.id = :id';

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'id' => $id,
        ]);

        $result = $stmt->fetch();
        return $result;
    }
    
    public function findArticleByLang($lang)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *
                FROM article a
                WHERE a.lang = :lang';

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'lang' => $lang,
        ]);

        $result = $stmt->fetch();
        return $result;
    }
}
