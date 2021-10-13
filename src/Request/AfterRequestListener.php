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

use W7\Core\Exception\HandlerExceptions;
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
		$response = $params[1];

		$span = $this->getSpan('request');
		$responseCode = $response->getStatusCode();
		$span->setTag(HTTP_STATUS_CODE, $responseCode);
		$span->log(['finish-request']);
		if ($responseCode > 400) {
			$span->setTag(ERROR, $response->getBody()->getContents());
		}
		$this->finishSpan($span);

		if (!isCo()) {
			try {
				$this->getTracer()->flush();
			} catch (\Throwable $e) {
				/**
				 * @var HandlerExceptions $exceptionHandler
				 */
				$exceptionHandler = $this->getContainer()->get(HandlerExceptions::class);
				$exceptionHandler->getHandler()->report($e);
			}
		}
	}
}
