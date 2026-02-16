<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findVeryLastArticles($lim)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *
                FROM article a
                ORDER BY a.id DESC
                LIMIT 4';

        $stmt = $conn->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result;
    }

    public function findLastArticles($lim)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *
                FROM article a
                ORDER BY a.id DESC
                LIMIT 6';

        $stmt = $conn->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result;
    }

    public function findTotalArticles($lang)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(a.id) as nb
                FROM article a
                WHERE a.lang = :lang';

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'lang' => $lang
        ]);

        $result = $stmt->fetch();
        return \intval($result['nb']);
    }

    public function customFindArticles($lim, $off, $lang)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT *
                FROM article a
                WHERE a.lang = :lang
                ORDER BY a.id DESC
                LIMIT ".$lim." OFFSET ".$off;

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'lang' => $lang
        ]);

        $result = $stmt->fetchAll();
        return $result;
    }

    public function findAllArticles()
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *
                FROM article a';

        $stmt = $conn->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result;
    }


    public function findArticles($lang)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *
                FROM article a
                WHERE a.lang = :lang';

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'lang' => $lang
        ]);

        $result = $stmt->fetchAll();
        return $result;
    }

    public function findArticle($id)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *
                FROM article a
                WHERE a.id = :id';

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'id' => $id,
        ]);

        $result = $stmt->fetch();
        return $result;
    }

    public function customFindArticle($id)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT a.id, a.lang, a.title, concat(u.firstName, " ", u.lastName) as user, a.content, a.createTime, a.picture
                FROM article a
                JOIN user u ON u.id = a.user
                WHERE a.id = :id';

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'id' => $id,
        ]);

        $result = $stmt->fetch();
        return $result;
    }

    public function findArticleBySlug($lang, $slug)
    {
        $result = null;
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *
                FROM article a
                WHERE a.slug = :slug
                AND a.lang = :lang';

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'lang' => $lang,
            'slug' => $slug
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

    public function updateArticle($id, $lang, $title, $slug, $content)
    {
        $result = null; $data = [];
        $conn = $this->getEntityManager()->getConnection();

        $sql = "UPDATE article SET 
                lang="."'".$lang."'".", 
                title="."'".$title."'".", 
                slug="."'".$slug."'".", 
                content="."'".$content."'"." 
                WHERE id="."'".$id."'";
                

        $stmt = $conn->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetch();
        $data = [
            'result' => $result,
            'sql' => $sql,
        ];
        return $data; 
    }
}
