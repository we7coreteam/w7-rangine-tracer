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
use W7\Tracer\TracerSpanTrait;

class MakeConnectionListener extends ListenerAbstract {
	use TracerSpanTrait;

	public function run(...$params) {
		/**
		 * @var MakeConnectionEvent $event
		 */
		$event = $params[0];
		$span = $this->getSpanFromContext('cache');
		$span->setTag('channel', $event->name);
		$span->finish();
	}
}
