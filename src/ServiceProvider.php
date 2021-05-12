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

use W7\App;
use W7\Core\Cache\Event\MakeConnectionEvent;
use W7\Core\Database\Event\QueryExecutedEvent;
use W7\Core\Database\Event\TransactionBeginningEvent;
use W7\Core\Database\Event\TransactionCommittedEvent;
use W7\Core\Database\Event\TransactionRolledBackEvent;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Route\Event\RouteMatchedEvent;
use W7\Core\Server\ServerEvent;
use W7\Tracer\Cache\MakeConnectionListener;
use W7\Tracer\Database\QueryExecutedListener;
use W7\Tracer\Database\TransactionBeginningListener;
use W7\Tracer\Database\TransactionCommittedListener;
use W7\Tracer\Database\TransactionRolledBackListener;
use W7\Tracer\Database\MakeConnectionListener as MakeDatabaseConnectionListener;
use W7\Core\Database\Event\MakeConnectionEvent as MakeDatabaseConnectionEvent;
use W7\Core\Pool\Event\MakeConnectionEvent as PoolMakeConnectionEvent;
use W7\Tracer\Pool\MakeConnectionListener as PoolMakeConnectionListener;
use W7\Tracer\Request\AfterRequestListener;
use W7\Tracer\Request\BeforeRequestListener;
use W7\Tracer\Route\RouteMatchedListener;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\Reporters\Log;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;
use ZipkinOpenTracing\Tracer;

class ServiceProvider extends ProviderAbstract {
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		$this->registerLog();
		$this->registerListener();
	}

	private function registerLog() {
		if (!empty($this->config->get('log.channel.tracer'))) {
			return false;
		}
		$this->registerLogger('tracer', [
			'driver' => $this->config->get('handler.log.daily'),
			'path' => App::getApp()->getRuntimePath() . '/logs/tracer.log',
			'level' => 'debug',
			'days' => 1
		]);
	}

	private function registerListener() {
		$this->getEventDispatcher()->listen(ServerEvent::ON_USER_BEFORE_REQUEST, BeforeRequestListener::class);
		$this->getEventDispatcher()->listen(RouteMatchedEvent::class, RouteMatchedListener::class);
		$this->getEventDispatcher()->listen(MakeConnectionEvent::class, MakeConnectionListener::class);
		$this->getEventDispatcher()->listen(MakeDatabaseConnectionEvent::class, MakeDatabaseConnectionListener::class);
		$this->getEventDispatcher()->listen(QueryExecutedEvent::class, QueryExecutedListener::class);
		$this->getEventDispatcher()->listen(TransactionBeginningEvent::class, TransactionBeginningListener::class);
		$this->getEventDispatcher()->listen(TransactionCommittedEvent::class, TransactionCommittedListener::class);
		$this->getEventDispatcher()->listen(TransactionRolledBackEvent::class, TransactionRolledBackListener::class);
		$this->getEventDispatcher()->listen(PoolMakeConnectionEvent::class, PoolMakeConnectionListener::class);
		$this->getEventDispatcher()->listen(ServerEvent::ON_USER_AFTER_REQUEST, AfterRequestListener::class);
	}

	protected function getZipKinTracer() {
		$endpoint = Endpoint::create(
			$this->config->get('tracer.name', $this->name),
			$this->config->get('tracer.zipkin.host', '127.0.0.1'),
			null,
			$this->config->get('tracer.zipkin.port', '9411')
		);
		$logger = $this->getLogger()->channel('tracer');
		if ($this->config->get('tracer.zipkin.reporter') === 'http') {
			$reporter = new Http(
				[],
				null,
				$logger
			);
		} else {
			$reporter = new Log($logger);
		}
		$sampler = BinarySampler::createAsAlwaysSample();
		$tracing = TracingBuilder::create()
			->havingLocalEndpoint($endpoint)
			->havingSampler($sampler)
			->havingReporter($reporter)
			->build();

		return new Tracer($tracing);
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
	}
}
