<?php

namespace W7\Tracer\Database;

use W7\Core\Database\Event\BeforeMakeConnectionEvent;
use const OpenTracing\Tags\DATABASE_TYPE;

class BeforeMakeConnectionListener extends DatabaseListenerAbstract {
	public function run(...$params) {
		/**
		 * @var BeforeMakeConnectionEvent $event
		 */
		$event = $params[0];
		$span = $this->getDatabaseSpan($event->name);
		$span->setTag(DATABASE_TYPE, 'database');
		$span->log(['make-connection']);
	}
}