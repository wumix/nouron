<?php
namespace Techtree\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GatewayFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Techtree\Service\Gateway
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db     = $serviceLocator->get("Zend\Db\Adapter\Adapter");
        $tick   = $serviceLocator->get('Nouron\Service\Tick');
        $logger = $serviceLocator->get('logger');

        $tables['technology']  = new \Techtree\Table\Technology($db);
        $tables['possession']  = new \Techtree\Table\Possession($db);
        $tables['requirement'] = new \Techtree\Table\Requirement($db);
        $tables['order'] = new \Techtree\Table\Order($db);
        $tables['cost']  = new \Techtree\Table\Cost($db);

        $gateways['resources'] = $serviceLocator->get('Resources\Service\Gateway');
        $gateways['galaxy']    = $serviceLocator->get('Galaxy\Service\Gateway');

        $techtreeGateway = new Gateway($tick, $tables, $gateways);
        $techtreeGateway->setLogger($logger);
        return $techtreeGateway;
    }
}