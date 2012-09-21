<?php
namespace Heartsentwined\Geoname\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Feature
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Feature extends EntityRepository
{
    /**
     * findByGeonameCode
     *
     * @param string $code [feature class].[feature code]
     *      e.g. A.ADM1
     * @return Feature|null
     */
    public function findByGeonameCode($code)
    {
        list($parentCode, $featureCode) = explode('.', $code);

        $dqb = $this->_em->createQueryBuilder();
        $dqb->select(array('f'))
            ->from('Geoname\Entity\Feature', 'f')
            ->join('f.parent', 'p')
            ->where($dqb->expr()->andX(
                $dqb->expr()->eq('p.code', ':parentCode'),
                $dqb->expr()->eq('f.code', ':featureCode')
            ))
            ->setParameters(array(
                'parentCode'    => $parentCode,
                'featureCode'   => $featureCode,
            ));

        if ($features = $dqb->getQuery()->getResult()) {
            return current($features);
        } else {
            return null;
        }
    }
}
