<?php
namespace app\models\authentication;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 17.12.12
 * Time: 11:32
 * To change this template use File | Settings | File Templates.
 */
interface IAuthenticateProvidesConstants
{

	const AUTHENTICATE_PROVIDE_EMAIL = 1;
	const AUTHENTICATE_PROVIDE_USER = 2;
	const AUTHENTICATE_PROVIDE_FACEBOOK = 3;
}
