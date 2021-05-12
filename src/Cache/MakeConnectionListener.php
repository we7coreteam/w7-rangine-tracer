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

namespace W7\Tracer\Cache;

use W7\Core\Cache\Event\MakeConnectionEvent;
use W7\Core\Listener\ListenerAbstract;

class MakeConnectionListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var MakeConnectionEvent $event
		 */
		$event = $params[0];
		$this->log($event);
	}

	protected function log(MakeConnectionEvent $event) {
		itrace('cache', 'create ' . $event->name . ' connection');
	}
}
