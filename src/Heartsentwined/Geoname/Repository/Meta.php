<?php
namespace Heartsentwined\Geoname\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Meta
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Meta extends EntityRepository
{
    const STATUS_INSTALL    = 'install';
    const STATUS_INSTALL_DOWNLOAD   = 'install_download';
    const STATUS_UPDATE     = 'update';
}
