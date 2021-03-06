<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * MACAddress
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MACAddress extends EntityRepository
{
    /**
     * Find virtual interface by MAC address
     * 
     * @param  string $mac The MAC address to search for
     * @return \Entities\VirtualInterface[] Matching interfaces
     */
    public function findVirtualInterface( $mac )
    {
        return $this->getEntityManager()->createQuery(
                "SELECT vi
        
                 FROM \\Entities\\VirtualInterface vi
                 LEFT JOIN vi.MACAddresses m

                 WHERE m.mac = :mac"
            )
            ->setParameter( 'mac', strtolower( str_replace( ':', '', $mac ) ) )
            ->getResult();
    }
}
