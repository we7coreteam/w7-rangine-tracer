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

use W7\Core\Database\Event\TransactionRolledBackEvent;

class TransactionRolledBackListener extends DatabaseListenerAbstract {
	public function run(...$params) {
		/**
		 * @var TransactionRolledBackEvent $event
		 */
		$event = $params[0];
		$this->log($event);
	}
}
