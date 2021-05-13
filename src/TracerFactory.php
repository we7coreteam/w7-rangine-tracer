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

use OpenTracing\Tracer;
use W7\Tracer\Contract\TracerFactoryInterface;

class TracerFactory implements TracerFactoryInterface {
	protected $defaultChannel = 'default';
	protected $tracerResolverMap = [];

	public function registerTracerResolver($name, \Closure $closure) {
		$this->tracerResolverMap[$name] = $closure;
	}

	public function setDefaultChannel($default) {
		$this->defaultChannel = $default;
	}

	public function channel($name = '', array $options = []): Tracer {
		$name = empty($name) ? $this->defaultChannel : $name;
		if (empty($this->tracerResolverMap[$name])) {
			throw new \RuntimeException('tracer ' . $name . ' not exists');
		}

		$resolver = $this->tracerResolverMap[$name];
		return $resolver($options);
	}
}
