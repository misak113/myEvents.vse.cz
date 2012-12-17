<?php
namespace app\plugins;

use Nette\Security\IAuthenticator;

use Nette\Object;
use Nette\Security;
use app\models\authentication\UserTable;
use app\models\authentication\AuthenticateTable;
use Nette\Security\Identity;
use app\services\User;
use app\models\authentication\User as DbUser;
use app\models\authentication\IAuthenticateProvidesConstants;

/**
 * Users authenticator.
 */
class FacebookAuthenticator implements IAuthenticator, IAuthenticateProvidesConstants
{

	protected $id;

	/** @var UserTable */
	protected $userTable;

	/** @var AuthenticateTable */
	protected $authenticateTable;
	/** @var User */
	protected $user;


	/**
	 *
	 * @param UserTable $usersTable
	 * @param AuthenticateTable $userAuthenticates
	 */
	public function setContext(UserTable $userTable, AuthenticateTable $authenticateTable, User $user)
	{
		$this->userTable = $userTable;
		$this->authenticateTable = $authenticateTable;
		$this->user = $user;
	}


	/**
	 * @param array $fbIdentity
	 * @return \Nette\Security\Identity
	 * 1. find by UID
	 * 2. find by email
	 * 3. update if find
	 * 4. create new if not exists
	 */
	public function authenticate(array $fbIdentity)
	{
		//load by FB UID authenticator
		$authenticate = $this->authenticateTable->getByIdentity($fbIdentity['id'], self::AUTHENTICATE_PROVIDE_FACEBOOK);

		if (!$authenticate) {
			$authenticate = $this->register($fbIdentity);
		}

		$user = $this->userTable->getById($authenticate->getUserId());

		$this->updateUserData($user, $fbIdentity);

		$authenticateData = $authenticate->toArray();
		$authenticateData['user'] = $user->toArray();

		// Uživatel byl úspěšně ověřen a je přihlášen
		$this->updateUserData($user, $fbIdentity);
		$roles = array();
		foreach ($user->getRoles() as $role) {
			$roles[] = $role->getUriCode();
		}

		return new Identity($authenticate->getUserId(), $roles, $authenticateData);
	}


	protected function register(array $fbIdentity)
	{

		if ($this->user->isLoggedIn()) {
			$userId = $this->user->getId();
		} else {
			$user = $this->userTable->createRow(array(
				'email' => $fbIdentity['email'],
				'first_name' => $fbIdentity['first_name'],
				'last_name' => $fbIdentity['last_name'],
			));
			try {
				$user->save();
				$userId = $user->getUserId();
			} catch (\Zend_Db_Statement_Exception $e) {
				if ($e->getCode() == 23000) {
					$user = $this->userTable->getByEmail($fbIdentity['email']);
					$userId = $user->getUserId();
				} else {
					throw $e;
				}
			}
		}

		$authenticate = $this->authenticateTable->createRow(array(
			'identity' => $fbIdentity['id'],
			'verification' => null,
			'authenticate_provides_id' => self::AUTHENTICATE_PROVIDE_FACEBOOK,
			'user_id' => $userId,
			'active' => 1,
			'created' => date('Y-m-d H:i:s'),
		));
		$authenticate->save();

		return $authenticate;
	}


	protected function updateUserData(DbUser $user, array $me)
	{
		$updateData = array();

		if (empty($user['first_name'])) {
			$updateData['first_name'] = $me['first_name'];
			$updateData['last_name'] = $me['last_name'];
		}

		if (!empty($updateData)) {
			$this->userTable->update($updateData, array('user_id = ?' => $user->getUserId()));
		}
	}

}