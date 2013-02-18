<?php

/**
 * Debug panel for timing
 * use \Panel\TimerPanel\start('sql') ... \Panel\TimerPanel\stop('sql') for measuring time
 * use \Panel\TimerPanel\timeLeft('starting') for capture running time
 * @author AoJ
 */

namespace Zette\Diagnostics;

use Nette,
\Nette\Diagnostics\Debugger;

/**
 * slouží k profilování aplikace
 * buď použití TimerPanel::trackTime() k zachycení času v místě volání
 * nebo k měření doby přes metody ::start('x') ::stop('x')
 */
class TimerPanel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{

	/** @var array */
	private static $times = array();
	private static $duration = array();
	private static $memory = array();

	protected static $style = "<style type=\"text/css\">/* Zette debuger */
	#nette-debug {z-index: 50000;}
	#nette-debug-logo{background-image:url('data:image/png;base64,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') !important;}
	</style>"; // @todo obecně dát do Zette::Bar


	public static function getTraceTimes() {
		return self::$times;
	}

	public static function getDuration() {
		return self::$duration;
	}

	/**
	 * @static
	 * @param string $name
	 */
	public static function start($name)
	{
		Debugger::timer($name);
		self::startMemory($name);
	}

	/**
	 * @static
	 * @param string $name
	 * @return float
	 */
	public static function stop($name)
	{
		$memoryDiff = self::stopMemory($name);
		$last = self::$duration[] = array('name' => $name, 'time' => Debugger::timer($name), 'memory' => $memoryDiff);
		return $last['time'];
	}
	/**
	 * @static
	 * @param string $name
	 * @return float
	 */
	public static function endGroup($name)
	{
		if(!isset(self::$duration[$name])) self::$duration[$name] = array('name' => $name, 'time' => 0, 'times' => 0);

		self::$duration[$name]['time'] += Debugger::timer($name);
		self::$duration[$name]['name'] = $name . ' ('.(++self::$duration[$name]['times']).'×)';
		return self::$duration[$name]['time'];
	}

	/**
	 * @static
	 * @param string $name
	 */
	public static function timeLeft($name)
	{
		self::$times[] = array('name' => $name, 'time' => (microtime(TRUE) - \Nette\Diagnostics\Debugger::$time), 'memory' => self::startMemory($name));
	}

	protected static function startMemory($name) {
		return self::$memory[$name] = memory_get_usage(true);
	}
	protected static function stopMemory($name) {
		if (isset(self::$memory[$name])) {
			$diff = memory_get_usage(true) - self::$memory[$name];
			unset(self::$memory[$name]);
			return $diff;
		}
		return false;
	}


	/**
	 * @static
	 * @param string $name
	 */
	public static function traceTime($description = null)
	{
		$bt = array_slice(debug_backtrace(false), 0, 5);
        foreach($bt as $trace) {
            if ($trace['function'] !== __FUNCTION__) {
                $caller = $trace;
                break;
            }
        }
		if(!$caller) {
			Debugger::barDump($bt, 'caller not found');
			return;
		}

		$fields = array('class', 'function', 'line', 'file');
		foreach($fields as &$field) {
			if(!isset($caller[$field])) $caller[$field] = '';
		}
		$name = $caller['class'] ? "{$caller['class']}->{$caller['function']}" : '';
		if($description) $name .= ($name ? ' - ' : '') . $description;
		self::$times[] = array('name' => $name, 'time' => (microtime(TRUE) - \Nette\Diagnostics\Debugger::$time), 'memory' => self::startMemory($name));
	}

	/**
	 * @return string
	 */
	public function getTab()
	{
		$count = count(self::$times) + count(self::$duration);
		return '<span>'
				. '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAHpSURBVHjajJOxa1NRFMZ/ea+DNNAkpIQYooOJi4gQCBmDuGVUyJ5FsvhnFNx1Emf7wCnQpItIklZp1SwxNTpEoo90CBqMSWnq67vvOHgfPNNS+sGFew/n+75z7zkXzuIm8BjYB46AY6ALPAFyXAAT2AD+AAI4wGfgkxYSvZ4BkWXyCvBCJ0wsy9pyXdcWEURElFI/Op3OTjabtXVOE1gNCmwAEo/H3zuOM5R/UMBmu91uiR9QalKpVN5pEcsn3wBOgZ/aVUREHMcZAlIoFL7I/1jkcrmBFrlrAA+BFcuy9kzTvLZ8t7W1tdOl0JVarTbR+0cAH4Bj13UPgzZ+BaVSqbtUgXieN43FYlPgmwHcAr6bppk6pzOq3++vNxqNVr1ebw8Gg32AUCgUKRaLQ+AqwAI4WHZxXfcQmAXaJ6lUaiwiJyIi5XK5AzghoAdcV0o5hmGsB+3n83l3Npsd+edoNBoJh8O3RWRmGMYCcAGeAtJsNrflkhiNRm90VdsAdwAPGHqeN7kEXyUSCX8W7vvVPQckn883Pc/7fQHZqVardU1uAYYvENHjKcBBr9d7pZQaB9r2y7btnWQyuadzPgJnurYKbAZefZpOp99mMpldYByIvz6PHMQ94CXwFTjRv9MGtoAHQCiY/HcA4TCdApPucwYAAAAASUVORK5CYII=" />'
				.'<strong>Timers</strong> (' . $count . ')</span>';
	}


	/**
	 * @return string
	 */
	public function getPanel()
	{
		if(!self::$times && !self::$duration) return '';
		$render = function(&$times) {
			$totalTime = (microtime(TRUE) - Debugger::$time);
			$return = '';
			foreach ($times as $value) {
				$name = $value['name'];
				$return .= "<tr><th>$name</th><td style='text-align: right;'>" . number_format(round($value['time'] * 1000, 1), 1) . " ms (".round($value['time']/$totalTime*100, 2)."%)</td>
				<td>".(isset($value['memory']) && $value['memory'] ?TimerPanel::formatMemory($value['memory']) :'')."</td>
				</tr>";
			}
			return $return;
		};
		$return = '<h1>Timers</h1>';
		$return .= '<div class="nette-inner" style="width: 500px;"><table width="100%">';
		if(self::$times) {
			$return .= '<thead><tr><td colspan=3>Time Left trace</td></tr></thead>';
			$return .= '<tbody>'.$render(self::$times).'</tbody>';
		}
		if(self::$duration) {
			$return .= '<thead><tr><td colspan=3>Durations</td></tr></thead>';
			$return .= '<tbody>'.$render(self::$duration).'</tbody>';
		}
		$return .= '</table></div>'.self::$style;
		return $return;
	}

	public static function formatMemory($memory) {
		$max = 1024;
		$mem = $memory;
		$ui = 0;
		$units = array('', 'K', 'M', 'G', 'T', 'P');
		while ($mem > $max) {
			$mem = $mem / $max;
			$ui++;
		}
		$unit = isset($units[$ui]) ?' '.$units[$ui] :'×10^'.($ui*3).' ';

		return $mem.$unit.'B';
	}
}