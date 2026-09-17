<?php
declare(strict_types=1);

namespace MarketForecast\Support;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

final class Logger
{
    private MonologLogger $logger;

    public function __construct(string $path, string $level = 'INFO')
    {
        $this->logger = new MonologLogger('market-forecast');
        $this->logger->pushHandler(new StreamHandler($path, MonologLogger::toMonologLevel($level)));
    }

    public function info(string $message, array $context = []): void { $this->logger->info($message, $context); }
    public function error(string $message, array $context = []): void { $this->logger->error($message, $context); }
}
