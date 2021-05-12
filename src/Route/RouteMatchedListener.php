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

namespace W7\Tracer\Route;

use W7\Core\Listener\ListenerAbstract;
use W7\Core\Route\Event\RouteMatchedEvent;
use W7\Core\Route\Route;

class RouteMatchedListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var RouteMatchedEvent $event
		 */
		$event = $params[0];

		$this->log($event);
	}

	protected function log(RouteMatchedEvent $event) {
		if ($event->route instanceof Route) {
			$routeName = $event->route->name;
			$routeMiddleware = $event->route->getMiddleware();
			$routeModule = $event->route->module;
			$routeHandler = $event->route->handler instanceof \Closure ? 'closure' : implode('@', $event->route->handler);
		} else {
			$routeName = $event->route['name'];
			$routeMiddleware = $event->route['middleware'];
			$routeModule = $event->route['module'];
			$routeHandler = $event->route['controller'] instanceof \Closure ? 'closure' : $event->route['controller'] . '@' . $event->route['method'];
		}

		$middleWares = [];
		array_walk_recursive($routeMiddleware, function ($middleware) use (&$middleWares) {
			$middleWares[] = $middleware;
		});

		itrace('route', 'name: ' . $routeName . ', module: ' . $routeModule . ', handler: ' . $routeHandler);
		itrace('middleware', implode(',', $middleWares));
	}
}
