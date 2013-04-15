<?php
namespace Zette\Diagnostics;

use DOMDocument;
use Nette\Utils\Strings;
use Nette\Http\Request;
use DateTime;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 28.12.12
 * Time: 22:33
 * To change this template use File | Settings | File Templates.
 */
class ExceptionLogPanel extends \Nette\Object implements \Nette\Diagnostics\IBarPanel, \Zette\Caching\ICacheable {

	/** @var array */
	private $exceptions;

	protected $httpRequest;
	/** @var \Zette\Caching\ClassCache */
	protected $classCache;

	public function __construct(Request $httpRequest, \Zette\Caching\ClassCache $classCache) {
		$this->classCache = $classCache;
		$this->httpRequest = $httpRequest;

		\Zette\Diagnostics\TimerPanel::start('construct ExceptionLogPanel');
		// @todo to je shit
		if ($exc = $this->getExceptionByUrl((string)$this->httpRequest->url)) {
			echo $exc['source'];
			die();
		}
		if (preg_match('~-resolve$~', $this->httpRequest->url) && $exc = $this->getExceptionByUrl(str_replace('-resolve', '', $this->httpRequest->url))) {
			$this->resolve($exc);
			echo 'exception resolved (wait to go back) <script>history.go(-1);</script>';
			die();
		}
		\Zette\Diagnostics\TimerPanel::stop('construct ExceptionLogPanel');

	}

	protected function resolve($exc) {
		@rename($exc['filePath'], $exc['filePath'].'-resolved');
	}

	public function getExceptions() {
		if (!$this->exceptions) {
			$this->exceptions = array();

			$dir = LOG_DIR;
			$files = @scandir($dir);
			if (!$files) {
				_dBar('Nebyla nalezena složka s Logy');
				return $this->exceptions;
			}
			foreach ($files as $file) {
				\Zette\Diagnostics\TimerPanel::start('exception file ExceptionLogPanel');
				if (!preg_match('~^exception-.*\.html$~', $file)) continue;
				$path = $dir.'/'.$file;
				$date = DateTime::createFromFormat('Y-m-d-H-i-s', substr($file, 10, 19));

				$dom = new DOMDocument();
				$source = @file_get_contents($path);
				$source = str_replace('<!-- "\' --></script></style></pre></xmp></table>', '', $source);
				$dom->loadHTML($source);
				$blueScreen = $dom->getElementById('netteBluescreenError');
				$name = $blueScreen->firstChild->nextSibling->textContent;
				$description = str_replace('search►', '', $blueScreen->firstChild->nextSibling->nextSibling->nextSibling->textContent);
				$this->exceptions[] = array(
					'url' => $this->httpRequest->url->baseUrl.'log/'.$file,
					'name' => $name,
					'description' => $description,
					'source' => $source,
					'filePath' => $path,
					'date' => $date,
				);
				\Zette\Diagnostics\TimerPanel::stop('exception file ExceptionLogPanel');
			}
		}
		return $this->exceptions;
	}

	public function getExceptionByUrl($url) {


		$excs = $this->classCache->getCachedInstance($this)->getExceptions();
		foreach ($excs as $exc) {
			if ($exc['url'] == $url) {
				return $exc;
			}
		}
		return false;
	}

	/**
	 * Renders HTML code for custom tab.
	 * @return string
	 */
	function getTab()
	{
		$count = count($this->getExceptions());
		return '<span>'
				. '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACNElEQVQ4jXVTzWoTURQ+83PHTCbNTAMpsSQQ6QM4oNiVJIIbN76AIugLNI8SH6M7dSEYF6ZbBclKF7ZKE4SplWYSkrmTSeeO59z5aav1wAf33nO+838V+I98vO12hRAuAmIhRve/fhleZ6dcvny+c9dBQg+xpzLmKIYBIo4h4gGC+/j+UsSi//DHd1+SFeXCQUb+wDY23EqzCYZlASA5QcD5OQT+FH6PJ7CYzUZo9+DRZOwXDnJyqV53nZ0dSfQ9D5bLJSRJAqaug2Pb0tnPoyOYnv6STh57nq+SA0qZVasFmQwZEu4N3sHu+wEYm5uQYC/ofbvVArNsUW/2iJs76FHakBlJrFYZQhAIcpzr61tb1NgecXXsdkc1DOdGpZLWi0ZkLMIQEgLe5ZkZhd4yTdA13dm3nY6K0bsKY6kyjy5JHATn0ong4RU9NgYYcoirYyqgZVFzI5nBKkpLQGMRRQCl0kWGadmIGHQ8DKNgKaMWGSBiHNGnJ0/TKWhaqs/IJCFmiMGHKm7YQRRwn5+dFU4IGo5u9/UrCTrL90zm8zkEuFjPOD/Ip9A/PT5OM8gngcsjaw+z+i/JeDKWnGKVB+1bNpVSrdXc7XZbEta4RCeLBSSor2EGJvaA5NvhIXgnHi1S98V6PStW+W2zhU7ioWlZbr1xEyrlssyCOp6nTZGnvi/Jz6NoduUvkLxpNOw4/Uw9mjNjuuw2xzJ4+pn6BCL/85n+ln3b7tB3juW4xIgadp3dH7SByCHes+VpAAAAAElFTkSuQmCC" />'
				.'<strong>Logs</strong> (' . $count . ')</span>';
	}

