<?php
/**
 * @license AVT
 */

namespace App\Repository\Logic;

use App\Entity\Logic\Logic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Logic|null find($id, $lockMode = null, $lockVersion = null)
 * @method Logic|null findOneBy(array $criteria, array $orderBy = null)
 * @method Logic[]    findAll()
 * @method Logic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogicRepository extends ServiceEntityRepository
{
    const SQL = "SET NOCOUNT ON;  exec sptdsls00101 1,1,0,0,1,:document_id,:document_id2,0,:date_from,:date_to,:contragent_descr,:emp_descr,'RUR',0,0,0,0,0,0, 0,329,0,0,0, '' ";

    const SQL_COMPANY = "SELECT descr, inn, CASE tcemp001.emp_id WHEN NULL THEN  t2.F_name + ' ' + t2.I_name + ' ' + t2.O_name ELSE tcemp001.F_name+ ' ' + tcemp001.I_name + ' ' + tcemp001.O_name  END  as manager,COALESCE (address_fact, address_official) as address from tccom010 left JOIN tcemp001 on tcemp001.emp_id = tccom010.emp_id left JOIN tcemp001 t2 on t2.emp_id = tccom010.emp_id_insert WHERE contragent_id = :contragent_id";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Logic::class);
    }

    /**
     * @return \mixed[][]
     * @return array
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getDocuments(
        \DateTimeInterface $dateTimeTo,
        int $documentId = null,
        int $documentId2 = null,
        string $contragentDescr = null,
        string $managerDescr = null
    ): array
    {
        $dateTimeFrom = clone $dateTimeTo;
        $dateTimeFrom->sub(new \DateInterval("P1M"));
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('contragent_descr', 'contragent_descr')
            ->addScalarResult('order_id', 'order_id', 'integer')
            ->addScalarResult('sell_id', 'sell_id', 'integer')
            ->addScalarResult('_date', '_date')
            ->addScalarResult('fio', 'fio')
            ->addScalarResult('total_cur', 'total_cur', 'float')
            ->addScalarResult('status_descr', 'status_descr')
            ->addScalarResult('contragent_id', 'contragent_id', 'integer');
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createNativeQuery(self::SQL, $rsm);
        $query
            ->setParameter('date_from', $dateTimeFrom->format('d.m.Y'))
            ->setParameter('date_to', $dateTimeTo->format('d.m.Y'))
            ->setParameter('document_id', $documentId??0)
            ->setParameter('document_id2', $documentId2??0)
            ->setParameter('contragent_descr', $contragentDescr??'%%')
            ->setParameter('emp_descr', $managerDescr??'%%')
        ;
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param int $contragentId
     * @return int|mixed|string
     */
    public function getContragent(int $contragentId)
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('descr', 'contragent_descr')
            ->addScalarResult('inn', 'inn')
            ->addScalarResult('manager', 'manager')
            ->addScalarResult('address', 'address');
        $query = $this->getEntityManager()->createNativeQuery(self::SQL_COMPANY, $rsm);
        $query->setParameter('contragent_id', $contragentId);
        $result = $query->getSingleResult();

        return $result;
    }


}
