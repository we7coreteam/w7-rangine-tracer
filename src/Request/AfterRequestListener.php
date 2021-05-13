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
use W7\Http\Message\Server\Response;
use W7\Tracer\TracerSpanTrait;
use const OpenTracing\Tags\ERROR;
use const OpenTracing\Tags\HTTP_STATUS_CODE;

class AfterRequestListener extends ListenerAbstract {
	use TracerSpanTrait;

	public function run(...$params) {
		/**
		 * @var Response $response
		 */
		$response = $params[2];

		$span = $this->getSpanFromContext();
		$span->setTag(HTTP_STATUS_CODE, $response->getStatusCode());
		$span->setTag(ERROR, true);
		$span->finish();

		$this->getTracer()->flush();
	}
}
