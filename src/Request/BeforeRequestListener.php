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

namespace W7\Tracer\Request;

use W7\Core\Listener\ListenerAbstract;
use W7\Http\Message\Server\Request;
use W7\Tracer\TracerSpanTrait;

class BeforeRequestListener extends ListenerAbstract {
	use TracerSpanTrait;

	public function run(...$params) {
		/**
		 * @var Request $request
		 */
		$request = $params[0];

		$span = $this->getSpan('request');
		$span->setTag('protocol', $params[2]);
		$span->setTag('coroutine.id', $this->getContext()->getCoroutineId());
		$span->setTag('request.path', $request->getUri()->getPath());
		$span->setTag('request.method', $request->getMethod());
		$span->log(['begin-request']);
	}
}