	/**
	 * Renders HTML code for custom panel.
	 * @return string
	 */
	function getPanel()
	{
		$excs = $this->getExceptions();
		if(!$excs) return '';
		$render = function(&$excs) {
			uasort($excs, function ($a, $b) {
				return $a['date']->format('U') < $b['date']->format('U') ?1 :-1;
			});
			$return = '';
			foreach ($excs as $value) {
				$url = $value['url'];
				$name = $value['name'];
				$desc = $value['description'];
				$date = $value['date'];
				$return .= '<tr><td><b style="font-weight: bold;">'.$date->format('j.n.').'</b> <i style="font-style: italic;">' . $date->format('G:i').'</i></td>
					<td><a href="'.$url.'" target="_blank">'.$name.'</a></td><td title="'.Strings::normalize($desc).'">' . Strings::truncate($desc, 200) . '</td>
					<td><a href="'.$url.'-resolve"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAK3RFWHRDcmVhdGlvbiBUaW1lAERpIDMwIFNlcCAyMDAzIDIzOjU0OjI2ICswMTAwZdX/wQAAAAd0SU1FB9MJHhU2OF5bzowAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAEZ0FNQQAAsY8L/GEFAAACcElEQVR42q1TXUgUURg94/7goutC/iRoGT1YZphZoIRhBBUZsRRBVr5V0EM9RfTSU9BLiNRLRA9FJPQgBD2IhtpKFga57mq20CL+sGsotauz4+7O3Jl7b98uuaztq/Mw9873fefMOWfuANt9BYD2MaBvCmj+v/cZuD6kYGQAOHsfKCoAzwNPoxePCfPhZRlUlE/5vUngTKi+gsmr9TLS5JFDNiWQqW9hkbXl3prOZsXuBHaeOnDcD3Rn6rS2yBpPf0NHrQNpA6zEAYNLfwFBPBrrNxdXAMNAdeseWOUlvcPASVZZNnjU2+CGugHNMBGaikftwN0CgjLg9dLYrISkB53hsLexsrjCPdLW1VQFNQHBLcx8V4XbEDfOAWoBwT5gNjYxNw7LBEwLDpcD7V0HFag0y02EIxoSkeSzE8CHTUxBkmkuehbHwwRgWStIJImM4Xc8hR/fYkENuJc/v4WAkq5NA3eKPS4CkQrGsuDsPm3CklgjgC0fo2yuX4Gb2LXj8ZHzjR4714EUUVnWPxIikBzxdR2+L/HJMgHvaeBXjuAjcKGute7d3rYaYC1BQE4WONlmsFFwwqR9piYFdN2Ez6+tpFOiuwsYzVpYp0SFSX7/xCl9Pes9sZHEwPCyNb+k0Vs4YTkJsmBXJDpaiqtJ34NcBg6b0ru7rpR8prPgVTUJ39hqTGpmRyCoPl+IUl0ISFLBSNnkPAOdtZcZrD1zS3E5yrWNQ4ZLQZiGf06r0yVCXuoE5qg98X5Gs9h+1+0Mam6ZwYjxJ9eAN7kM+oCKUmfRoLArVXqKv6AUem6Rlvy03wKP6BM63cCrK0Bo2/7ev40+Kr/ztfp4AAAAAElFTkSuQmCC" alt="delete" /></a></td>
				</tr>';
			}
			return $return;
		};
		$return = '<h1>Log Exceptions</h1>';
		$return .= '<div class="nette-inner" style="width: 900px;"><table width="100%">';
		if($excs) {
			$return .= '<thead><tr><th>Date</th></th><th>Exception</th><th>Message</th><th>Resolve</th></tr></thead>';
			$return .= '<tbody>'.$render($excs).'</tbody>';
		}
		$return .= '</table></div>';
		return $return;
	}
}