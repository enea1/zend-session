<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Session
 */

namespace Zend\Session\Storage;

use ArrayObject;

/**
 * Session storage in $_SESSION
 *
 * Replaces the $_SESSION superglobal with an ArrayObject that allows for
 * property access, metadata storage, locking, and immutability.
 *
 * @category   Zend
 * @package    Zend_Session
 * @subpackage Storage
 */
class SessionStorage extends ArrayStorage
{
    /**
     * Session save path.
     *
     * @var string
     */
    protected $savePath;
    
    /**
     * Constructor
     *
     * Sets the $_SESSION superglobal to an ArrayObject, maintaining previous
     * values if any discovered.
     *
     * @param  array|null $input
     * @param  int $flags
     * @param  string $iteratorClass
     */
    public function __construct($input = null, $flags = ArrayObject::ARRAY_AS_PROPS, $iteratorClass = '\\ArrayIterator')
    {
        $resetSession = true;
        if ((null === $input) && isset($_SESSION)) {
            $input = $_SESSION;
            if (is_object($input) && $_SESSION instanceof \ArrayObject) {
                $resetSession = false;
            } elseif (is_object($input) && !$_SESSION instanceof \ArrayObject) {
                $input = (array) $input;
            }
        } elseif (null === $input) {
            $input = array();
        }
        parent::__construct($input, $flags, $iteratorClass);
        if ($resetSession) {
            $_SESSION = $this;
        }
    }

    /**
     * Destructor
     *
     * Resets $_SESSION superglobal to an array, by casting object using
     * getArrayCopy().
     *
     * @return void
     */
    public function __destruct()
    {
        $_SESSION = (array) $this->getArrayCopy();
    }

    /**
     * Load session object from an existing array
     *
     * Ensures $_SESSION is set to an instance of the object when complete.
     *
     * @param  array $array
     * @return SessionStorage
     */
    public function fromArray(array $array)
    {
        parent::fromArray($array);
        if ($_SESSION !== $this) {
            $_SESSION = $this;
        }
        return $this;
    }

    /**
     * Mark object as isImmutable
     *
     * @return SessionStorage
     */
    public function markImmutable()
    {
        $this['_IMMUTABLE'] = true;
        return $this;
    }

    /**
     * Determine if this object is isImmutable
     *
     * @return bool
     */
    public function isImmutable()
    {
        return (isset($this['_IMMUTABLE']) && $this['_IMMUTABLE']);
    }
    
    /**
     * Returns the currently set session_save_path() value.  Sets the class 
     * property if the class property is stale.
     * 
     * @return string
     */
    public function getSavePath()
    {
        $savePath = session_save_path();
        if ($savePath != $this->savePath) {
            $this->setSavePath($savePath);
        }
        return $this->savePath;
    }

    /**
     * Sets the session_save_path() value and the class property.
     * 
     * @param string $savePath
     * @return \Zend\Session\Storage\SessionStorage
     */
    public function setSavePath($savePath)
    {
        session_save_path($savePath);
        $this->savePath = $savePath;
        return $this;
    }
}
