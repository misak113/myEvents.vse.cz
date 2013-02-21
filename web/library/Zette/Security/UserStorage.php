<?php
namespace Zette\Security;

use Nette\Security\IUserStorage;
use Zend_Auth_Storage_Interface;
use Nette\Security\IIdentity;
use Nette\Http\Session;
use Nette\Http\UserStorage as NetteUserStorage;


/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 6.12.12
 * Time: 9:43
 * To change this template use File | Settings | File Templates.
 */
class UserStorage extends NetteUserStorage implements IUserStorage, Zend_Auth_Storage_Interface
{
	const UNKNOWN_REASON = 1;

	/** @var bool */
	protected $authenticated;
	/** @var IIdentity */
	protected $identity;
	/** @var \Nette\Http\SessionSection */
	protected $storage = null;

	public function __construct(Session $session) {
		$this->storage = $session->getSection('_Zette.Security.UserStorage');
	}

	/**
	 * Sets the authenticated status of this user.
	 * @param  bool
	 * @return void
	 */
	function setAuthenticated($state)
	{
		$this->storage['authenticated'] = (bool)$state;
	}

	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	function isAuthenticated()
	{
		return $this->storage['authenticated'];
	}

	/**
	 * Sets the user identity.
	 * @return void
	 */
	function setIdentity(IIdentity $identity = NULL)
	{
		$this->storage['identity'] = $identity;
	}

	/**
	 * Returns current user identity, if any.
	 * @return \Nette\Security\IIdentity|NULL
	 */
	function getIdentity()
	{
		return $this->storage['identity'];
	}

	/**
	 * Enables log out from the persistent storage after inactivity.
	 * @param  string|int|DateTime number of seconds or timestamp
	 * @param  int Log out when the browser is closed | Clear the identity from persistent storage?
	 * @return void
	 */
	function setExpiration($time, $flags = 0)
	{
		$this->storage->setExpiration($time, $flags);
	}

	/**
	 * Why was user logged out?
	 * @return int
	 */
	function getLogoutReason()
	{
		return self::UNKNOWN_REASON;
	}

	/**
	 * Returns true if and only if storage is empty
	 *
	 * @throws Zend_Auth_Storage_Exception If it is impossible to determine whether storage is empty
	 * @return boolean
	 */
	public function isEmpty()
	{
		return empty($this->storage);
	}

	/**
	 * Clears contents from storage
	 *
	 * @throws Zend_Auth_Storage_Exception If clearing contents from storage is impossible
	 * @return void
	 */
	public function clear()
	{
		$this->storage->remove();
	}

	/**
	 * Returns the contents of storage
	 *
	 * Behavior is undefined when storage is empty.
	 *
	 * @throws Zend_Auth_Storage_Exception If reading contents from storage is impossible
	 * @return mixed
	 */
	public function read()
	{
		return $this->storage['zend.contents'];
	}

	/**
	 * Writes $contents to storage
	 *
	 * @param  mixed $contents
	 * @throws Zend_Auth_Storage_Exception If writing $contents to storage is impossible
	 * @return void
	 */
	public function write($contents)
	{
		$this->storage['zend.contents'] = $contents;
	}
}
