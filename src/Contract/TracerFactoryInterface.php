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

namespace W7\Tracer\Contract;

use OpenTracing\Tracer;

interface TracerFactoryInterface {
	public function channel($name = 'default', array $options = []) : Tracer;
}
