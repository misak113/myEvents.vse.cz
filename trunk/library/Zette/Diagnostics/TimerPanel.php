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

	protected static $style = '<style type="text/css">/* Zette debuger */ #nette-debug {z-index: 50000;}</style>'; // @todo obecně dát do Zette::Bar


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
	}

	/**
	 * @static
	 * @param string $name
	 * @return float
	 */
	public static function stop($name)
	{
		$last = self::$duration[] = array('name' => $name, 'time' => Debugger::timer($name));
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
		self::$times[] = array('name' => $name, 'time' => (microtime(TRUE) - \Nette\Diagnostics\Debugger::$time));
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
		self::$times[] = array('name' => $name, 'time' => (microtime(TRUE) - \Nette\Diagnostics\Debugger::$time));
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
			$return = '';
			foreach ($times as $value) {
				$name = $value['name'];
				$return .= "<tr><th>$name</th><td style='text-align: right;'>" . number_format(round($value['time'] * 1000, 1), 1) . " ms</td></tr>";
			}
			return $return;
		};
		$return = '<h1>Timers</h1>';
		$return .= '<div class="nette-inner" style="width: 500px;"><table width="100%">';
		if(self::$times) {
			$return .= '<thead><tr><td colspan=2>Time Left trace</td></tr></thead>';
			$return .= '<tbody>'.$render(self::$times).'</tbody>';
		}
		if(self::$duration) {
			$return .= '<thead><tr><td colspan=2>Durations</td></tr></thead>';
			$return .= '<tbody>'.$render(self::$duration).'</tbody>';
		}
		$return .= '</table></div>'.self::$style;
		return $return;
	}
}