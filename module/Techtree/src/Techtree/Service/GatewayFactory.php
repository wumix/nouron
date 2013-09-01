<?php
namespace Techtree\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

use Techtree\Table\TechnologyTable;
use Techtree\Table\PossessionTable;
use Techtree\Table\RequirementTable;
use Techtree\Table\OrderTable;
use Techtree\Table\CostTable;
use Techtree\Table\ActionPointTable;

use Techtree\Entity\Technology;
use Techtree\Entity\Possession;
use Techtree\Entity\Requirement;
use Techtree\Entity\Order;
use Techtree\Entity\Cost;
use Techtree\Entity\ActionPoint;


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

        $tables = array();
        $tables['technology']  = new TechnologyTable($db, new Technology());
        $tables['possession']  = new PossessionTable($db, new Possession());
        $tables['requirement'] = new RequirementTable($db, new Requirement());
        #$tables['order'] = new OrderTable($db, new Order);
        $tables['cost']  = new CostTable($db, new Cost());
        $tables['log_actionpoints'] = new ActionPointTable($db, new ActionPoint());

        $services = array();
        $services['resources'] = $serviceLocator->get('Resources\Service\Gateway');
        $services['galaxy']    = $serviceLocator->get('Galaxy\Service\Gateway');

        $techtreeGateway = new Gateway($tick, $tables, $services);
        $techtreeGateway->setLogger($logger);
        return $techtreeGateway;
    }
}