# -*- coding: utf-8 -*-

# PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
# https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

import ccxt.async_support
from ccxt.async_support.base.ws.cache import ArrayCache, ArrayCacheBySymbolById, ArrayCacheByTimestamp
import hashlib
from ccxt.base.errors import ArgumentsRequired
from ccxt.base.errors import AuthenticationError


class bitvavo(ccxt.async_support.bitvavo):

    def describe(self):
        return self.deep_extend(super(bitvavo, self).describe(), {
            'has': {
                'ws': True,
                'watchOrderBook': True,
                'watchTrades': True,
                'watchTicker': True,
                'watchOHLCV': True,
                'watchOrders': True,
                'watchMyTrades': True,
            },
            'urls': {
                'api': {
                    'ws': 'wss://ws.bitvavo.com/v2',
                },
            },
            'options': {
                'tradesLimit': 1000,
                'ordersLimit': 1000,
                'OHLCVLimit': 1000,
            },
        })

    async def watch_public(self, name, symbol, params={}):
        await self.load_markets()
        market = self.market(symbol)
        messageHash = name + '@' + market['id']
        url = self.urls['api']['ws']
        request = {
            'action': 'subscribe',
            'channels': [
                {
                    'name': name,
                    'markets': [
                        market['id'],
                    ],
                },
            ],
        }
        message = self.extend(request, params)
        return await self.watch(url, messageHash, message, messageHash)

    async def watch_ticker(self, symbol, params={}):
        """
        watches a price ticker, a statistical calculation with the information calculated over the past 24 hours for a specific market
        :param str symbol: unified symbol of the market to fetch the ticker for
        :param dict params: extra parameters specific to the bitvavo api endpoint
        :returns dict: a `ticker structure <https://docs.ccxt.com/#/?id=ticker-structure>`
        """
        return await self.watch_public('ticker24h', symbol, params)

    def handle_ticker(self, client, message):
        #
        #     {
        #         event: 'ticker24h',
        #         data: [
        #             {
        #                 market: 'ETH-EUR',
        #                 open: '193.5',
        #                 high: '202.72',
        #                 low: '192.46',
        #                 last: '199.01',
        #                 volume: '3587.05020246',
        #                 volumeQuote: '708030.17',
        #                 bid: '199.56',
        #                 bidSize: '4.14730803',
        #                 ask: '199.57',
        #                 askSize: '6.13642074',
        #                 timestamp: 1590770885217
        #             }
        #         ]
        #     }
        #
        event = self.safe_string(message, 'event')
        tickers = self.safe_value(message, 'data', [])
        for i in range(0, len(tickers)):
            data = tickers[i]
            marketId = self.safe_string(data, 'market')
            market = self.safe_market(marketId, None, '-')
            messageHash = event + '@' + marketId
            ticker = self.parse_ticker(data, market)
            symbol = ticker['symbol']
            self.tickers[symbol] = ticker
            client.resolve(ticker, messageHash)
        return message

    async def watch_trades(self, symbol, since=None, limit=None, params={}):
        """
        get the list of most recent trades for a particular symbol
        :param str symbol: unified symbol of the market to fetch trades for
        :param int|None since: timestamp in ms of the earliest trade to fetch
        :param int|None limit: the maximum amount of trades to fetch
        :param dict params: extra parameters specific to the bitvavo api endpoint
        :returns [dict]: a list of `trade structures <https://docs.ccxt.com/en/latest/manual.html?#public-trades>`
        """
        await self.load_markets()
        symbol = self.symbol(symbol)
        trades = await self.watch_public('trades', symbol, params)
        if self.newUpdates:
            limit = trades.getLimit(symbol, limit)
        return self.filter_by_since_limit(trades, since, limit, 'timestamp', True)

    def handle_trade(self, client, message):
        #
        #     {
        #         event: 'trade',
        #         timestamp: 1590779594547,
        #         market: 'ETH-EUR',
        #         id: '450c3298-f082-4461-9e2c-a0262cc7cc2e',
        #         amount: '0.05026233',
        #         price: '198.46',
        #         side: 'buy'
        #     }
        #
        marketId = self.safe_string(message, 'market')
        market = self.safe_market(marketId, None, '-')
        symbol = market['symbol']
        name = 'trades'
        messageHash = name + '@' + marketId
        trade = self.parse_trade(message, market)
        tradesArray = self.safe_value(self.trades, symbol)
        if tradesArray is None:
            limit = self.safe_integer(self.options, 'tradesLimit', 1000)
            tradesArray = ArrayCache(limit)
        tradesArray.append(trade)
        self.trades[symbol] = tradesArray
        client.resolve(tradesArray, messageHash)

    async def watch_ohlcv(self, symbol, timeframe='1m', since=None, limit=None, params={}):
        """
        watches historical candlestick data containing the open, high, low, and close price, and the volume of a market
        :param str symbol: unified symbol of the market to fetch OHLCV data for
        :param str timeframe: the length of time each candle represents
        :param int|None since: timestamp in ms of the earliest candle to fetch
        :param int|None limit: the maximum amount of candles to fetch
        :param dict params: extra parameters specific to the bitvavo api endpoint
        :returns [[int]]: A list of candles ordered, open, high, low, close, volume
        """
        await self.load_markets()
        market = self.market(symbol)
        symbol = market['symbol']
        name = 'candles'
        marketId = market['id']
        interval = self.safe_string(self.timeframes, timeframe, timeframe)
        messageHash = name + '@' + marketId + '_' + interval
        url = self.urls['api']['ws']
        request = {
            'action': 'subscribe',
            'channels': [
                {
                    'name': 'candles',
                    'interval': [interval],
                    'markets': [marketId],
                },
            ],
        }
        message = self.extend(request, params)
        ohlcv = await self.watch(url, messageHash, message, messageHash)
        if self.newUpdates:
            limit = ohlcv.getLimit(symbol, limit)
        return self.filter_by_since_limit(ohlcv, since, limit, 0, True)

    def handle_ohlcv(self, client, message):
        #
        #     {
        #         event: 'candle',
        #         market: 'BTC-EUR',
        #         interval: '1m',
        #         candle: [
        #             [
        #                 1590797160000,
        #                 '8480.9',
        #                 '8480.9',
        #                 '8480.9',
        #                 '8480.9',
        #                 '0.01038628'
        #             ]
        #         ]
        #     }
        #
        name = 'candles'
        marketId = self.safe_string(message, 'market')
        market = self.safe_market(marketId, None, '-')
        symbol = market['symbol']
        interval = self.safe_string(message, 'interval')
        # use a reverse lookup in a static map instead
        timeframe = self.find_timeframe(interval)
        messageHash = name + '@' + marketId + '_' + interval
        candles = self.safe_value(message, 'candle')
        self.ohlcvs[symbol] = self.safe_value(self.ohlcvs, symbol, {})
        stored = self.safe_value(self.ohlcvs[symbol], timeframe)
        if stored is None:
            limit = self.safe_integer(self.options, 'OHLCVLimit', 1000)
            stored = ArrayCacheByTimestamp(limit)
            self.ohlcvs[symbol][timeframe] = stored
        for i in range(0, len(candles)):
            candle = candles[i]
            parsed = self.parse_ohlcv(candle, market)
            stored.append(parsed)
        client.resolve(stored, messageHash)

    async def watch_order_book(self, symbol, limit=None, params={}):
        """
        watches information on open orders with bid(buy) and ask(sell) prices, volumes and other data
        :param str symbol: unified symbol of the market to fetch the order book for
        :param int|None limit: the maximum amount of order book entries to return
        :param dict params: extra parameters specific to the bitvavo api endpoint
        :returns dict: A dictionary of `order book structures <https://docs.ccxt.com/#/?id=order-book-structure>` indexed by market symbols
        """
        await self.load_markets()
        market = self.market(symbol)
        symbol = market['symbol']
        name = 'book'
        messageHash = name + '@' + market['id']
        url = self.urls['api']['ws']
        request = {
            'action': 'subscribe',
            'channels': [
                {
                    'name': name,
                    'markets': [
                        market['id'],
                    ],
                },
            ],
        }
        subscription = {
            'messageHash': messageHash,
            'name': name,
            'symbol': symbol,
            'marketId': market['id'],
            'method': self.handle_order_book_subscription,
            'limit': limit,
            'params': params,
        }
        message = self.extend(request, params)
        orderbook = await self.watch(url, messageHash, message, messageHash, subscription)
        return orderbook.limit()

    def handle_delta(self, bookside, delta):
        price = self.safe_float(delta, 0)
        amount = self.safe_float(delta, 1)
        bookside.store(price, amount)

    def handle_deltas(self, bookside, deltas):
        for i in range(0, len(deltas)):
            self.handle_delta(bookside, deltas[i])

    def handle_order_book_message(self, client, message, orderbook):
        #
        #     {
        #         event: 'book',
        #         market: 'BTC-EUR',
        #         nonce: 36947383,
        #         bids: [
        #             ['8477.8', '0']
        #         ],
        #         asks: [
        #             ['8550.9', '0']
        #         ]
        #     }
        #
        nonce = self.safe_integer(message, 'nonce')
        if nonce > orderbook['nonce']:
            self.handle_deltas(orderbook['asks'], self.safe_value(message, 'asks', []))
            self.handle_deltas(orderbook['bids'], self.safe_value(message, 'bids', []))
            orderbook['nonce'] = nonce
        return orderbook

    def handle_order_book(self, client, message):
        #
        #     {
        #         event: 'book',
        #         market: 'BTC-EUR',
        #         nonce: 36729561,
        #         bids: [
        #             ['8513.3', '0'],
        #             ['8518.8', '0.64236203'],
        #             ['8513.6', '0.32435481'],
        #         ],
        #         asks: []
        #     }
        #
        event = self.safe_string(message, 'event')
        marketId = self.safe_string(message, 'market')
        market = self.safe_market(marketId, None, '-')
        symbol = market['symbol']
        messageHash = event + '@' + market['id']
        orderbook = self.safe_value(self.orderbooks, symbol)
        if orderbook is None:
            return
        if orderbook['nonce'] is None:
            subscription = self.safe_value(client.subscriptions, messageHash, {})
            watchingOrderBookSnapshot = self.safe_value(subscription, 'watchingOrderBookSnapshot')
            if watchingOrderBookSnapshot is None:
                subscription['watchingOrderBookSnapshot'] = True
                client.subscriptions[messageHash] = subscription
                options = self.safe_value(self.options, 'watchOrderBookSnapshot', {})
                delay = self.safe_integer(options, 'delay', self.rateLimit)
                # fetch the snapshot in a separate async call after a warmup delay
                self.delay(delay, self.watch_order_book_snapshot, client, message, subscription)
            orderbook.cache.append(message)
        else:
            self.handle_order_book_message(client, message, orderbook)
            client.resolve(orderbook, messageHash)

    async def watch_order_book_snapshot(self, client, message, subscription):
        params = self.safe_value(subscription, 'params')
        marketId = self.safe_string(subscription, 'marketId')
        name = 'getBook'
        messageHash = name + '@' + marketId
        url = self.urls['api']['ws']
        request = {
            'action': name,
            'market': marketId,
        }
        orderbook = await self.watch(url, messageHash, self.extend(request, params), messageHash, subscription)
        return orderbook.limit()

    def handle_order_book_snapshot(self, client, message):
        #
        #     {
        #         action: 'getBook',
        #         response: {
        #             market: 'BTC-EUR',
        #             nonce: 36946120,
        #             bids: [
        #                 ['8494.9', '0.24399521'],
        #                 ['8494.8', '0.34884085'],
        #                 ['8493.9', '0.14535128'],
        #             ],
        #             asks: [
        #                 ['8495', '0.46982463'],
        #                 ['8495.1', '0.12178267'],
        #                 ['8496.2', '0.21924143'],
        #             ]
        #         }
        #     }
        #
        response = self.safe_value(message, 'response')
        if response is None:
            return message
        marketId = self.safe_string(response, 'market')
        symbol = self.safe_symbol(marketId, None, '-')
        name = 'book'
        messageHash = name + '@' + marketId
        orderbook = self.orderbooks[symbol]
        snapshot = self.parse_order_book(response, symbol)
        snapshot['nonce'] = self.safe_integer(response, 'nonce')
        orderbook.reset(snapshot)
        # unroll the accumulated deltas
        messages = orderbook.cache
        for i in range(0, len(messages)):
            message = messages[i]
            self.handle_order_book_message(client, message, orderbook)
        self.orderbooks[symbol] = orderbook
        client.resolve(orderbook, messageHash)

    def handle_order_book_subscription(self, client, message, subscription):
        symbol = self.safe_string(subscription, 'symbol')
        limit = self.safe_integer(subscription, 'limit')
        if symbol in self.orderbooks:
            del self.orderbooks[symbol]
        self.orderbooks[symbol] = self.order_book({}, limit)

    def handle_order_book_subscriptions(self, client, message, marketIds):
        name = 'book'
        for i in range(0, len(marketIds)):
            marketId = self.safe_string(marketIds, i)
            symbol = self.safe_symbol(marketId, None, '-')
            messageHash = name + '@' + marketId
            if not (symbol in self.orderbooks):
                subscription = self.safe_value(client.subscriptions, messageHash)
                method = self.safe_value(subscription, 'method')
                if method is not None:
                    method(client, message, subscription)

    async def watch_orders(self, symbol=None, since=None, limit=None, params={}):
        """
        watches information on multiple orders made by the user
        :param str|None symbol: unified market symbol of the market orders were made in
        :param int|None since: the earliest time in ms to fetch orders for
        :param int|None limit: the maximum number of  orde structures to retrieve
        :param dict params: extra parameters specific to the bitvavo api endpoint
        :returns [dict]: a list of `order structures <https://docs.ccxt.com/#/?id=order-structure>`
        """
        if symbol is None:
            raise ArgumentsRequired(self.id + ' watchOrders requires a symbol argument')
        await self.load_markets()
        await self.authenticate()
        market = self.market(symbol)
        symbol = market['symbol']
        marketId = market['id']
        url = self.urls['api']['ws']
        name = 'account'
        messageHash = 'order:' + symbol
        request = {
            'action': 'subscribe',
            'channels': [
                {
                    'name': name,
                    'markets': [marketId],
                },
            ],
        }
        orders = await self.watch(url, messageHash, request, messageHash)
        if self.newUpdates:
            limit = orders.getLimit(symbol, limit)
        return self.filter_by_symbol_since_limit(orders, symbol, since, limit, True)

    async def watch_my_trades(self, symbol=None, since=None, limit=None, params={}):
        """
        watches information on multiple trades made by the user
        :param str symbol: unified market symbol of the market orders were made in
        :param int|None since: the earliest time in ms to fetch orders for
        :param int|None limit: the maximum number of  orde structures to retrieve
        :param dict params: extra parameters specific to the bitvavo api endpoint
        :returns [dict]: a list of [order structures]{@link https://docs.ccxt.com/#/?id=order-structure
        """
        if symbol is None:
            raise ArgumentsRequired(self.id + ' watchMyTrades requires a symbol argument')
        await self.load_markets()
        await self.authenticate()
        market = self.market(symbol)
        symbol = market['symbol']
        marketId = market['id']
        url = self.urls['api']['ws']
        name = 'account'
        messageHash = 'myTrades:' + symbol
        request = {
            'action': 'subscribe',
            'channels': [
                {
                    'name': name,
                    'markets': [marketId],
                },
            ],
        }
        trades = await self.watch(url, messageHash, request, messageHash)
        if self.newUpdates:
            limit = trades.getLimit(symbol, limit)
        return self.filter_by_symbol_since_limit(trades, symbol, since, limit, True)

    def handle_order(self, client, message):
        #
        #     {
        #         event: 'order',
        #         orderId: 'f0e5180f-9497-4d05-9dc2-7056e8a2de9b',
        #         market: 'ETH-EUR',
        #         created: 1590948500319,
        #         updated: 1590948500319,
        #         status: 'new',
        #         side: 'sell',
        #         orderType: 'limit',
        #         amount: '0.1',
        #         amountRemaining: '0.1',
        #         price: '300',
        #         onHold: '0.1',
        #         onHoldCurrency: 'ETH',
        #         selfTradePrevention: 'decrementAndCancel',
        #         visible: True,
        #         timeInForce: 'GTC',
        #         postOnly: False
        #     }
        #
        marketId = self.safe_string(message, 'market')
        market = self.safe_market(marketId, None, '-')
        symbol = market['symbol']
        messageHash = 'order:' + symbol
        order = self.parse_order(message, market)
        if self.orders is None:
            limit = self.safe_integer(self.options, 'ordersLimit', 1000)
            self.orders = ArrayCacheBySymbolById(limit)
        orders = self.orders
        orders.append(order)
        client.resolve(self.orders, messageHash)

    def handle_my_trade(self, client, message):
        #
        #     {
        #         event: 'fill',
        #         timestamp: 1590964470132,
        #         market: 'ETH-EUR',
        #         orderId: '85d082e1-eda4-4209-9580-248281a29a9a',
        #         fillId: '861d2da5-aa93-475c-8d9a-dce431bd4211',
        #         side: 'sell',
        #         amount: '0.1',
        #         price: '211.46',
        #         taker: True,
        #         fee: '0.056',
        #         feeCurrency: 'EUR'
        #     }
        #
        marketId = self.safe_string(message, 'market')
        market = self.safe_market(marketId, None, '-')
        symbol = market['symbol']
        messageHash = 'myTrades:' + symbol
        trade = self.parse_trade(message, market)
        if self.myTrades is None:
            limit = self.safe_integer(self.options, 'tradesLimit', 1000)
            self.myTrades = ArrayCache(limit)
        tradesArray = self.myTrades
        tradesArray.append(trade)
        client.resolve(tradesArray, messageHash)

    def handle_subscription_status(self, client, message):
        #
        #     {
        #         event: 'subscribed',
        #         subscriptions: {
        #             book: ['BTC-EUR']
        #         }
        #     }
        #
        subscriptions = self.safe_value(message, 'subscriptions', {})
        methods = {
            'book': self.handle_order_book_subscriptions,
        }
        names = list(subscriptions.keys())
        for i in range(0, len(names)):
            name = names[i]
            method = self.safe_value(methods, name)
            if method is not None:
                subscription = self.safe_value(subscriptions, name)
                method(client, message, subscription)
        return message

    def authenticate(self, params={}):
        url = self.urls['api']['ws']
        client = self.client(url)
        messageHash = 'authenticated'
        future = self.safe_value(client.subscriptions, messageHash)
        if future is None:
            timestamp = self.milliseconds()
            stringTimestamp = str(timestamp)
            auth = stringTimestamp + 'GET/' + self.version + '/websocket'
            signature = self.hmac(self.encode(auth), self.encode(self.secret), hashlib.sha256)
            action = 'authenticate'
            request = {
                'action': action,
                'key': self.apiKey,
                'signature': signature,
                'timestamp': timestamp,
            }
            message = self.extend(request, params)
            future = self.watch(url, messageHash, message)
            client.subscriptions[messageHash] = future
        return future

    def handle_authentication_message(self, client, message):
        #
        #     {
        #         event: 'authenticate',
        #         authenticated: True
        #     }
        #
        messageHash = 'authenticated'
        authenticated = self.safe_value(message, 'authenticated', False)
        if authenticated:
            # we resolve the future here permanently so authentication only happens once
            client.resolve(message, messageHash)
        else:
            error = AuthenticationError(self.json(message))
            client.reject(error, messageHash)
            # allows further authentication attempts
            if messageHash in client.subscriptions:
                del client.subscriptions[messageHash]

    def handle_message(self, client, message):
        #
        #     {
        #         event: 'subscribed',
        #         subscriptions: {
        #             book: ['BTC-EUR']
        #         }
        #     }
        #
        #
        #     {
        #         event: 'book',
        #         market: 'BTC-EUR',
        #         nonce: 36729561,
        #         bids: [
        #             ['8513.3', '0'],
        #             ['8518.8', '0.64236203'],
        #             ['8513.6', '0.32435481'],
        #         ],
        #         asks: []
        #     }
        #
        #     {
        #         action: 'getBook',
        #         response: {
        #             market: 'BTC-EUR',
        #             nonce: 36946120,
        #             bids: [
        #                 ['8494.9', '0.24399521'],
        #                 ['8494.8', '0.34884085'],
        #                 ['8493.9', '0.14535128'],
        #             ],
        #             asks: [
        #                 ['8495', '0.46982463'],
        #                 ['8495.1', '0.12178267'],
        #                 ['8496.2', '0.21924143'],
        #             ]
        #         }
        #     }
        #
        #     {
        #         event: 'authenticate',
        #         authenticated: True
        #     }
        #
        methods = {
            'subscribed': self.handle_subscription_status,
            'book': self.handle_order_book,
            'getBook': self.handle_order_book_snapshot,
            'trade': self.handle_trade,
            'candle': self.handle_ohlcv,
            'ticker24h': self.handle_ticker,
            'authenticate': self.handle_authentication_message,
            'order': self.handle_order,
            'fill': self.handle_my_trade,
        }
        event = self.safe_string(message, 'event')
        method = self.safe_value(methods, event)
        if method is None:
            action = self.safe_string(message, 'action')
            method = self.safe_value(methods, action)
            if method is None:
                return message
            else:
                return method(client, message)
        else:
            return method(client, message)
