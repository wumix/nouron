<?php
namespace Galaxy\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GatewayFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db     = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $tick   = $serviceLocator->get('Nouron\Service\Tick');
        $logger = $serviceLocator->get('logger');

        $tables['colony'] = new \Galaxy\Table\Colony($db);
        $tables['system'] = new \Galaxy\Table\System($db);
        $tables['systemobject'] = new \Galaxy\Table\SystemObject($db);

        $gateway = new Gateway($tick, $tables, array());
        $gateway->setLogger($logger);
        return $gateway;
    }
}