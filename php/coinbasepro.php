<?php

namespace ccxtpro;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception; // a common import

class coinbasepro extends \ccxt\coinbasepro {

    use ClientTrait;

    public function describe () {
        return array_replace_recursive(parent::describe (), array(
            'has' => array(
                'ws' => true,
                'watchOHLCV' => false, // missing on the exchange side
                'watchOrderBook' => true,
                'watchTicker' => true,
                'watchTickers' => false, // for now
                'watchTrades' => true,
                'watchBalance' => false,
                'watchStatus' => false, // for now
            ),
            'urls' => array(
                'api' => array(
                    'ws' => 'wss://ws-feed.pro.coinbase.com',
                ),
            ),
        ));
    }

    public function subscribe ($name, $symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $messageHash = $name . ':' . $market['id'];
        $url = $this->urls['api']['ws'];
        $subscribe = array(
            'type' => 'subscribe',
            'product_ids' => [
                $market['id'],
            ],
            'channels' => array(
                $name,
            ),
        );
        $request = array_merge($subscribe, $params);
        return $this->watch ($url, $messageHash, $request, $messageHash);
    }

    public function watch_ticker ($symbol, $params = array ()) {
        $name = 'ticker';
        return $this->subscribe ($name, $symbol, $params);
    }

    public function watch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $name = 'matches';
        $future = $this->subscribe ($name, $symbol, $params);
        return $this->after ($future, $this->filterBySinceLimit, $since, $limit);
    }

    public function watch_order_book ($symbol, $limit = null, $params = array ()) {
        $name = 'level2';
        $future = $this->subscribe ($name, $symbol, $params);
        return $this->after ($future, array($this, 'limit_order_book'), $symbol, $limit, $params);
    }

    public function limit_order_book ($orderbook, $symbol, $limit = null, $params = array ()) {
        return $orderbook->limit ($limit);
    }

    public function handle_trade ($client, $message) {
        //
        //     {
        //         $type => 'match',
        //         trade_id => 82047307,
        //         maker_order_id => '0f358725-2134-435e-be11-753912a326e0',
        //         taker_order_id => '252b7002-87a3-425c-ac73-f5b9e23f3caf',
        //         side => 'sell',
        //         size => '0.00513192',
        //         price => '9314.78',
        //         product_id => 'BTC-USD',
        //         sequence => 12038915443,
        //         time => '2020-01-31T20:03:41.158814Z'
        //     }
        //
        $marketId = $this->safe_string($message, 'product_id');
        if ($marketId !== null) {
            $trade = $this->parse_trade($message);
            $symbol = $trade['symbol'];
            // the exchange sends $type = 'match'
            // but requires 'matches' upon subscribing
            // therefore we resolve 'matches' here instead of 'match'
            // $type = $this->safe_string($message, 'type');
            $type = 'matches';
            $messageHash = $type . ':' . $marketId;
            $array = $this->safe_value($this->trades, $symbol, $array());
            $array[] = $trade;
            $length = is_array($array) ? count($array) : 0;
            if ($length > $this->options['tradesLimit']) {
                array_shift($array);
            }
            $this->trades[$symbol] = $array;
            $client->resolve ($array, $messageHash);
        }
        return $message;
    }

    public function handle_ticker ($client, $message) {
        //
        //     {
        //         $type => 'ticker',
        //         sequence => 12042642428,
        //         product_id => 'BTC-USD',
        //         price => '9380.55',
        //         open_24h => '9450.81000000',
        //         volume_24h => '9611.79166047',
        //         low_24h => '9195.49000000',
        //         high_24h => '9475.19000000',
        //         volume_30d => '327812.00311873',
        //         best_bid => '9380.54',
        //         best_ask => '9380.55',
        //         side => 'buy',
        //         time => '2020-02-01T01:40:16.253563Z',
        //         trade_id => 82062566,
        //         last_size => '0.41969131'
        //     }
        //
        $marketId = $this->safe_string($message, 'product_id');
        if ($marketId !== null) {
            $ticker = $this->parse_ticker($message);
            $symbol = $ticker['symbol'];
            $this->tickers[$symbol] = $ticker;
            $type = $this->safe_string($message, 'type');
            $messageHash = $type . ':' . $marketId;
            $client->resolve ($ticker, $messageHash);
        }
        return $message;
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //     {
        //         $type => 'ticker',
        //         sequence => 12042642428,
        //         product_id => 'BTC-USD',
        //         price => '9380.55',
        //         open_24h => '9450.81000000',
        //         volume_24h => '9611.79166047',
        //         low_24h => '9195.49000000',
        //         high_24h => '9475.19000000',
        //         volume_30d => '327812.00311873',
        //         best_bid => '9380.54',
        //         best_ask => '9380.55',
        //         side => 'buy',
        //         time => '2020-02-01T01:40:16.253563Z',
        //         trade_id => 82062566,
        //         last_size => '0.41969131'
        //     }
        //
        $type = $this->safe_string($ticker, 'type');
        if ($type === null) {
            return parent::parse_ticker($ticker, $market);
        }
        $symbol = null;
        $marketId = $this->safe_string($ticker, 'product_id');
        if ($marketId !== null) {
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            } else {
                list($baseId, $quoteId) = explode('-', $marketId);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        }
        if (($symbol === null) && ($market !== null)) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->parse8601 ($this->safe_string($ticker, 'time'));
        $last = $this->safe_float($ticker, 'price');
        return array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high_24h'),
            'low' => $this->safe_float($ticker, 'low_24h'),
            'bid' => $this->safe_float($ticker, 'best_bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'best_ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => $this->safe_float($ticker, 'open_24h'),
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'volume_24h'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function handle_delta ($bookside, $delta) {
        $price = $this->safe_float($delta, 0);
        $amount = $this->safe_float($delta, 1);
        $bookside->store ($price, $amount);
    }

    public function handle_deltas ($bookside, $deltas) {
        for ($i = 0; $i < count($deltas); $i++) {
            $this->handle_delta ($bookside, $deltas[$i]);
        }
    }

    public function handle_order_book ($client, $message) {
        //
        // first $message (snapshot)
        //
        //     {
        //         "$type" => "snapshot",
        //         "product_id" => "BTC-USD",
        //         "bids" => [
        //             ["10101.10", "0.45054140"]
        //         ],
        //         "asks" => [
        //             ["10102.55", "0.57753524"]
        //         ]
        //     }
        //
        // subsequent updates
        //
        //     {
        //         "$type" => "l2update",
        //         "product_id" => "BTC-USD",
        //         "time" => "2019-08-14T20:42:27.265Z",
        //         "$changes" => array(
        //             array( "buy", "10101.80000000", "0.162567" )
        //         )
        //     }
        //
        $type = $this->safe_string($message, 'type');
        $marketId = $this->safe_string($message, 'product_id');
        if ($marketId !== null) {
            $symbol = null;
            $market = null;
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
                $symbol = $market['symbol'];
            } else {
                list($baseId, $quoteId) = explode('-', $marketId);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
            $name = 'level2';
            $messageHash = $name . ':' . $marketId;
            if ($type === 'snapshot') {
                $depth = 50; // default $depth is 50
                $this->orderbooks[$symbol] = $this->order_book (array(), $depth);
                $orderbook = $this->orderbooks[$symbol];
                $this->handle_deltas ($orderbook['asks'], $this->safe_value($message, 'asks', array()));
                $this->handle_deltas ($orderbook['bids'], $this->safe_value($message, 'bids', array()));
                $orderbook['timestamp'] = null;
                $orderbook['datetime'] = null;
                $client->resolve ($orderbook, $messageHash);
            } else if ($type === 'l2update') {
                $orderbook = $this->orderbooks[$symbol];
                $timestamp = $this->parse8601 ($this->safe_string($message, 'time'));
                $changes = $this->safe_value($message, 'changes', array());
                $sides = array(
                    'sell' => 'asks',
                    'buy' => 'bids',
                );
                for ($i = 0; $i < count($changes); $i++) {
                    $change = $changes[$i];
                    $key = $this->safe_string($change, 0);
                    $side = $this->safe_string($sides, $key);
                    $price = $this->safe_float($change, 1);
                    $amount = $this->safe_float($change, 2);
                    $bookside = $orderbook[$side];
                    $bookside->store ($price, $amount);
                }
                $orderbook['timestamp'] = $timestamp;
                $orderbook['datetime'] = $this->iso8601 ($timestamp);
                $client->resolve ($orderbook, $messageHash);
            }
        }
    }

    public function sign_message ($client, $messageHash, $message, $params = array ()) {
        // todo => implement coinbasepro signMessage() via parent sign()
        return $message;
    }

    public function handle_subscription_status ($client, $message) {
        //
        //     {
        //         type => 'subscriptions',
        //         channels => array(
        //             {
        //                 name => 'level2',
        //                 product_ids => array( 'ETH-BTC' )
        //             }
        //         )
        //     }
        //
        return $message;
    }

    public function handle_message ($client, $message) {
        $type = $this->safe_string($message, 'type');
        $methods = array(
            'snapshot' => array($this, 'handle_order_book'),
            'l2update' => array($this, 'handle_order_book'),
            'subscribe' => array($this, 'handle_subscription_status'),
            'match' => array($this, 'handle_trade'),
            'ticker' => array($this, 'handle_ticker'),
        );
        $method = $this->safe_value($methods, $type);
        if ($method === null) {
            return $message;
        } else {
            return $method($client, $message);
        }
    }
}
