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

use W7\Core\Listener\ListenerAbstract;
use W7\Tracer\TracerSpanTrait;

abstract class DatabaseListenerAbstract extends ListenerAbstract {
	use TracerSpanTrait;

	public function getDatabaseSpan($connectionName) {
		return $this->getSpan('database-' . $connectionName);
	}
}
