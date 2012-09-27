<?php
namespace Heartsentwined\Geoname\Repository;

use Doctrine\ORM\EntityRepository;
use Heartsentwined\ArgValidator\ArgValidator;

/**
 * Place
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Place extends EntityRepository
{
    /**
     * findPlace
     *
     * @param array $criteria (all optional)
     *  'countryCode'   => (e.g.) 'DE'
     *  'admin1Code'    => (e.g.) '03'
     *  'admin2Code'    => (e.g.) '00'
     *  'admin3Code'    => (same)
     *  'admin4Code'    => (same)
     *  'featureClass'  => (e.g.) 'A'
     *  'featureCode'   => (e.g.) 'ADM1'
     *  @param int|null $limit = null
     * @return array of Place
     */
    public function findPlace(array $criteria = array(), $limit = null)
    {
        ArgValidator::arrayAssert($criteria, array(
            'countryCode'   => array('string', 'notSet'),
            'admin1Code'    => array('string', 'notSet'),
            'admin2Code'    => array('string', 'notSet'),
            'admin3Code'    => array('string', 'notSet'),
            'admin4Code'    => array('string', 'notSet'),
            'featureClass'  => array('string', 'notSet'),
            'featureCode'   => array('string', 'notSet'),
        ));
        ArgValidator::assert($limit, array('int', 'null'));

        if (empty($criteria)) return $this->findAll();

        $dqb = $this->_em->createQueryBuilder();
        $dqb->select(array('p'))
            ->from('Heartsentwined\Geoname\Entity\Place', 'p');
        if (isset($criteria['featureCode'])
            || isset($criteria['featureClass'])) {
            $dqb->join('p.feature', 'f');
            if (isset($criteria['featureClass'])) {
                $dqb->join('f.parent', 'fp');
            }
        }
        $dqb->where($dqb->expr()->andX(
            isset($criteria['countryCode']) ?
                $dqb->expr()->orX(
                    $dqb->expr()->eq('p.countryCode', ':countryCode'),
                    empty($criteria['countryCode']) ?
                        $dqb->expr()->isNull('p.countryCode') : null
                ) : null,
            isset($criteria['admin1Code']) ?
                $dqb->expr()->orX(
                    $dqb->expr()->eq('p.admin1Code', ':admin1Code'),
                    empty($criteria['admin1Code']) ?
                        $dqb->expr()->isNull('p.admin1Code') : null
                ) : null,
            isset($criteria['admin2Code']) ?
                $dqb->expr()->orX(
                    $dqb->expr()->eq('p.admin2Code', ':admin2Code'),
                    empty($criteria['admin2Code']) ?
                        $dqb->expr()->isNull('p.admin2Code') : null
                ) : null,
            isset($criteria['admin3Code']) ?
                $dqb->expr()->orX(
                    $dqb->expr()->eq('p.admin3Code', ':admin3Code'),
                    empty($criteria['admin3Code']) ?
                        $dqb->expr()->isNull('p.admin3Code') : null
                ) : null,
            isset($criteria['admin4Code']) ?
                $dqb->expr()->orX(
                    $dqb->expr()->eq('p.admin4Code', ':admin4Code'),
                    empty($criteria['admin4Code']) ?
                        $dqb->expr()->isNull('p.admin4Code') : null
                ) : null,
            isset($criteria['featureCode']) ?
                $dqb->expr()->orX(
                    $dqb->expr()->eq('f.code', ':featureCode'),
                    empty($criteria['featureCode']) ?
                        $dqb->expr()->isNull('f.code') : null
                ) : null,
            isset($criteria['featureClass']) ?
                $dqb->expr()->orX(
                    $dqb->expr()->eq('fp.code', ':featureClass'),
                    empty($criteria['featureClass']) ?
                        $dqb->expr()->isNull('fp.code') : null
                ) : null
        ))
        ->setMaxResults($limit);

        foreach ($criteria as $key => $value) {
            $dqb->setParameter($key, $value);
        }

        return $dqb->getQuery()->getResult();
    }
}
