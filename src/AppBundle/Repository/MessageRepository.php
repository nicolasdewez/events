<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Message;
use AppBundle\Workflow\MessageWorkflow as Workflow;
use Doctrine\ORM\EntityRepository;

/**
 * Class MessageRepository.
 */
class MessageRepository extends EntityRepository
{
    /**
     * @param array $ids
     *
     * @return Message[]
     */
    public function findByIds(array $ids)
    {
        return $this->createQueryBuilder('m')
            ->where(sprintf('m.id IN (%s)', implode(',', $ids)))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Message[]
     */
    public function findByStateToResend()
    {
        return $this->createQueryBuilder('m')
            ->where(
                sprintf('m.state IN (%s, %s, %s)',
                    '\''.Workflow::STATE_PARTIAL_SENT.'\'',
                    '\''.Workflow::STATE_ERROR.'\'',
                    '\''.Workflow::STATE_NO_APPLICATIONS.'\''
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $title
     * @param string $state
     *
     * @return int
     */
    public function countByTitleAndState($title, $state)
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m)')
            ->where(sprintf('m.state = \'%s\'', $state))
            ->andWhere(sprintf('m.title = \'%s\'', $title))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
