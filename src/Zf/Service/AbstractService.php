<?php

namespace AuctioCore\Zf\Service;

use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

/**
 * Class AbstractService
 */
abstract class AbstractService implements InputFilterAwareInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $om;

    /**
     * @var \DoctrineModule\Stdlib\Hydrator\DoctrineObject
     */
    private $hydrator;

    /**
     * @var Name of the \Doctrine\ORM\EntityRepository
     */
    protected $objectName;

    /**
     * @var inputData
     */
    protected $inputData;

    /**
     * @var InputFilter
     */
    protected $inputFilter;

    /**
     * @var Error-messages
     */
    private $messages;

    /**
     * Constructor
     *
     * @param EntityManager $objectManager
     */
    public function __construct(EntityManager $objectManager = null)
    {
        if (!empty($objectManager)) {
            $this->om = $objectManager;
        }
        $this->messages = [];
    }

    /**
     * Get ObjectManager
     *
     * @return EntityManager
     */
    public function getObjectManager()
    {
        return $this->om;
    }

    /**
     * Set the ObjectName
     *
     * @param $entityNamespace
     * @param $name
     */
    public function setObjectName($entityNamespace)
    {
        $this->objectName = $entityNamespace;
    }

    /**
     * Get the ObjectName
     *
     * @return mixed
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Get Hydrator
     *
     * @return DoctrineObject
     */
    public function getHydrator()
    {
        // create hydrator if not created yet
        if ($this->hydrator === null) {
            // create hydrator
            $this->hydrator = new DoctrineObject($this->om);
        }

        return $this->hydrator;
    }

    /**
     * Set Hydrator
     *
     * @param DoctrineObject $hydrator
     */
    public function setHydrator(DoctrineObject $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * Get input filter
     *
     * @return object
     */
    public abstract function getInputFilter();

    /**
     * Set input filter
     *
     * @param  InputFilterInterface $inputFilter
     * @return object
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;

        return $this;
    }

    /**
     * Prepare input-data (default)
     *
     * @param  array $data
     * @return array
     */
    public function prepareInputDataDefault($data)
    {
        // Unset specific database-fields (if available)
        if (isset($data['id'])) unset($data['id']);
        if (isset($data['created'])) unset($data['created']);
        if (isset($data['lastUpdated'])) unset($data['lastUpdated']);

        $this->inputData = $data;
    }

    /**
     * Prepare input-data (specific for entity)
     *
     * @return array
     */
    public abstract function prepareInputData();

    /**
     * Hydrate object, apply inputfilter, save it, and return result
     *
     * @param $data
     * @param $object
     * @return bool
     */
    public function filterAndPersist($data, &$object)
    {
        // hydrate data to object
        $this->getHydrator()->hydrate($data, $object);

        // check if data is valid
        $this->getInputFilter()->setData($this->getHydrator()->extract($object));
        if (!$this->getInputFilter()->isValid()) {
            // get error messages from inputfilter
            $this->messages = array_merge($this->messages, $this->getInputFilter()->getMessages());
        }

        // if no problems found, continue to save it
        if (empty($this->messages)) {
            // persist and flush object
            try {
                $this->getObjectManager()->persist($object);
                $this->getObjectManager()->flush();
            } catch (Exception $e) {
                $this->messages['flushException'] = $e->getMessage();
            }
        }

        // return false if errors were found
        if (empty($this->messages)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Transform object-data into usable data
     *
     * @param array $data
     * @return array
     */
    public function transformData($data)
    {
        if (isset($data['results']) && is_array($data['results'])) {
            foreach ($data['results'] AS $k => $record) {
                $data['results'][$k] = $this->transformRecord($record);
            }
        } elseif (is_array($data) && !isset($data['id'])) {
            foreach ($data AS $k => $record) {
                $data[$k] = $this->transformRecord($record);
            }
        } else {
            $data = $this->transformRecord($data);
        }
        return $data;
    }

    /**
     * Transform object-record into usable record
     *
     * @param array $record
     * @return array
     */

    public abstract function transformRecord($record);

    /**
     * Transform object-values into usable values
     *
     * @param mixed $data
     * @param array $fields
     * @return array
     */
    public function transformValues($data, $fields)
    {
        if (empty($fields)) return;
        if (empty($data)) return;

        if ($data instanceof \Doctrine\ORM\PersistentCollection) {
            if (count($data) < 1) return;

            $values = [];
            foreach ($data AS $k => $v) {
                $values[$k] = $this->transformValues($v, $fields);
            }
        } else {
            $values = [];
            foreach ($fields AS $k => $field) {
                if (is_array($field)) {
                    $func = 'get' . ucfirst($k);
                    $values[$k] = $this->transformValues($data->$func(), $field);
                } else {
                    if (is_object($data)) {
                        $func = 'get' . ucfirst($field);
                        $fieldValue = $data->$func();
                    } elseif (is_array($data)) {
                        $fieldValue = $data[$field];
                    }
                    $values[$field] = $fieldValue;
                }
            }
        }

        return $values;
    }

    /**
     * Return a single object from the repository
     *
     * @param $id
     * @param $output
     * @return array/object
     */
    public function get($id, $output = 'object')
    {
        // get object from the repository specified by primary key
        $object = $this->om
            ->getRepository($this->objectName)
            ->find($id);

        // return error if object not found
        if ($object == null) {
            return false;
        }

        // return
        if ($output == 'array') {
            $record = $this->getHydrator()->extract($object);

            // Return result
            if (method_exists($this, 'transformData')) return $this->transformData($record);
            else return $record;
        } else {
            return $object;
        }
    }

    /**
     * Return all objects from the repository
     *
     * @return array
     */
    public function getAll($output = 'object')
    {
        // get all objects from the repository
        $objects = $this->om
            ->getRepository($this->objectName)
            ->findAll();

        // convert objects to arrays
        if ($output == 'array') {
            $records = [];
            $hydrator = $this->getHydrator();
            foreach ($objects as $object) {
                $records[] = $hydrator->extract($object);
            }

            // Return result
            if (method_exists($this, 'transformData')) return $this->transformData($records);
            else return $records;
        } else {
            return $objects;
        }
    }

    /**
     * Return a list of objects from the repository
     *
     * @param string $output
     * @param array $filter
     * @param array $orderBy
     * @param integer $limitRecords
     * @param integer $offset
     * @param boolean $paginator
     * @param boolean $debug
     * @return array/object
     */
    public function getList($output = 'object', $filter = NULL, $orderBy = NULL, $limitRecords = 25, $offset = 0, $paginator = false, $debug = false)
    {
        if (!empty($limitRecords)) $limit['limit'] = (int) $limitRecords;
        else $limit['limit'] = 25;
        $limit['offset'] = $offset;
        if (!is_array($filter)) $filter = array();

        // Get results
        $records = $this->getByFilter($filter, $orderBy, $limit, $paginator, $debug);

        // Convert object to array (if output is array)
        if ($output == 'array') {
            $hydrator = $this->getHydrator();
            if ($paginator === true) {
                foreach ($records['results'] AS $k => $v) {
                    $records['results'][$k] = $hydrator->extract($v);
                }
            } else {
                foreach ($records AS $k => $v) {
                    $records[$k] = $hydrator->extract($v);
                }
            }

            // Return result
            if (method_exists($this, 'transformData')) return $this->transformData($records);
            else return $records;
        } else {
            // Return result
            return $records;
        }
    }

    /**
     * Return objects by filter
     *
     * @param $filter
     * @param $orderBy
     * @param $limit
     * @param $paginator
     * @param $debug
     * @return array/object
     */
    public function getByFilter($filter = NULL, $orderBy = null, $limit = NULL, $paginator = false, $debug = false)
    {
        // Build query
        $query = $this->om->createQueryBuilder();
        if ($paginator) $queryPaginator = $this->om->createQueryBuilder();

        // Set fields
        $query->select('f');
        if ($paginator) $queryPaginator->select(array('COUNT(f.id) total'));
        // Set from
        $query->from($this->objectName, 'f');
        if ($paginator) $queryPaginator->from($this->objectName, 'f');
        // Set filter (if available)
        if (!empty($filter)) {
            $query->where($filter['filter']);
            $query->setParameters($filter['parameters']);
            if ($paginator) $queryPaginator->where($filter['filter']);
            if ($paginator) $queryPaginator->setParameters($filter['parameters']);
        }
        // Set order-by (if available)
        if (!empty($orderBy)) {
            foreach ($orderBy AS $order) {
                $direction = (!empty($order['direction'])) ? $order['direction'] : null;
                $query->addOrderBy($order['field'], $direction);
            }
        }
        // Set limit (if available)
        if (!empty($limit)) {
            if (!empty($limit['offset'])) {
                $query->setFirstResult($limit['offset']);
            }
            if (!empty($limit['limit'])) {
                // Set maximum limit to 1000 records!
                if ($limit['limit'] > 1000) {
                    $limit['limit'] = 1000;
                }

                $query->setMaxResults($limit['limit']);
            }
        }

        // Return DQL (in debug-mode)
        if ($debug) {
            return array("results"=>array("query"=>$query->getQuery()->getDQL(), "parameters"=>$filter['parameters']));
        }

        // Get results
        if ($paginator) {
            // Set paginator-results
            $paginatorResults = $queryPaginator->getQuery()->getSingleResult();
            $paginatorData['records'] = (int) $paginatorResults['total'];
            $paginatorData['pages'] = (int) ceil($paginatorResults['total'] / $limit['limit']);
            $paginatorData['currentPage'] = (int) (ceil($limit['offset'] / $limit['limit']) + 1);
            $paginatorData['recordsPage'] = (int) $limit['limit'];

            // Get "page"-results
            $results = $query->getQuery()->getResult();

            // Return
            return array("paginator"=>$paginatorData, "results"=>$results);
        } else {
            // Return
            return $query->getQuery()->getResult();
        }
    }

    /**
     * Return all objects from the repository with parameters
     *
     * @param array $parameters
     * @param string $output [object, array]
     * @param boolean $multiple
     * @return array/object
     */
    public function getByParameters($parameters, $output = 'object', $multiple = true)
    {
        // get object from the repository specified by primary key
        $objects = $this->om
            ->getRepository($this->objectName)
            ->findBy($parameters);

        // return error if object not found
        if ($objects == null) {
            return false;
        }

        // convert objects to arrays
        if ($output == 'array') {
            $data = [];
            $hydrator = $this->getHydrator();
            foreach ($objects as $object) {
                $data[] = $hydrator->extract($object);
            }

            // return
            if ($multiple === false) {
                return current($data);
            } else {
                return $data;
            }
        } else {
            if ($multiple === false) {
                return current($objects);
            } else {
                return $objects;
            }
        }
    }

    /**
     * Create a new object
     *
     * @param $data
     * @return array
     */
    public function create($data, $output = 'object')
    {
        // create object instance
        $object = new $this->objectName();

        // Prepare data
        $this->prepareInputDataDefault($data);
        $this->prepareInputData();

        // Set default data (if not available)
        if (property_exists($object, 'created')) $this->inputData['created'] = new \DateTime();
        if (property_exists($object, 'deleted')) $this->inputData['deleted'] = false;

        // hydrate object, apply inputfilter, and save it
        if ($this->filterAndPersist($this->inputData, $object)) {
            if ($output == 'array') {
                // Return result
                $record = $this->getHydrator()->extract($object);
                if (method_exists($this, 'transformData')) return $this->transformData($record);
                else return $record;
            } else {
                return $object;
            }
        } else {
            return [
                'error' => true,
                'message' => $this->messages,
            ];
        }
    }

    /**
     * Update an existing object
     *
     * @param $id
     * @param $data
     * @return array
     */
    public function update($id, $data, $output = 'object')
    {
        // get existing object
        $object = $this->getObjectManager()
            ->getRepository($this->getObjectName())
            ->find($id);

        if ($object == null) {
            return [
                'error' => true,
                'message' => $this->objectName .' not found!',
            ];
        }

        // Prepare data
        $this->prepareInputDataDefault($data);
        $this->prepareInputData();

        // Set default data (if not available)
        if (property_exists($object, 'lastUpdated')) $this->inputData['lastUpdated'] = new \DateTime();

        // hydrate object, apply inputfilter, save it, and return result
        if ($this->filterAndPersist($this->inputData, $object)) {
            if ($output == 'array') {
                // Return result
                $record = $this->getHydrator()->extract($object);
                if (method_exists($this, 'transformData')) return $this->transformData($record);
                else return $record;
            } else {
                return $object;
            }
        } else {
            return [
                'error' => true,
                'message' => $this->messages,
            ];
        }
    }

    /**
     * Delete an object from the repository
     *
     * @param $id
     * @param $remove
     * @return array
     */
    public function delete($id, $remove = false)
    {
        // get object from the repository specified by primary key
        $object = $this->om
            ->getRepository($this->objectName)
            ->find($id);

        // return error if object not found
        if ($object == null) {
            return [
                'error' => true,
                'message' => $this->objectName .' not found!',
            ];
        }

        // check if object really has to move of only update status
        if ($remove === false) {
            $result = $this->update($id, ['deleted'=>true], 'array');
            if (isset($result['error'])) {
                return $result;
            }
        } else {
            // remove the object from the repository or return error if something went wrong
            try {
                $this->om->remove($object);
                $this->om->flush();
            } catch (Exception $e) {
                return [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }
        }

        // return succes message
        return true;
    }

}