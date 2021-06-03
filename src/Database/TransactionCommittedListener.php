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

use W7\Core\Database\Event\TransactionCommittedEvent;

class TransactionCommittedListener extends DatabaseListenerAbstract {
	public function run(...$params) {
		/**
		 * @var TransactionCommittedEvent $event
		 */
		$event = $params[0];

		$span = $this->getSpan($event->connectionName);
		$span->log(['transaction-commit:level' . $event->connection->transactionLevel()]);

		if ($event->connection->transactionLevel() === 0) {
			$this->finishSpan($span);
		}
	}
}
