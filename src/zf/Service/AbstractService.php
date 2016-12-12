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
    private $om;

    /**
     * @var \DoctrineModule\Stdlib\Hydrator\DoctrineObject
     */
    private $hydrator;

    /**
     * @var Name of the \Doctrine\ORM\EntityRepository
     */
    private $objectName;

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
            $data = [];
            foreach ($objects as $object) {
                $data[] = $this->getHydrator()->extract($object);
            }

            // return
            return $data;
        } else {
            return $objects;
        }
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
            $hydrator = $this->getHydrator();
            return $hydrator->extract($object);
        } else {
            return $object;
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
            foreach ($objects as $object) {
                $data[] = $this->getHydrator()->extract($object);
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

        // hydrate object, apply inputfilter, and save it
        if ($this->filterAndPersist($data, $object)) {
            if ($output == 'array') {
                return $this->getHydrator()->extract($object);
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

        // hydrate object, apply inputfilter, save it, and return result
        if ($this->filterAndPersist($data, $object)) {
            if ($output == 'array') {
                return $this->getHydrator()->extract($object);
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
     * @return array
     */
    public function delete($id)
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

        // return succes message
        return true;
    }

}