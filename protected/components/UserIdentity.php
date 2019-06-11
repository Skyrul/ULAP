<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	private $_id;
	/**
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate($validatePassword = true)
	{
		$username = strtolower($this->username);
		
		// $account = Account::model()->find('LOWER(username=:username)', array(':username' => $username));
		
		$account = Account::model()->find(array(
			'condition' => 'LOWER(username=:username) AND status = 1',
			'params' => array(
				':username' => $username,
			),
		));
		
		if ($account === null)
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		else if ($validatePassword AND ( $account->password != $this->password ) )
		{
			if($account->account_type_id != Account::TYPE_ADMIN)
			{
				$account->login_attempt = $account->login_attempt + 1;
				$account->save(false);
			}
			
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		}
		else
		{
			$account->login_attempt = 0;
			$account->save(false);
			
			$this->_id = $account->id;
			$this->username = $account->username;
			$this->errorCode = self::ERROR_NONE;
		}
		return $this->errorCode == self::ERROR_NONE;
	}
	
	/**
	 * Override getId() method
	 */
	public function getId()
	{
		return $this->_id;
	}	
}