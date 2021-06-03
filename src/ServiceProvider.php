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

use W7\Core\Cache\Event\AfterMakeConnectionEvent;
use W7\Core\Cache\Event\BeforeMakeConnectionEvent;
use W7\Core\Database\Event\QueryExecutedEvent;
use W7\Core\Database\Event\TransactionBeginningEvent;
use W7\Core\Database\Event\TransactionCommittedEvent;
use W7\Core\Database\Event\TransactionRolledBackEvent;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Server\ServerEvent;
use W7\Tracer\Cache\AfterMakeConnectionListener;
use W7\Tracer\Cache\BeforeMakeConnectionListener;
use W7\Tracer\Contract\TracerFactoryInterface;
use W7\Tracer\Database\QueryExecutedListener;
use W7\Tracer\Database\TransactionBeginningListener;
use W7\Tracer\Database\TransactionCommittedListener;
use W7\Tracer\Database\TransactionRolledBackListener;
use W7\Tracer\Handler\ZipkinHandler;
use W7\Tracer\Request\AfterRequestListener;
use W7\Tracer\Request\BeforeRequestListener;

class ServiceProvider extends ProviderAbstract {
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		if (empty($this->config->get('opentracing.enable'))) {
			return;
		}

		$this->registerTracerResolver();
		$this->registerListener();
	}

	private function registerTracerResolver() {
		$this->container->set(TracerFactoryInterface::class, function () {
			$tracerFactory = new TracerFactory();
			$tracerFactory->setDefaultChannel($this->config->get('opentracing.default', 'default'));

			$tracerMap = $this->config->get('opentracing.tracer', []);
			foreach ($tracerMap as $name => $tracer) {
				$handler = $tracer['handler'] ?? ZipkinHandler::class;
				$options = $tracer['options'] ?? [];
				$tracerFactory->registerTracerResolver($name, function ($name, array $replenishOptions = []) use ($handler, $options) {
					$options = array_merge($replenishOptions, $options);
					$handler = new $handler();
					return $handler->make($name, $options);
				});
			}

			return $tracerFactory;
		});
	}

	private function registerListener() {
		$this->getEventDispatcher()->listen(ServerEvent::ON_USER_BEFORE_REQUEST, BeforeRequestListener::class);
		$this->getEventDispatcher()->listen(BeforeMakeConnectionEvent::class, BeforeMakeConnectionListener::class);
		$this->getEventDispatcher()->listen(AfterMakeConnectionEvent::class, AfterMakeConnectionListener::class);
		$this->getEventDispatcher()->listen(\W7\Core\Database\Event\BeforeMakeConnectionEvent::class, \W7\Tracer\Database\BeforeMakeConnectionListener::class);
		$this->getEventDispatcher()->listen(\W7\Core\Database\Event\AfterMakeConnectionEvent::class, \W7\Tracer\Database\AfterMakeConnectionListener::class);
		$this->getEventDispatcher()->listen(QueryExecutedEvent::class, QueryExecutedListener::class);
		$this->getEventDispatcher()->listen(TransactionBeginningEvent::class, TransactionBeginningListener::class);
		$this->getEventDispatcher()->listen(TransactionCommittedEvent::class, TransactionCommittedListener::class);
		$this->getEventDispatcher()->listen(TransactionRolledBackEvent::class, TransactionRolledBackListener::class);
		$this->getEventDispatcher()->listen(ServerEvent::ON_USER_AFTER_REQUEST, AfterRequestListener::class);
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
	}
}
