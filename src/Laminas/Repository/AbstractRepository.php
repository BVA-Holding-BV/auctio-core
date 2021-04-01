<?php

namespace AuctioCore\Laminas\Repository;

use __PHP_Incomplete_Class;
use DateTime;
use Doctrine\ORM\PersistentCollection;
use Exception;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class AbstractRepository
 */
abstract class AbstractRepository implements InputFilterAwareInterface
{
    /**
     * @var EntityManager
     */
    protected EntityManager $om;

    /**
     * @var DoctrineObject
     */
    private $hydrator;

    /**
     * @var string $objectName Name of the \Doctrine\ORM\EntityRepository
     */
    protected string $objectName;

    /**
     * @var array $filterAssociations List of filter-associations
     */
    protected array $filterAssociations;

    /**
     * @var array $inputData
     */
    protected array $inputData;

    /**
     * @var InputFilterInterface $inputFilter
     */
    protected $inputFilter;

    /**
     * @var array $messages Error-messages
     */
    private array $messages;

    /**
     * @var array $errorData Error-data
     */
    private array $errorData;

    /**
     * @var string $cacheFolder Cache-folder
     */
    protected string $cacheFolder;

    /**
     * Constructor
     *
     * @param EntityManager|null $objectManager
     */
    public function __construct(EntityManager $objectManager = null)
    {
        if (!empty($objectManager)) {
            $this->om = $objectManager;
        }
        $this->messages = [];
        $this->errorData = [];

        // Set cache-folder
        $this->cacheFolder = getcwd() . "/data/cache/Entity/";
    }

    /**
     * Get ObjectManager
     *
     * @return EntityManager
     */
    public function getObjectManager(): EntityManager
    {
        return $this->om;
    }

    /**
     * Set the ObjectName
     *
     * @param $entityNamespace
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
    public function getObjectName(): string
    {
        return $this->objectName;
    }

    /**
     * Get Hydrator
     *
     * @return DoctrineObject
     */
    public function getHydrator(): DoctrineObject
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
     */
    public abstract function getInputFilter(): InputFilterInterface;

    /**
     * Set input filter
     *
     * @param  InputFilterInterface $inputFilter
     * @return object
     */
    public function setInputFilter(InputFilterInterface $inputFilter): object
    {
        $this->inputFilter = $inputFilter;

        return $this;
    }

    /**
     * Set filter-associations of entity
     *
     * @param array $filterAssociations
     */
    public function setFilterAssociations(array $filterAssociations)
    {
        $this->filterAssociations = $filterAssociations;
    }

    /**
     * Get filter-associations of entity
     *
     * @return mixed
     */
    public function getFilterAssociations(): array
    {
        return $this->filterAssociations;
    }

    /**
     * Set error-data
     *
     * @param $data
     */
    public function setErrorData($data)
    {
        $this->errorData = $data;
    }

    /**
     * Get error-data
     *
     * @return array
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Set error-message
     *
     * @param array|string $messages
     */
    public function setMessages($messages)
    {
        if (!is_array($messages)) $messages = [$messages];
        $this->messages = $messages;
    }

    /**
     * Add error-message
     *
     * @param array|string $message
     */
    public function addMessage($message)
    {
        if (!is_array($message)) $message = [$message];
        $this->messages = array_merge($this->messages, $message);
    }

    /**
     * Get error-messages
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Reset error-messages and error-data
     */
    public function resetErrors()
    {
        $this->messages = [];
        $this->errorData = [];
    }

    /**
     * Prepare input-data (default)
     *
     * @param  array $data
     * @param  array $overrule
     */
    public function prepareInputDataDefault(array $data, $overrule = [])
    {
        // Unset specific database-fields (if available)
        if (isset($data['id']) && !in_array('id', $overrule)) unset($data['id']);
        if (isset($data['created']) && !in_array('created', $overrule)) unset($data['created']);
        if (isset($data['lastUpdated']) && !in_array('lastUpdated', $overrule)) unset($data['lastUpdated']);

        $this->inputData = $data;
    }

    /**
     * Prepare input-data (specific for entity)
     *
     * @return array
     */
    public abstract function prepareInputData(): array;

