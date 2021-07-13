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

namespace W7\Tracer;

use OpenTracing\Span;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Helper\Traiter\AppCommonTrait;
use W7\Tracer\Contract\TracerFactoryInterface;
use const OpenTracing\Formats\TEXT_MAP;
use const OpenTracing\Tags\SPAN_KIND;

trait TracerSpanTrait {
	use AppCommonTrait;

	protected function getTracer($name = '') {
		$contextKey = sprintf('opentracing.tracer.handler.%s', empty($name) ? 'default' : $name);
		if (!$tracer = $this->getContext()->getContextDataByKey($contextKey)) {
			/**
			 * @var TracerFactoryInterface $traceFactory
			 */
			$traceFactory = $this->getContainer()->get(TracerFactoryInterface::class);
			$tracer = $traceFactory->channel($name);
			$this->getContext()->setContextDataByKey($contextKey, $tracer);
			if (isCo()) {
				$this->getContext()->defer(function () use ($tracer) {
					try {
						$tracer->flush();
					} catch (\Throwable $e) {
						/**
						 * @var HandlerExceptions $exceptionHandler
						 */
						$exceptionHandler = $this->getContainer()->get(HandlerExceptions::class);
						$exceptionHandler->getHandler()->report($e);
					}
				});
			}
		}

		return $tracer;
	}

	/**
	 * @param string $spanName
	 * @param string $kind
	 * @return Span
	 */
	protected function getSpan(string $spanName = 'server', string $kind = 'server', $traceName = '') {
		$contextKey = 'opentracing.tracer.span.' . $spanName . '.' . $kind . '.' . $traceName;
		if (!$span = $this->getContext()->getContextDataByKey($contextKey)) {
			$span = $this->makeSpan($traceName, $spanName, $kind);
			$this->getContext()->setContextDataByKey($contextKey, $span);
		}

		return $span;
	}

	protected function finishSpan(Span $span) {
		$contextKey = 'opentracing.tracer.span.' . $span->getOperationName() . '.' . $span->getContext()->getBaggageItem('x-span-kind') . '.' . $span->getContext()->getBaggageItem('x-span-tracer');
		$this->getContext()->setContextDataByKey($contextKey, null);
		try {
			$span->finish();
		} catch (\Throwable $e) {
			/**
			 * @var HandlerExceptions $exceptionHandler
			 */
			$exceptionHandler = $this->getContainer()->get(HandlerExceptions::class);
			$exceptionHandler->getHandler()->report($e);
		}
	}

	protected function makeSpan($traceName, $spanName, $kind) {
		$tracer = $this->getTracer($traceName);
		$request = $this->getContext()->getRequest();

		$contextHeaders = [];
		if ($request) {
			$contextHeaders = $request->getHeaders();
		}
		foreach ($contextHeaders as &$header) {
			if (is_array($header)) {
				$header = rtrim(implode(';', $header), ';');
			}
		}
		$spanContext = $tracer->extract(TEXT_MAP, $contextHeaders);
		$scope = $tracer->startActiveSpan($spanName, ['child_of' => $spanContext]);
		$traceSpan = $scope->getSpan();
		$traceHeaders = [];
		$tracer->inject($traceSpan->getContext(), TEXT_MAP, $traceHeaders);
		$traceSpan->setTag(SPAN_KIND, $kind);
		$traceSpan->addBaggageItem('x-span-kind', $kind);
		$traceSpan->addBaggageItem('x-span-tracer', $traceName);
		if ($request) {
			foreach ($traceHeaders as $name => $header) {
				$request = $request->withHeader($name, $header);
			}
			$this->getContext()->setRequest($request);
		}

		return $traceSpan;
	}
}
