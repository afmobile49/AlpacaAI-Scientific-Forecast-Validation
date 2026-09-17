<?php
declare(strict_types=1);

namespace MarketForecast\Database;

use PDO;

final class Seeder
{
    public function __construct(private readonly PDO $pdo) {}

    public function seedAssets(): int
    {
        $assets = [
            ['SPY','SPDR S&P 500 ETF','STOCK','ALPACA','SPY','NYSE','America/New_York',0],
            ['QQQ','Invesco QQQ Trust','STOCK','ALPACA','QQQ','NASDAQ','America/New_York',0],
            ['AAPL','Apple Inc.','STOCK','ALPACA','AAPL','NASDAQ','America/New_York',0],
            ['MSFT','Microsoft Corp.','STOCK','ALPACA','MSFT','NASDAQ','America/New_York',0],
            ['NVDA','NVIDIA Corp.','STOCK','ALPACA','NVDA','NASDAQ','America/New_York',0],
            ['BTC/USD','Bitcoin / USD','CRYPTO','ALPACA','BTC/USD','CRYPTO','UTC',1],
            ['ETH/USD','Ethereum / USD','CRYPTO','ALPACA','ETH/USD','CRYPTO','UTC',1],
            ['XAUT/USD','Tokenized Gold / USD','TOKENIZED_GOLD','XAUT_PROVIDER','XAUT/USD','TOKENIZED_GOLD','UTC',1],
        ];
        $sql = 'INSERT OR IGNORE INTO assets(symbol,display_name,asset_type,provider,provider_symbol,exchange,timezone,is_24_7,enabled) VALUES (?,?,?,?,?,?,?,?,1)';
        $statement = $this->pdo->prepare($sql); $count = 0;
        foreach ($assets as $asset) { $statement->execute($asset); $count += $statement->rowCount(); }
        return $count;
    }
}
