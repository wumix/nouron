<?php
namespace Nouron\Table;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Nouron\Entity\EntityInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\ResultSetInterface;
/**
 * This is the abstract class for all table classes. It implements all standard
 * methods for table classes so they are 'ready-to-use' for new table classes.
 *
 */
abstract class AbstractTable extends TableGateway
{
    /**
     * @var string
     */
    protected $table   = null;

    /**
     * @var string|array
     */
    protected $primary = 'id'; // default

    /**
     * @var EntityInterface
     */
    protected $entityPrototype = null;

    /**
     * @return string
     */
    public function getTableName() {
        return $this->table;
    }

    /**
     * @return string|array
     */
    public function getPrimary() {
        return $this->primary;
    }

    /**
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @param EntityInterface $enitity
     */
    public function __construct(AdapterInterface $adapter, EntityInterface $entity)
    {
        $hydrator  = new \Zend\Stdlib\Hydrator\ClassMethods;
        $resultSet = new \Nouron\Model\ResultSet($hydrator, $entity);
        $this->entityPrototype = $entity;

        parent::__construct($this->getTableName(),
            $adapter,
            null,
            $resultSet
        );
    }

    /**
     * @param array $array
     * @return Entity
     */
    public function createEntity($array)
    {
        if (is_array($this->getPrimary())) {
            $this->_validateId($array, $this->getPrimary());
        } else {
            $this->_validateId($array[$this->getPrimary()]);
        }
        $entity = $this->entityPrototype;
        $row = new $entity();
        $row->exchangeArray($array);
        return $row;
    }

    /**
     *
     * @param string|array $where
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    public function fetchAll($where = null, $order = null)
    {
        $select = $this->sql->select();
        if ($where) {
            $select->where($where);
        }
        if ($order) {
            $select->order($order);
        }
        return $this->selectWith($select);
    }

    /**
     *
     * @param string|array $where
     * @return Ambigous <multitype:, ArrayObject, NULL, \ArrayObject, unknown>
     */
    public function fetchRow($where)
    {
        return $this->fetchAll($where)->current();
    }

    /**
     *
     * @param numeric|array $id
     * @throws \Exception
     * @return Ambigous <multitype:, ArrayObject, NULL, \ArrayObject, unknown>
     */
    public function getEntity($id)
    {
        if (is_array($this->getPrimary())) {
            $this->_validateId($id, $this->getPrimary());
            $rowset = $this->fetchAll($id);
        } else {
            $this->_validateId($id);
            $rowset = $this->fetchAll("id = $id");
        }

        $row = $rowset->current();
        /*if (!$row) {
            #throw new \Exception("Could not find row $id");
            $row = $this->createEntity($id);
        }*/

        return $row;
    }

    /**
     * save the object to db:
     * - insert if new data
     * - else update
     *
     * TODO find a general solution for return type
     *       (now: update returns number of lines, insert return pk)
     *
     * @return integer|array The primary key
     */
    public function save($entity)
    {
        // make a copy of row data (to avoid changing original data):
        if ($entity instanceof EntityInterface) {
            $data = $entity->getArrayCopy();
        } elseif (is_array($entity) or is_object($entity)) {
            $data = (array) $entity;
        } else {
            throw new \Exception('Invalid parameter type for save(): ' . get_class($entity));
        }

        $primary = (array) $this->getPrimary();
        // primary is now an array so we can handle scalar and compound keys the same way:

        $where = array();

        foreach ($primary as $key) {
            if (array_key_exists($key, $data)) {
                $val = $data[$key];
                if ( is_numeric($val) || !empty($val) ) {
                    $where[] = "$key = $val";
                } else {
                    $missingPrimaryKey = true;
                    break;
                }
            } else {
                $missingPrimaryKey = true;
                break;
            }
        }

        // update if data set is in table,
        // else insert the new data to the table
        $result = $this->fetchAll($where)->getArrayCopy();

        if (!empty( $result ) && !isset($missingPrimaryKey)) {
            // if check is not empty the record set exists and has to be updated
            $result = $this->update($data, $where);
        } else {
            $result = $this->insert($data);
        }

//         $cache = Zend_Registry::get('cache');
//         $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('user'));
        return $result;
    }

    /**
     *
     * @param numeric $id
     */
    public function deleteEntity($id)
    {
        if (is_array($id)) {
            $this->_validateId($id, $this->getPrimary());
            $this->delete($id);
        } else {
            $this->_validateId($id);
            $this->delete(array('id' => $id));
        }
    }

    /**
     * Validates an id parameter to be a positive numeric number.
     * In case of an compound primary key the parameter $compoundKey holds the
     * indezes of the ids.
     *
     * @param  mixed       $id           the id (primary key)
     * @param  null|array  $compoundKey  OPTIONAL the indezes in case of an compoundKey
     * @throws Nouron\Model\Exception    if id is invalid
     */
    protected function _validateId($id, $compoundKey = null)
    {
        $error = false;
        if (empty($compoundKey)) {
            if (!is_numeric($id) || $id < 0) {
                print_r($id);
                print_r($this->getPrimary());
                throw new \Exception('Parameter is not a valid id.');
            }
        } else {
            $idArray = $id;
            if (is_array($idArray)) {
                foreach ($compoundKey as $key) {
                    if (!isset($idArray[$key])) {
                        $error = true;
                        break;
                    }
                    try {
                        $this->_validateId($idArray[$key]);
                    } catch (Exception $e) {
                        $error = true;
                    }
                }
            } else {
                $error = true;
            }
            if ($error) {
                throw new \Exception('Parameter is not a valid compound id.');
            }
        }
    }
}