<?php
namespace Zette\Git;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 24.2.13
 * Time: 13:37
 * To change this template use File | Settings | File Templates.
 */
class Helper
{


	/**
	 * dost prasácké řešení, jak získat aktuální branch
	 * je to hlavně kvůli jednoduchém načtení configu podle aktuální branche
	 * možná by nebylo špatné udělat GitExtension, která by tohle vše zajišťovala pod testy
	 *
	 * @proofOfConcept
	 * @return bool|string
	 */
	public static function parseRawGitDirectoryAndGetCurrentBranch() {

		//nejprve zkusí podle nastaveného env
		if($branch = self::getenvIssue('REVISION_TAG')) return $branch;

		$dir = __DIR__;
		$maxDepth = 7;

		do {
			$file = $dir . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'HEAD';
			if(is_file($file)) {
				$gitHead = explode("/", str_replace("\n", "", file_get_contents($file)));
				$currentBranch = end($gitHead);
				return $currentBranch;
			}
		} while(($dir = dirname($dir)) && --$maxDepth > 0 );
		return false;
	}


	/**
	 * vrací env proměnné
	 * opravuje chování v případě, kdy je nastavena env v htaccess v rewriterule
	 * @param $varname
	 * @return bool|string
	 */
	public static function getenvIssue($varname) {
		$value = getenv($varname);
		if($value) return $value;

		//zkusí najít env v $_SERVER, via http://www.php.net/manual/en/reserved.variables.php#79811
		foreach($_SERVER as $key => &$value) {
			if($key === "REDIRECT_$varname") return $value;
		}

		return false;
	}


}
