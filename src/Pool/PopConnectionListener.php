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

namespace W7\Tracer\Pool;

use W7\Core\Pool\Event\PopConnectionEvent;

class PopConnectionListener extends PoolListenerAbstract {
	public function run(...$params) {
		/**
		 * @var PopConnectionEvent $event
		 */
		$event = $params[0];
		$this->log($event);
	}

	protected function log($event) {
		itrace($event->type, $event->name . ' get by queue , count ' . $event->pool->getIdleCount());
	}
}