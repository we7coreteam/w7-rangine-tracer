<?php

/**
 * Rangine debugger
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

use Symfony\Component\VarDumper\VarDumper;
use W7\App;
use W7\Contract\Logger\LoggerFactoryInterface;
use W7\Core\Exception\DumpException;

if (!function_exists('itrace')) {
	function itrace($group, $message, $context = []) {
		$context['trace_group'] = $group;
		App::getApp()->getContainer()->singleton(LoggerFactoryInterface::class)->channel('rangine-debugger')->debug($message, $context);
	}
}

if (!function_exists('idd')) {
	function idd(...$vars) {
		ob_start();

		$_SERVER['VAR_DUMPER_FORMAT'] = 'html';
		foreach ($vars as $var) {
			VarDumper::dump($var);
		}
		VarDumper::setHandler(null);

		$content = ob_get_clean();

		throw new DumpException($content);
	}
}
