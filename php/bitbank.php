<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception; // a common import
use \ccxt\ExchangeError;
use \ccxt\ArgumentsRequired;

class bitbank extends Exchange {

    public function describe() {
        return $this->deep_extend(parent::describe (), array(
            'id' => 'bitbank',
            'name' => 'bitbank',
            'countries' => array( 'JP' ),
            'version' => 'v1',
            'has' => array(
                'CORS' => null,
                'spot' => true,
                'margin' => false,
                'swap' => false,
                'future' => false,
                'option' => false,
                'addMargin' => false,
                'cancelOrder' => true,
                'createOrder' => true,
                'createReduceOnlyOrder' => false,
                'fetchBalance' => true,
                'fetchBorrowRate' => false,
                'fetchBorrowRateHistories' => false,
                'fetchBorrowRateHistory' => false,
                'fetchBorrowRates' => false,
                'fetchBorrowRatesPerSymbol' => false,
                'fetchDepositAddress' => true,
                'fetchFundingHistory' => false,
                'fetchFundingRate' => false,
                'fetchFundingRateHistory' => false,
                'fetchFundingRates' => false,
                'fetchIndexOHLCV' => false,
                'fetchLeverage' => false,
                'fetchLeverageTiers' => false,
                'fetchMarkOHLCV' => false,
                'fetchMyTrades' => true,
                'fetchOHLCV' => true,
                'fetchOpenInterestHistory' => false,
                'fetchOpenOrders' => true,
                'fetchOrder' => true,
                'fetchOrderBook' => true,
                'fetchPosition' => false,
                'fetchPositions' => false,
                'fetchPositionsRisk' => false,
                'fetchPremiumIndexOHLCV' => false,
                'fetchTicker' => true,
                'fetchTrades' => true,
                'fetchTradingFee' => false,
                'fetchTradingFees' => true,
                'fetchTransfer' => false,
                'fetchTransfers' => false,
                'reduceMargin' => false,
                'setLeverage' => false,
                'setMarginMode' => false,
                'setPositionMode' => false,
                'transfer' => false,
                'withdraw' => true,
            ),
            'timeframes' => array(
                '1m' => '1min',
                '5m' => '5min',
                '15m' => '15min',
                '30m' => '30min',
                '1h' => '1hour',
                '4h' => '4hour',
                '8h' => '8hour',
                '12h' => '12hour',
                '1d' => '1day',
                '1w' => '1week',
            ),
            'hostname' => 'bitbank.cc',
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/37808081-b87f2d9c-2e59-11e8-894d-c1900b7584fe.jpg',
                'api' => array(
                    'public' => 'https://public.{hostname}',
                    'private' => 'https://api.{hostname}',
                    'markets' => 'https://api.{hostname}',
                ),
                'www' => 'https://bitbank.cc/',
                'doc' => 'https://docs.bitbank.cc/',
                'fees' => 'https://bitbank.cc/docs/fees/',
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        '{pair}/ticker',
                        '{pair}/depth',
                        '{pair}/transactions',
                        '{pair}/transactions/{yyyymmdd}',
                        '{pair}/candlestick/{candletype}/{yyyymmdd}',
                    ),
                ),
                'private' => array(
                    'get' => array(
                        'user/assets',
                        'user/spot/order',
                        'user/spot/active_orders',
                        'user/spot/trade_history',
                        'user/withdrawal_account',
                    ),
                    'post' => array(
                        'user/spot/order',
                        'user/spot/cancel_order',
                        'user/spot/cancel_orders',
                        'user/spot/orders_info',
                        'user/request_withdrawal',
                    ),
                ),
                'markets' => array(
                    'get' => array(
                        'spot/pairs',
                    ),
                ),
            ),
            'exceptions' => array(
                '20001' => '\\ccxt\\AuthenticationError',
                '20002' => '\\ccxt\\AuthenticationError',
                '20003' => '\\ccxt\\AuthenticationError',
                '20005' => '\\ccxt\\AuthenticationError',
                '20004' => '\\ccxt\\InvalidNonce',
                '40020' => '\\ccxt\\InvalidOrder',
                '40021' => '\\ccxt\\InvalidOrder',
                '40025' => '\\ccxt\\ExchangeError',
                '40013' => '\\ccxt\\OrderNotFound',
                '40014' => '\\ccxt\\OrderNotFound',
                '50008' => '\\ccxt\\PermissionDenied',
                '50009' => '\\ccxt\\OrderNotFound',
                '50010' => '\\ccxt\\OrderNotFound',
                '60001' => '\\ccxt\\InsufficientFunds',
                '60005' => '\\ccxt\\InvalidOrder',
            ),
        ));
    }

    public function fetch_markets($params = array ()) {
        /**
         * retrieves $data on all markets for bitbank
         * @param {dict} $params extra parameters specific to the exchange api endpoint
         * @return {[dict]} an array of objects representing market $data
         */
        $response = $this->marketsGetSpotPairs ($params);
        //
        //     {
        //       "success" => 1,
        //       "data" => {
        //         "pairs" => array(
        //           {
        //             "name" => "btc_jpy",
        //             "base_asset" => "btc",
        //             "quote_asset" => "jpy",
        //             "maker_fee_rate_base" => "0",
        //             "taker_fee_rate_base" => "0",
        //             "maker_fee_rate_quote" => "-0.0002",
        //             "taker_fee_rate_quote" => "0.0012",
        //             "unit_amount" => "0.0001",
        //             "limit_max_amount" => "1000",
        //             "market_max_amount" => "10",
        //             "market_allowance_rate" => "0.2",
        //             "price_digits" => 0,
        //             "amount_digits" => 4,
        //             "is_enabled" => true,
        //             "stop_order" => false,
        //             "stop_order_and_cancel" => false
        //           }
        //         )
        //       }
        //     }
        //
        $data = $this->safe_value($response, 'data');
        $pairs = $this->safe_value($data, 'pairs', array());
        $result = array();
        for ($i = 0; $i < count($pairs); $i++) {
            $entry = $pairs[$i];
            $id = $this->safe_string($entry, 'name');
            $baseId = $this->safe_string($entry, 'base_asset');
            $quoteId = $this->safe_string($entry, 'quote_asset');
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $result[] = array(
                'id' => $id,
                'symbol' => $base . '/' . $quote,
                'base' => $base,
                'quote' => $quote,
                'settle' => null,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'settleId' => null,
                'type' => 'spot',
                'spot' => true,
                'margin' => false,
                'swap' => false,
                'future' => false,
                'option' => false,
                'active' => $this->safe_value($entry, 'is_enabled'),
                'contract' => false,
                'linear' => null,
                'inverse' => null,
                'taker' => $this->safe_number($entry, 'taker_fee_rate_quote'),
                'maker' => $this->safe_number($entry, 'maker_fee_rate_quote'),
                'contractSize' => null,
                'expiry' => null,
                'expiryDatetime' => null,
                'strike' => null,
                'optionType' => null,
                'precision' => array(
                    'amount' => $this->safe_integer($entry, 'amount_digits'),
                    'price' => $this->safe_integer($entry, 'price_digits'),
                ),
                'limits' => array(
                    'leverage' => array(
                        'min' => null,
                        'max' => null,
                    ),
                    'amount' => array(
                        'min' => $this->safe_number($entry, 'unit_amount'),
                        'max' => $this->safe_number($entry, 'limit_max_amount'),
                    ),
                    'price' => array(
                        'min' => null,
                        'max' => null,
                    ),
                    'cost' => array(
                        'min' => null,
                        'max' => null,
                    ),
                ),
                'info' => $entry,
            );
        }
        return $result;
    }

    public function parse_ticker($ticker, $market = null) {
        $symbol = $this->safe_symbol(null, $market);
        $timestamp = $this->safe_integer($ticker, 'timestamp');
        $last = $this->safe_string($ticker, 'last');
        return $this->safe_ticker(array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'high' => $this->safe_string($ticker, 'high'),
            'low' => $this->safe_string($ticker, 'low'),
            'bid' => $this->safe_string($ticker, 'buy'),
            'bidVolume' => null,
            'ask' => $this->safe_string($ticker, 'sell'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_string($ticker, 'vol'),
            'quoteVolume' => null,
            'info' => $ticker,
        ), $market, false);
    }

    public function fetch_ticker($symbol, $params = array ()) {
        /**
         * fetches a price ticker, a statistical calculation with the information calculated over the past 24 hours for a specific $market
         * @param {str} $symbol unified $symbol of the $market to fetch the ticker for
         * @param {dict} $params extra parameters specific to the bitbank api endpoint
         * @return {dict} a {@link https://docs.ccxt.com/en/latest/manual.html#ticker-structure ticker structure}
         */
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'pair' => $market['id'],
        );
        $response = $this->publicGetPairTicker (array_merge($request, $params));
        $data = $this->safe_value($response, 'data', array());
        return $this->parse_ticker($data, $market);
    }

    public function fetch_order_book($symbol, $limit = null, $params = array ()) {
        /**
         * fetches information on open orders with bid (buy) and ask (sell) prices, volumes and other data
         * @param {str} $symbol unified $symbol of the market to fetch the order book for
         * @param {int|null} $limit the maximum amount of order book entries to return
         * @param {dict} $params extra parameters specific to the bitbank api endpoint
         * @return {dict} A dictionary of {@link https://docs.ccxt.com/en/latest/manual.html#order-book-structure order book structures} indexed by market symbols
         */
        $this->load_markets();
        $request = array(
            'pair' => $this->market_id($symbol),
        );
        $response = $this->publicGetPairDepth (array_merge($request, $params));
        $orderbook = $this->safe_value($response, 'data', array());
        $timestamp = $this->safe_integer($orderbook, 'timestamp');
        return $this->parse_order_book($orderbook, $symbol, $timestamp);
    }

    public function parse_trade($trade, $market = null) {
        $timestamp = $this->safe_integer($trade, 'executed_at');
        $market = $this->safe_market(null, $market);
        $priceString = $this->safe_string($trade, 'price');
        $amountString = $this->safe_string($trade, 'amount');
        $id = $this->safe_string_2($trade, 'transaction_id', 'trade_id');
        $takerOrMaker = $this->safe_string($trade, 'maker_taker');
        $fee = null;
        $feeCostString = $this->safe_string($trade, 'fee_amount_quote');
        if ($feeCostString !== null) {
            $fee = array(
                'currency' => $market['quote'],
                'cost' => $feeCostString,
            );
        }
        $orderId = $this->safe_string($trade, 'order_id');
        $type = $this->safe_string($trade, 'type');
        $side = $this->safe_string($trade, 'side');
        return $this->safe_trade(array(
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'symbol' => $market['symbol'],
            'id' => $id,
            'order' => $orderId,
            'type' => $type,
            'side' => $side,
            'takerOrMaker' => $takerOrMaker,
            'price' => $priceString,
            'amount' => $amountString,
            'cost' => null,
            'fee' => $fee,
            'info' => $trade,
        ), $market);
    }

    public function fetch_trades($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'pair' => $market['id'],
        );
        $response = $this->publicGetPairTransactions (array_merge($request, $params));
        $data = $this->safe_value($response, 'data', array());
        $trades = $this->safe_value($data, 'transactions', array());
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_trading_fees($params = array ()) {
        $this->load_markets();
        $response = $this->marketsGetSpotPairs ($params);
        //
        //     {
        //         success => '1',
        //         $data => {
        //           $pairs => array(
        //             array(
        //               name => 'btc_jpy',
        //               base_asset => 'btc',
        //               quote_asset => 'jpy',
        //               maker_fee_rate_base => '0',
        //               taker_fee_rate_base => '0',
        //               maker_fee_rate_quote => '-0.0002',
        //               taker_fee_rate_quote => '0.0012',
        //               unit_amount => '0.0001',
        //               limit_max_amount => '1000',
        //               market_max_amount => '10',
        //               market_allowance_rate => '0.2',
        //               price_digits => '0',
        //               amount_digits => '4',
        //               is_enabled => true,
        //               stop_order => false,
        //               stop_order_and_cancel => false
        //             ),
        //             ...
        //           )
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $pairs = $this->safe_value($data, 'pairs', array());
        $result = array();
        for ($i = 0; $i < count($pairs); $i++) {
            $pair = $pairs[$i];
            $marketId = $this->safe_string($pair, 'name');
            $market = $this->safe_market($marketId);
            $symbol = $market['symbol'];
            $result[$symbol] = array(
                'info' => $pair,
                'symbol' => $symbol,
                'maker' => $this->safe_number($pair, 'maker_fee_rate_quote'),
                'taker' => $this->safe_number($pair, 'taker_fee_rate_quote'),
                'percentage' => true,
                'tierBased' => false,
            );
        }
        return $result;
    }

    public function parse_ohlcv($ohlcv, $market = null) {
        //
        //     array(
        //         "0.02501786",
        //         "0.02501786",
        //         "0.02501786",
        //         "0.02501786",
        //         "0.0000",
        //         1591488000000
        //     )
        //
        return array(
            $this->safe_integer($ohlcv, 5),
            $this->safe_number($ohlcv, 0),
            $this->safe_number($ohlcv, 1),
            $this->safe_number($ohlcv, 2),
            $this->safe_number($ohlcv, 3),
            $this->safe_number($ohlcv, 4),
        );
    }

    public function fetch_ohlcv($symbol, $timeframe = '5m', $since = null, $limit = null, $params = array ()) {
        /**
         * fetches historical $candlestick $data containing the open, high, low, and close price, and the volume of a $market
         * @param {str} $symbol unified $symbol of the $market to fetch OHLCV $data for
         * @param {str} $timeframe the length of time each candle represents
         * @param {int|null} $since timestamp in ms of the earliest candle to fetch
         * @param {int|null} $limit the maximum amount of candles to fetch
         * @param {dict} $params extra parameters specific to the bitbank api endpoint
         * @return {[[int]]} A list of candles ordered as timestamp, open, high, low, close, volume
         */
        if ($since === null) {
            throw new ArgumentsRequired($this->id . ' fetchOHLCV() requires a $since argument');
        }
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'pair' => $market['id'],
            'candletype' => $this->timeframes[$timeframe],
            'yyyymmdd' => $this->yyyymmdd($since, ''),
        );
        $response = $this->publicGetPairCandlestickCandletypeYyyymmdd (array_merge($request, $params));
        //
        //     {
        //         "success":1,
        //         "data":{
        //             "candlestick":[
        //                 {
        //                     "type":"5min",
        //                     "ohlcv":[
        //                         ["0.02501786","0.02501786","0.02501786","0.02501786","0.0000",1591488000000],
        //                         ["0.02501747","0.02501953","0.02501747","0.02501953","0.3017",1591488300000],
        //                         ["0.02501762","0.02501762","0.02500392","0.02500392","0.1500",1591488600000],
        //                     ]
        //                 }
        //             ],
        //             "timestamp":1591508668190
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $candlestick = $this->safe_value($data, 'candlestick', array());
        $first = $this->safe_value($candlestick, 0, array());
        $ohlcv = $this->safe_value($first, 'ohlcv', array());
        return $this->parse_ohlcvs($ohlcv, $market, $timeframe, $since, $limit);
    }

    public function parse_balance($response) {
        $result = array(
            'info' => $response,
            'timestamp' => null,
            'datetime' => null,
        );
        $data = $this->safe_value($response, 'data', array());
        $assets = $this->safe_value($data, 'assets', array());
        for ($i = 0; $i < count($assets); $i++) {
            $balance = $assets[$i];
            $currencyId = $this->safe_string($balance, 'asset');
            $code = $this->safe_currency_code($currencyId);
            $account = $this->account();
            $account['free'] = $this->safe_string($balance, 'free_amount');
            $account['used'] = $this->safe_string($balance, 'locked_amount');
            $account['total'] = $this->safe_string($balance, 'onhand_amount');
            $result[$code] = $account;
        }
        return $this->safe_balance($result);
    }

    public function fetch_balance($params = array ()) {
        /**
         * query for balance and get the amount of funds available for trading or funds locked in orders
         * @param {dict} $params extra parameters specific to the bitbank api endpoint
         * @return {dict} a ~@link https://docs.ccxt.com/en/latest/manual.html?#balance-structure balance structure~
         */
        $this->load_markets();
        $response = $this->privateGetUserAssets ($params);
        //
        //     {
        //       "success" => "1",
        //       "data" => {
        //         "assets" => array(
        //           {
        //             "asset" => "jpy",
        //             "amount_precision" => "4",
        //             "onhand_amount" => "0.0000",
        //             "locked_amount" => "0.0000",
        //             "free_amount" => "0.0000",
        //             "stop_deposit" => false,
        //             "stop_withdrawal" => false,
        //             "withdrawal_fee" => array(
        //               "threshold" => "30000.0000",
        //               "under" => "550.0000",
        //               "over" => "770.0000"
        //             }
        //           ),
        //           array(
        //             "asset" => "btc",
        //             "amount_precision" => "8",
        //             "onhand_amount" => "0.00000000",
        //             "locked_amount" => "0.00000000",
        //             "free_amount" => "0.00000000",
        //             "stop_deposit" => false,
        //             "stop_withdrawal" => false,
        //             "withdrawal_fee" => "0.00060000"
        //           ),
        //         )
        //       }
        //     }
        //
        return $this->parse_balance($response);
    }

    public function parse_order_status($status) {
        $statuses = array(
            'UNFILLED' => 'open',
            'PARTIALLY_FILLED' => 'open',
            'FULLY_FILLED' => 'closed',
            'CANCELED_UNFILLED' => 'canceled',
            'CANCELED_PARTIALLY_FILLED' => 'canceled',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order($order, $market = null) {
        $id = $this->safe_string($order, 'order_id');
        $marketId = $this->safe_string($order, 'pair');
        $market = $this->safe_market($marketId, $market);
        $timestamp = $this->safe_integer($order, 'ordered_at');
        $price = $this->safe_string($order, 'price');
        $amount = $this->safe_string($order, 'start_amount');
        $filled = $this->safe_string($order, 'executed_amount');
        $remaining = $this->safe_string($order, 'remaining_amount');
        $average = $this->safe_string($order, 'average_price');
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $type = $this->safe_string_lower($order, 'type');
        $side = $this->safe_string_lower($order, 'side');
        return $this->safe_order(array(
            'id' => $id,
            'clientOrderId' => null,
            'datetime' => $this->iso8601($timestamp),
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $market['symbol'],
            'type' => $type,
            'timeInForce' => null,
            'postOnly' => null,
            'side' => $side,
            'price' => $price,
            'stopPrice' => null,
            'cost' => null,
            'average' => $average,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => null,
            'fee' => null,
            'info' => $order,
        ), $market);
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'pair' => $market['id'],
            'amount' => $this->amount_to_precision($symbol, $amount),
            'side' => $side,
            'type' => $type,
        );
        if ($type === 'limit') {
            $request['price'] = $this->price_to_precision($symbol, $price);
        }
        $response = $this->privatePostUserSpotOrder (array_merge($request, $params));
        $data = $this->safe_value($response, 'data');
        return $this->parse_order($data, $market);
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'order_id' => $id,
            'pair' => $market['id'],
        );
        $response = $this->privatePostUserSpotCancelOrder (array_merge($request, $params));
        $data = $this->safe_value($response, 'data');
        return $data;
    }

    public function fetch_order($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'order_id' => $id,
            'pair' => $market['id'],
        );
        $response = $this->privateGetUserSpotOrder (array_merge($request, $params));
        $data = $this->safe_value($response, 'data');
        return $this->parse_order($data, $market);
    }

    public function fetch_open_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'pair' => $market['id'],
        );
        if ($limit !== null) {
            $request['count'] = $limit;
        }
        if ($since !== null) {
            $request['since'] = intval($since / 1000);
        }
        $response = $this->privateGetUserSpotActiveOrders (array_merge($request, $params));
        $data = $this->safe_value($response, 'data', array());
        $orders = $this->safe_value($data, 'orders', array());
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function fetch_my_trades($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $market = null;
        if ($symbol !== null) {
            $request['pair'] = $market['id'];
            $market = $this->market($symbol);
        }
        if ($limit !== null) {
            $request['count'] = $limit;
        }
        if ($since !== null) {
            $request['since'] = intval($since / 1000);
        }
        $response = $this->privateGetUserSpotTradeHistory (array_merge($request, $params));
        $data = $this->safe_value($response, 'data', array());
        $trades = $this->safe_value($data, 'trades', array());
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_deposit_address($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency($code);
        $request = array(
            'asset' => $currency['id'],
        );
        $response = $this->privateGetUserWithdrawalAccount (array_merge($request, $params));
        $data = $this->safe_value($response, 'data', array());
        // Not sure about this if there could be more than one account...
        $accounts = $this->safe_value($data, 'accounts', array());
        $firstAccount = $this->safe_value($accounts, 0, array());
        $address = $this->safe_string($firstAccount, 'address');
        return array(
            'currency' => $currency,
            'address' => $address,
            'tag' => null,
            'network' => null,
            'info' => $response,
        );
    }

    public function withdraw($code, $amount, $address, $tag = null, $params = array ()) {
        list($tag, $params) = $this->handle_withdraw_tag_and_params($tag, $params);
        if (!(is_array($params) && array_key_exists('uuid', $params))) {
            throw new ExchangeError($this->id . ' uuid is required for withdrawal');
        }
        $this->load_markets();
        $currency = $this->currency($code);
        $request = array(
            'asset' => $currency['id'],
            'amount' => $amount,
        );
        $response = $this->privatePostUserRequestWithdrawal (array_merge($request, $params));
        //
        //     {
        //         "success" => 1,
        //         "data" => {
        //             "uuid" => "string",
        //             "asset" => "btc",
        //             "amount" => 0,
        //             "account_uuid" => "string",
        //             "fee" => 0,
        //             "status" => "DONE",
        //             "label" => "string",
        //             "txid" => "string",
        //             "address" => "string",
        //             "requested_at" => 0
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        return $this->parse_transaction($data, $currency);
    }

    public function parse_transaction($transaction, $currency = null) {
        //
        // withdraw
        //
        //     {
        //         "uuid" => "string",
        //         "asset" => "btc",
        //         "amount" => 0,
        //         "account_uuid" => "string",
        //         "fee" => 0,
        //         "status" => "DONE",
        //         "label" => "string",
        //         "txid" => "string",
        //         "address" => "string",
        //         "requested_at" => 0
        //     }
        //
        $txid = $this->safe_string($transaction, 'txid');
        $currency = $this->safe_currency(null, $currency);
        return array(
            'id' => $txid,
            'txid' => $txid,
            'timestamp' => null,
            'datetime' => null,
            'network' => null,
            'addressFrom' => null,
            'address' => null,
            'addressTo' => null,
            'amount' => null,
            'type' => null,
            'currency' => $currency['code'],
            'status' => null,
            'updated' => null,
            'tagFrom' => null,
            'tag' => null,
            'tagTo' => null,
            'comment' => null,
            'fee' => null,
            'info' => $transaction,
        );
    }

    public function nonce() {
        return $this->milliseconds();
    }

    public function sign($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $query = $this->omit($params, $this->extract_params($path));
        $url = $this->implode_hostname($this->urls['api'][$api]) . '/';
        if (($api === 'public') || ($api === 'markets')) {
            $url .= $this->implode_params($path, $params);
            if ($query) {
                $url .= '?' . $this->urlencode($query);
            }
        } else {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce();
            $auth = $nonce;
            $url .= $this->version . '/' . $this->implode_params($path, $params);
            if ($method === 'POST') {
                $body = $this->json($query);
                $auth .= $body;
            } else {
                $auth .= '/' . $this->version . '/' . $path;
                if ($query) {
                    $query = $this->urlencode($query);
                    $url .= '?' . $query;
                    $auth .= '?' . $query;
                }
            }
            $headers = array(
                'Content-Type' => 'application/json',
                'ACCESS-KEY' => $this->apiKey,
                'ACCESS-NONCE' => $nonce,
                'ACCESS-SIGNATURE' => $this->hmac($this->encode($auth), $this->encode($this->secret)),
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors($httpCode, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return;
        }
        $success = $this->safe_integer($response, 'success');
        $data = $this->safe_value($response, 'data');
        if (!$success || !$data) {
            $errorMessages = array(
                '10000' => 'URL does not exist',
                '10001' => 'A system error occurred. Please contact support',
                '10002' => 'Invalid JSON format. Please check the contents of transmission',
                '10003' => 'A system error occurred. Please contact support',
                '10005' => 'A timeout error occurred. Please wait for a while and try again',
                '20001' => 'API authentication failed',
                '20002' => 'Illegal API key',
                '20003' => 'API key does not exist',
                '20004' => 'API Nonce does not exist',
                '20005' => 'API signature does not exist',
                '20011' => 'Two-step verification failed',
                '20014' => 'SMS authentication failed',
                '30001' => 'Please specify the order quantity',
                '30006' => 'Please specify the order ID',
                '30007' => 'Please specify the order ID array',
                '30009' => 'Please specify the stock',
                '30012' => 'Please specify the order price',
                '30013' => 'Trade Please specify either',
                '30015' => 'Please specify the order type',
                '30016' => 'Please specify asset name',
                '30019' => 'Please specify uuid',
                '30039' => 'Please specify the amount to be withdrawn',
                '40001' => 'The order quantity is invalid',
                '40006' => 'Count value is invalid',
                '40007' => 'End time is invalid',
                '40008' => 'end_id Value is invalid',
                '40009' => 'The from_id value is invalid',
                '40013' => 'The order ID is invalid',
                '40014' => 'The order ID array is invalid',
                '40015' => 'Too many specified orders',
                '40017' => 'Incorrect issue name',
                '40020' => 'The order price is invalid',
                '40021' => 'The trading classification is invalid',
                '40022' => 'Start date is invalid',
                '40024' => 'The order type is invalid',
                '40025' => 'Incorrect asset name',
                '40028' => 'uuid is invalid',
                '40048' => 'The amount of withdrawal is illegal',
                '50003' => 'Currently, this account is in a state where you can not perform the operation you specified. Please contact support',
                '50004' => 'Currently, this account is temporarily registered. Please try again after registering your account',
                '50005' => 'Currently, this account is locked. Please contact support',
                '50006' => 'Currently, this account is locked. Please contact support',
                '50008' => 'User identification has not been completed',
                '50009' => 'Your order does not exist',
                '50010' => 'Can not cancel specified order',
                '50011' => 'API not found',
                '60001' => 'The number of possessions is insufficient',
                '60002' => 'It exceeds the quantity upper limit of the tender buying order',
                '60003' => 'The specified quantity exceeds the limit',
                '60004' => 'The specified quantity is below the threshold',
                '60005' => 'The specified price is above the limit',
                '60006' => 'The specified price is below the lower limit',
                '70001' => 'A system error occurred. Please contact support',
                '70002' => 'A system error occurred. Please contact support',
                '70003' => 'A system error occurred. Please contact support',
                '70004' => 'We are unable to accept orders as the transaction is currently suspended',
                '70005' => 'Order can not be accepted because purchase order is currently suspended',
                '70006' => 'We can not accept orders because we are currently unsubscribed ',
                '70009' => 'We are currently temporarily restricting orders to be carried out. Please use the limit order.',
                '70010' => 'We are temporarily raising the minimum order quantity as the system load is now rising.',
            );
            $errorClasses = $this->exceptions;
            $code = $this->safe_string($data, 'code');
            $message = $this->safe_string($errorMessages, $code, 'Error');
            $ErrorClass = $this->safe_value($errorClasses, $code);
            if ($ErrorClass !== null) {
                throw new $ErrorClass($message);
            } else {
                throw new ExchangeError($this->id . ' ' . $this->json($response));
            }
        }
    }
}
