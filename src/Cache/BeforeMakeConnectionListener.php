<?php

namespace W7\Tracer\Cache;

use W7\Core\Cache\Event\BeforeMakeConnectionEvent;
use W7\Core\Listener\ListenerAbstract;
use W7\Tracer\TracerSpanTrait;
use const OpenTracing\Tags\DATABASE_TYPE;

class BeforeMakeConnectionListener extends ListenerAbstract {
	use TracerSpanTrait;

	public function run(...$params) {
		/**
		 * @var BeforeMakeConnectionEvent $event
		 */
		$event = $params[0];
		$span = $this->getSpan('cache-' . $event->name);
		$span->setTag(DATABASE_TYPE, 'redis');
		$span->log(['make-connection']);
	}
}