    /**
     * Hydrate object, apply inputfilter, save it, and return result
     *
     * @param $data
     * @param $object
     * @param bool $flush
     * @return bool
     */
    public function filterAndPersist($data, $object, $flush = true): bool
    {
        // Hydrate data to object
        $this->getHydrator()->hydrate($data, $object);

        // Check if data is valid
        $this->getInputFilter()->setData($this->getHydrator()->extract($object));
        if (!$this->getInputFilter()->isValid()) {
            // Get error messages from inputfilter
            $this->addMessage($this->getInputFilter()->getMessages());
        }

        // If no problems found, continue to save it
        if (empty($this->messages)) {
            // Persist and flush object
            try {
                $this->getObjectManager()->persist($object);

                // Only flush (if permitted, used for bulk mutations)
                if ($flush === true) {
                    $this->getObjectManager()->flush();
                }
            } catch (Exception $e) {
                $this->addMessage(['flushException' => $e->getMessage()]);
            }
        }

        // Return false if errors were found
        if (empty($this->messages)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Hydrate object, apply inputfilter, save it, and return result (in bulk)
     *
     * @param $records
     * @param $objects
     * @return bool
     */
    public function filterAndPersistBulk($records, $objects): bool
    {
        // Iterate data
        foreach ($records AS $key => $record) {
            $object = $this->filterAndPersist($record, $objects[$key], false);
            if ($object === false) break;
        }

        // Flush prepared records
        if (empty($this->messages)) {
            try {
                $this->getObjectManager()->flush();
            } catch (Exception $e) {
                $this->addMessage(['flushException' => $e->getMessage()]);
            }
        }

        // Return false if errors were found
        if (empty($this->messages)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Transform object-data into usable data
     *
     * @param $data
     * @return array
     */
    public function transformData($data): array
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

    public abstract function transformRecord(array $record): array;

    /**
     * Transform object-values into usable values
     *
     * @param mixed $data
     * @param array $fields
     * @return array|void
     */
    public function transformValues($data, array $fields): array
    {
        if (empty($fields)) return;
        if (empty($data)) return;

        if ($data instanceof PersistentCollection) {
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
     * Check if object exists
     *
     * @param $id
     * @return boolean
     */
    public function exists($id): bool
    {
        // get object from the repository specified by primary key
        $object = $this->om
            ->getRepository($this->objectName)
            ->find($id);

        // return
        if ($object == null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Return a single object from the repository
     *
     * @param $id
     * @param $output
     * @param $refresh
     * @return false|object|array
     */
    public function get($id, $output = 'object', $refresh = false)
    {
        // get object from the repository specified by primary key
        $object = $this->om
            ->getRepository($this->objectName)
            ->find($id);

        // refresh entity (clear all local changes)
        if ($refresh === true) {
            $this->om->refresh($object);
        }

        // return error if object not found
        if ($object == null) {
            $this->setMessages(['notFound' => $this->objectName. ' not found']);
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
     * @param $output
     * @param $refresh
     * @return object|array
     */
    public function getAll($output = 'object', $refresh = false)
    {
        // get all objects from the repository
        $objects = $this->om
            ->getRepository($this->objectName)
            ->findAll();

        // refresh entity (clear all local changes)
        if ($refresh === true) {
            foreach ($objects AS $object) {
                $this->om->refresh($object);
            }
        }

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
     * @param array $groupBy
     * @param array $having
     * @param array $orderBy
     * @param integer $limitRecords
     * @param integer $offset
     * @param boolean $paginator
     * @param boolean $debug
     * @return array|object
     */
    public function getList($output = 'object', $filter = NULL, $groupBy = null, $having = null, $orderBy = NULL, $limitRecords = 25, $offset = 0, $paginator = false, $debug = false)
    {
        if (!empty($limitRecords)) $limit['limit'] = (int) $limitRecords;
        else $limit['limit'] = 25;
        $limit['offset'] = $offset;
        if (!is_array($filter)) $filter = [];

        // Get results
        $records = $this->getByFilter($filter, $groupBy, $having, $orderBy, $limit, $paginator, $debug);

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
     * Return result by cache(file)
     *
     * @param string $cacheFile
     * @return array|object
     */
    public function getByCache(string $cacheFile)
    {
        // Check if cache-file exists
        if (is_file($cacheFile)) {
            $result = file_get_contents($cacheFile);
            $result = unserialize($result);

            // Reset result if object is invalid (class not loaded)
            if ($result instanceof __PHP_Incomplete_Class) {
                $result = false;
            }
        } else {
            $result = false;
        }

        // Return
        return $result;
    }

    /**
     * Return objects by filter
     *
     * @param $filter
     * @param $groupBy
     * @param $having
     * @param $orderBy
     * @param $limit
     * @param $paginator
     * @param $debug
     * @return array|object
     */
    public function getByFilter($filter = NULL, $groupBy = null, $having = null, $orderBy = null, $limit = NULL, $paginator = false, $debug = false)
    {
        // Build query
        $query = $this->om->createQueryBuilder();
        $parameters = [];

        // Set fields
        if (!empty($groupBy)) $query->select($groupBy);
        else $query->select('f');
        // Set from
        $query->from($this->objectName, 'f');
        // Set joins (if available/needed)
        if ((!empty($filter) || !empty($orderBy) || !empty($groupBy)) && !empty($this->getFilterAssociations())) {
            $joins = [];
            foreach ($this->getFilterAssociations() AS $filterAssociation) {
                $match = false;
                if (stristr($filter['filter'], $filterAssociation['alias'] . ".") && !in_array($filterAssociation['alias'], $joins)) {
                    $match = true;
                } elseif (!empty($orderBy)) {
                    foreach ($orderBy AS $orderByField) {
                        if (stristr($orderByField['field'], $filterAssociation['alias'] . ".") && !in_array($filterAssociation['alias'], $joins)) {
                            $match = true;
                        }
                    }
                } elseif (!empty($groupBy)) {
                    foreach ($groupBy AS $groupByField) {
                        if (stristr($groupByField, $filterAssociation['alias'] . ".") && !in_array($filterAssociation['alias'], $joins)) {
                            $match = true;
                        }
                    }
                }

                if ($match === true) {
                    // Loop associations (to set filter-association-joins) till base (f.xx) reached (ORDER OF ADDING JOINS TO QUERY IS IMPORTANT, THEREFORE NOT ADD DIRECTLY TO QUERY!)
                    $filterAssociationJoins = [];
                    $association = $filterAssociation;
                    while (substr($association['join'], 0, 2) != "f.") {
                        $alias = current(explode(".", $association['join']));
                        $key = array_search($alias, array_column($this->getFilterAssociations(), 'alias'));
                        $association = $this->getFilterAssociations()[$key];
                        if (!in_array($association['alias'], $joins)) {
                            $joins[] = $association['alias'];
                            $filterAssociationJoins[] = $association;
                        }
                    }
                    // Set filter-association-joins (reverse order), if available for filter-association
                    if (!empty($filterAssociationJoins)) {
                        krsort($filterAssociationJoins);
                        foreach ($filterAssociationJoins AS $filterAssociationJoin) {
                            $query->leftJoin($filterAssociationJoin['join'], $filterAssociationJoin['alias']);
                        }
                    }
                    // Set association
                    $joins[] = $filterAssociation['alias'];
                    $query->leftJoin($filterAssociation['join'], $filterAssociation['alias']);
                }
            }
        }

        // Set filter (if available)
        if (!empty($filter)) {
            $query->where($filter['filter']);
            $parameters = (empty($parameters)) ? $filter['parameters'] : array_merge($parameters, $filter['parameters']);
        }
        // Set group-by (if available)
        if (!empty($groupBy)) {
            foreach ($groupBy AS $group) {
                $query->addGroupBy($group);
            }
        }
        // Set having (if available)
        if (!empty($having)) {
            $query->having($having['filter']);
            $parameters = (empty($parameters)) ? $having['parameters'] : array_merge($parameters, $having['parameters']);
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
        // Set parameters (if available)
        if (!empty($parameters)) {
            $query->setParameters($parameters);
        }

        // Return DQL (in debug-mode)
        if ($debug) {
            return ["results"=>["query"=>$query->getQuery()->getDQL(), "parameters"=>$parameters]];
        }

        // Get results
        if ($paginator) {
            // Set paginator-result
            $paginatorResult = new Paginator($query, $fetchJoinCollection = true);
            $paginatorData['records'] = (int) count($paginatorResult);
            $paginatorData['pages'] = (int) ceil($paginatorData['records'] / $limit['limit']);
            $paginatorData['currentPage'] = (int) (ceil($limit['offset'] / $limit['limit']) + 1);
            $paginatorData['recordsPage'] = (int) $limit['limit'];

            // Get "page"-results
            $results = $query->getQuery()->getResult();

            // Return
            return ["paginator"=>$paginatorData, "results"=>$results];
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
     * @param boolean $cache
     * @return false|array|object
     */
    public function getByParameters(array $parameters, $output = 'object', $multiple = true, $cache = false)
    {
        if (empty($output)) $output = 'object';

        // Check if cache available (if enabled)
        $result = null;
        if ($cache === true) {
            $objectCacheFolder = $this->cacheFolder . str_replace("\\", "_", str_replace("\Entity\\", "\\", $this->objectName)) . "/";
            $objectCacheFile = $objectCacheFolder . __FUNCTION__ . "_" . base64_encode(json_encode($parameters) . "_" . strtolower($output) . "_" . $multiple);
            $result = $this->getByCache($objectCacheFile);
        }

        if ($cache !== true || ($cache === true && $result === false)) {
            // Get object from the repository specified by primary key
            $objects = $this->om
                ->getRepository($this->objectName)
                ->findBy($parameters);

            // Return error if object not found
            if ($objects == null) {
                $this->setMessages(['notFound' => $this->objectName . ' not found']);
                return false;
            }

            // Convert objects to arrays
            if (strtolower($output) == 'array') {
                $data = [];
                $hydrator = $this->getHydrator();
                foreach ($objects as $object) {
                    $data[] = $hydrator->extract($object);
                }

                if ($multiple === false) {
                    $result = current($data);
                } else {
                    $result = $data;
                }
            } else {
                if ($multiple === false) {
                    $result = current($objects);
                } else {
                    $result = $objects;
                }
            }

            // Save cache (if enabled)
            if ($cache === true) {
                $this->saveCache($objectCacheFolder, $objectCacheFile, $result);
            }
        }

        // Return
        return $result;
    }

    /**
     * Get field-value by id(s)
     *
     * @param string $field
     * @param int|array $id
     * @return string|array
     */
    public function getFieldById(string $field, $id)
    {
        // Find records by field-value parameters
        $multiple = is_array($id);
        $records = $this->getByParameters(['id'=>$id], "object", $multiple);

        // Return
        if (is_array($id)) {
            // Get multiple values
            $values = [];
            foreach ($records AS $record) {
                $values[] = $record->{'get' . ucfirst($field)}();
            }

            return $values;
        } else {
            return $records->{'get' . ucfirst($field)}();
        }
    }

    /**
     * Get id(s) by field-value(s)
     *
     * @param string $field
     * @param string|array $value
     * @param boolean $cache
     * @return string|array
     */
    public function getIdByField(string $field, $value, $cache = false)
    {
        // Find records by field-value parameters
        $multiple = is_array($value);
        $records = $this->getByParameters([$field=>$value], "object", $multiple, $cache);

        // Return
        if (is_bool($records)) {
            return false;
        } elseif (is_array($records)) {
            // Get multiple ids
            $ids = [];
            foreach ($records AS $record) {
                $ids[] = $record->getId();
            }

            return $ids;
        } else {
            return $records->getId();
        }
    }

    /**
     * Create a new object
     *
     * @param $data
     * @param $output
     * @param $overrule
     * @return bool|array|object
     */
    public function create($data, $output = 'object', $overrule = [])
    {
        // Reset errors
        $this->resetErrors();

        // Create object instance
        $object = new $this->objectName();

        // Prepare data
        $this->prepareInputDataDefault($data, $overrule);
        $this->prepareInputData();

        // Set default data (if not available)
        if (property_exists($object, 'created')) $this->inputData['created'] = new DateTime();
        if (property_exists($object, 'deleted')) $this->inputData['deleted'] = false;

        // Hydrate object, apply inputfilter, and save it
        $result = $this->filterAndPersist($this->inputData, $object);
        if ($result === false) return false;
        if ($output == 'array') {
            // Return result
            $record = $this->getHydrator()->extract($object);
            if (method_exists($this, 'transformData')) return $this->transformData($record);
            else return $record;
        } elseif ($output == 'boolean') {
            return true;
        } else {
            return $object;
        }
    }

    /**
     * Create new objects (in bulk)
     *
     * @param $data
     * @param $output
     * @param $overrule
     * @return bool|array
     */
    public function createBulk($data, $output = 'object', $overrule = [])
    {
        // Reset errors
        $this->resetErrors();

        // Iterate data
        $objects = [];
        $recordData = [];
        foreach ($data AS $key => $value) {
            // Create object instance
            $objects[$key] = new $this->objectName();

            // Prepare data
            $this->prepareInputDataDefault($value, $overrule);
            $this->prepareInputData();

            // Set default data (if not available)
            if (property_exists($objects[$key], 'created')) $this->inputData['created'] = new DateTime();
            if (property_exists($objects[$key], 'deleted')) $this->inputData['deleted'] = false;
            $recordData[$key] = $this->inputData;
        }

        // Hydrate object, apply inputfilter, and save it
        $result = $this->filterAndPersistBulk($recordData, $objects);
        if ($result === false) return false;
        if ($output == 'array') {
            // Return results
            $records = [];
            foreach ($objects AS $key => $object) {
                $record = $this->getHydrator()->extract($object);
                if (method_exists($this, 'transformData')) $records[$key] = $this->transformData($record);
                else $records[$key] = $record;
            }
            return $records;
        } elseif ($output == 'boolean') {
            return true;
        } else {
            return $objects;
        }
    }

    /**
     * Update an existing object
     *
     * @param $id
     * @param $data
     * @param $output
     * @param $refresh
     * @return bool|array|object
     */
    public function update($id, $data, $output = 'object', $refresh = false)
    {
        // Reset errors
        $this->resetErrors();

        // Get existing object
        $object = $this->getObjectManager()
            ->getRepository($this->getObjectName())
            ->find($id);

        // Refresh entity (clear all local changes)
        if ($refresh === true) {
            $this->om->refresh($object);
        }

        if ($object == null) {
            $this->setMessages(['notFound' => $this->objectName. ' not found']);
            return false;
        }

        // Prepare data
        $this->prepareInputDataDefault($data);
        $this->prepareInputData();

        // Set default data (if not available)
        if (property_exists($object, 'lastUpdated')) $this->inputData['lastUpdated'] = new DateTime();

        // hydrate object, apply inputfilter, save it, and return result
        $result = $this->filterAndPersist($this->inputData, $object);
        if ($result === false) return false;
        if ($output == 'array') {
            // Return result
            $record = $this->getHydrator()->extract($object);
            if (method_exists($this, 'transformData')) return $this->transformData($record);
            else return $record;
        } elseif ($output == 'boolean') {
            return true;
        } else {
            return $object;
        }
    }

    /**
     * Update existing objects (in bulk)
     *
     * @param $data
     * @param $output
     * @param $refresh
     * @return false|array
     */
    public function updateBulk($data, $output = 'object', $refresh = false)
    {
        // Reset errors
        $this->resetErrors();

        // Iterate data
        $objects = [];
        $recordData = [];
        foreach ($data AS $id => $value) {
            // Get existing object
            $object = $this->getObjectManager()
                ->getRepository($this->getObjectName())
                ->find($id);

            // Refresh entity (clear all local changes)
            if ($refresh === true) {
                $this->om->refresh($object);
            }

            if ($object == null) {
                $this->setMessages(['notFound' => $this->objectName. ' not found']);
                return false;
            }

            // Prepare data
            $this->prepareInputDataDefault($value);
            $this->prepareInputData();

            // Set default data (if not available)
            if (property_exists($object, 'lastUpdated')) $this->inputData['lastUpdated'] = new DateTime();
            $recordData[$id] = $this->inputData;
            $objects[$id] = $object;
        }

        // Hydrate object, apply inputfilter, and save it
        $result = $this->filterAndPersistBulk($recordData, $objects);
        if ($result === false) return false;
        if ($output == 'array') {
            // Return results
            $records = [];
            foreach ($objects AS $key => $object) {
                $record = $this->getHydrator()->extract($object);
                if (method_exists($this, 'transformData')) $records[] = $this->transformData($record);
                else $records[] = $record;
            }
            return $records;
        } elseif ($output == 'boolean') {
            return true;
        } else {
            return $objects;
        }
    }

    /**
     * Delete an object from the repository
     *
     * @param $id
     * @param $remove
     * @param $refresh
     * @return false|array
     */
    public function delete($id, $remove = false, $refresh = false)
    {
        // Reset errors
        $this->resetErrors();

        // get object from the repository specified by primary key
        $object = $this->om
            ->getRepository($this->objectName)
            ->find($id);

        // refresh entity (clear all local changes)
        if ($refresh === true) {
            $this->om->refresh($object);
        }

        // return error if object not found
        if ($object == null) {
            $this->setMessages(['notFound' => $this->objectName. ' not found']);
            return false;
        }

        // check if object really has to move of only update status
        if ($remove === false) {
            return $this->update($id, ['deleted'=>true], 'array');
        } else {
            // remove the object from the repository or return error if something went wrong
            try {
                $this->om->remove($object);
                $this->om->flush();
                return true;
            } catch (Exception $e) {
                $this->setMessages($e->getMessage());
                return false;
            }
        }
    }

    public function saveCache($objectCacheFolder, $objectCacheFile, $data)
    {
        // Create cache-folder (if not exists)
        if (!is_dir($objectCacheFolder)) {
            mkdir($objectCacheFolder, 0775, true);
            chown($objectCacheFolder, "www-data");
            chgrp($objectCacheFolder, "www-data");
        }

        // Save result to cache-file
        file_put_contents($objectCacheFile, serialize($data));
        chmod($objectCacheFile, 0775);
        chown($objectCacheFile, "www-data");
        chgrp($objectCacheFile, "www-data");
    }
}