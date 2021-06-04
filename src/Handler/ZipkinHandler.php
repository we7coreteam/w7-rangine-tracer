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

namespace W7\Tracer\Handler;

use OpenTracing\Tracer;
use W7\Tracer\Contract\HandlerInterface;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;

class ZipkinHandler implements HandlerInterface {
	public function make($name, array $options = []): Tracer {
		$endpoint = Endpoint::create(
			$name,
			$options['ipv4'] ?? '127.0.0.1',
			$options['ipv6'] ?? null,
			$options['port'] ?? '9411'
		);
		$reporter = $options['reporter'] ?? new Http();
		$sampler = $options['sampler'] ?? BinarySampler::createAsAlwaysSample();
		$tracing = TracingBuilder::create()
			->havingLocalEndpoint($endpoint)
			->havingSampler($sampler)
			->havingReporter($reporter)
			->build();

		return new \ZipkinOpenTracing\Tracer($tracing);
	}
}
