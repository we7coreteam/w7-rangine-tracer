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

namespace W7\Tracer\Database;

use Illuminate\Database\Events\ConnectionEvent;
use W7\Core\Listener\ListenerAbstract;

abstract class DatabaseListenerAbstract extends ListenerAbstract {
	/**
	 * @param ConnectionEvent $event
	 * @return  bool
	 */
	protected function log($event) {
		if ($event->connection->pretending()) {
			return true;
		}
		$sql = $event->sql ?? '';
		$bindings = (array) (empty($event->bindings) ? [] : $event->bindings);
		foreach ($bindings as $key => $binding) {
			// This regex matches placeholders only, not the question marks,
			// nested in quotes, while we iterate through the bindings
			// and substitute placeholders by suitable values.
			$regex = is_numeric($key)
				? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
				: "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";

			// Mimic bindValue and only quote non-integer and non-float data types
			if (!is_int($binding) && !is_float($binding)) {
				$binding = $event->connection->getActiveConnection()->quote($binding);
			}

			$sql = preg_replace($regex, $binding, $sql, 1);
		}
		itrace('database', 'connection ' . $event->connectionName . ', ' . $sql);
	}
}
