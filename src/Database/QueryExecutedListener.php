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

use W7\Core\Database\Event\QueryExecutedEvent;
use const OpenTracing\Tags\DATABASE_STATEMENT;

class QueryExecutedListener extends DatabaseListenerAbstract {
	public function run(...$params) {
		/**
		 * @var QueryExecutedEvent $event
		 */
		$event = $params[0];

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

		$span = $this->getSpan($event->connectionName);
		$span->setTag(DATABASE_STATEMENT, $sql);

		if ($event->connection->transactionLevel() === 0) {
			$this->finishSpan($span);
		}
	}
}
