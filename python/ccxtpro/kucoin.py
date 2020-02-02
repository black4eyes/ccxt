# -*- coding: utf-8 -*-

# PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
# https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

from ccxtpro.base.exchange import Exchange
import ccxt.async_support as ccxt
from ccxt.base.errors import ExchangeError


class kucoin(Exchange, ccxt.kucoin):

    def describe(self):
        return self.deep_extend(super(kucoin, self).describe(), {
            'has': {
                'ws': True,
                'watchOrderBook': True,
                'watchTickers': False,  # for now
                'watchTicker': True,
                'watchTrades': True,
                'watchBalance': False,  # for now
                'watchOHLCV': False,  # missing on the exchange side
            },
            'options': {
                'tradesLimit': 1000,
                'watchOrderBookRate': 100,  # get updates every 100ms or 1000ms
            },
            'streaming': {
                # kucoin does not support built-in ws protocol-level ping-pong
                # instead it requires a custom json-based text ping-pong
                # https://docs.kucoin.com/#ping
                'ping': self.ping,
            },
        })

    async def negotiate(self, params={}):
        client = self.client('ws')
        messageHash = 'negotiate'
        future = self.safe_value(client.subscriptions, messageHash)
        if future is None:
            future = client.future(messageHash)
            client.subscriptions[messageHash] = future
            response = None
            throwException = False
            if self.check_required_credentials(throwException):
                response = await self.privatePostBulletPrivate()
                #
                #     {
                #         code: "200000",
                #         data: {
                #             instanceServers: [
                #                 {
                #                     pingInterval:  50000,
                #                     endpoint: "wss://push-private.kucoin.com/endpoint",
                #                     protocol: "websocket",
                #                     encrypt: True,
                #                     pingTimeout: 10000
                #                 }
                #             ],
                #             token: "2neAiuYvAU61ZDXANAGAsiL4-iAExhsBXZxftpOeh_55i3Ysy2q2LEsEWU64mdzUOPusi34M_wGoSf7iNyEWJ1UQy47YbpY4zVdzilNP-Bj3iXzrjjGlWtiYB9J6i9GjsxUuhPw3BlrzazF6ghq4Lzf7scStOz3KkxjwpsOBCH4=.WNQmhZQeUKIkh97KYgU0Lg=="
                #         }
                #     }
                #
            else:
                response = await self.publicPostBulletPublic()
            client.resolve(response, messageHash)
            # data = self.safe_value(response, 'data', {})
            # instanceServers = self.safe_value(data, 'instanceServers', [])
            # firstServer = self.safe_value(instanceServers, 0, {})
            # endpoint = self.safe_string(firstServer, 'endpoint')
            # token = self.safe_string(data, 'token')
        return await future

    async def subscribe(self, negotiation, topic, method, symbol, params={}):
        await self.load_markets()
        market = self.market(symbol)
        data = self.safe_value(negotiation, 'data', {})
        instanceServers = self.safe_value(data, 'instanceServers', [])
        firstServer = self.safe_value(instanceServers, 0, {})
        endpoint = self.safe_string(firstServer, 'endpoint')
        token = self.safe_string(data, 'token')
        nonce = self.nonce()
        query = {
            'token': token,
            'acceptUserMessage': 'true',
            # 'connectId': nonce,  # user-defined id is supported, received by handleSystemStatus
        }
        url = endpoint + '?' + self.urlencode(query)
        # topic = '/market/snapshot'  # '/market/ticker'
        messageHash = topic + ':' + market['id']
        subscribe = {
            'id': nonce,
            'type': 'subscribe',
            'topic': messageHash,
            'response': True,
        }
        subscription = {
            'id': str(nonce),
            'symbol': symbol,
            'topic': topic,
            'messageHash': messageHash,
            'method': method,
        }
        request = self.extend(subscribe, params)
        return await self.watch(url, messageHash, request, messageHash, subscription)

    async def watch_ticker(self, symbol, params={}):
        await self.load_markets()
        negotiate = self.negotiate()
        topic = '/market/snapshot'
        return await self.after_async(negotiate, self.subscribe, topic, None, symbol, params)

    def handle_ticker(self, client, message):
        #
        # updates come in every 2 sec unless there
        # were no changes since the previous update
        #
        #     {
        #         "data": {
        #             "sequence": "1545896669291",
        #             "data": {
        #                 "trading": True,
        #                 "symbol": "KCS-BTC",
        #                 "buy": 0.00011,
        #                 "sell": 0.00012,
        #                 "sort": 100,
        #                 "volValue": 3.13851792584,  # total
        #                 "baseCurrency": "KCS",
        #                 "market": "BTC",
        #                 "quoteCurrency": "BTC",
        #                 "symbolCode": "KCS-BTC",
        #                 "datetime": 1548388122031,
        #                 "high": 0.00013,
        #                 "vol": 27514.34842,
        #                 "low": 0.0001,
        #                 "changePrice": -1.0e-5,
        #                 "changeRate": -0.0769,
        #                 "lastTradedPrice": 0.00012,
        #                 "board": 0,
        #                 "mark": 0
        #             }
        #         },
        #         "subject": "trade.snapshot",
        #         "topic": "/market/snapshot:KCS-BTC",
        #         "type": "message"
        #     }
        #
        data = self.safe_value(message, 'data', {})
        rawTicker = self.safe_value(data, 'data', {})
        ticker = self.parse_ticker(rawTicker)
        symbol = ticker['symbol']
        self.tickers[symbol] = ticker
        messageHash = self.safe_string(message, 'topic')
        if messageHash is not None:
            client.resolve(ticker, messageHash)
        return message

    async def watch_trades(self, symbol, since=None, limit=None, params={}):
        await self.load_markets()
        negotiate = self.negotiate()
        topic = '/market/match'
        future = self.after_async(negotiate, self.subscribe, topic, None, symbol, since, params)
        return await self.after(future, self.filterBySinceLimit, since, limit)

    def handle_trade(self, client, message):
        #
        #     {
        #         data: {
        #             sequence: '1568787654360',
        #             symbol: 'BTC-USDT',
        #             side: 'buy',
        #             size: '0.00536577',
        #             price: '9345',
        #             takerOrderId: '5e356c4a9f1a790008f8d921',
        #             time: '1580559434436443257',
        #             type: 'match',
        #             makerOrderId: '5e356bffedf0010008fa5d7f',
        #             tradeId: '5e356c4aeefabd62c62a1ece'
        #         },
        #         subject: 'trade.l3match',
        #         topic: '/market/match:BTC-USDT',
        #         type: 'message'
        #     }
        #
        data = self.safe_value(message, 'data', {})
        trade = self.parse_trade(data)
        messageHash = self.safe_string(message, 'topic')
        symbol = trade['symbol']
        array = self.safe_value(self.trades, symbol, [])
        array.append(trade)
        length = len(array)
        if length > self.options['tradesLimit']:
            array.pop(0)
        self.trades[symbol] = array
        client.resolve(array, messageHash)
        return message

    async def watch_order_book(self, symbol, limit=None, params={}):
        #
        # https://docs.kucoin.com/#level-2-market-data
        #
        # 1. After receiving the websocket Level 2 data flow, cache the data.
        # 2. Initiate a REST request to get the snapshot data of Level 2 order book.
        # 3. Playback the cached Level 2 data flow.
        # 4. Apply the new Level 2 data flow to the local snapshot to ensure that
        # the sequence of the new Level 2 update lines up with the sequence of
        # the previous Level 2 data. Discard all the message prior to that
        # sequence, and then playback the change to snapshot.
        # 5. Update the level2 full data based on sequence according to the
        # size. If the price is 0, ignore the messages and update the sequence.
        # If the size=0, update the sequence and remove the price of which the
        # size is 0 out of level 2. For other cases, please update the price.
        #
        if limit is not None:
            if (limit != 20) and (limit != 100):
                raise ExchangeError(self.id + " watchOrderBook 'limit' argument must be None, 20 or 100")
        await self.load_markets()
        negotiate = self.negotiate()
        topic = '/market/level2'
        future = self.after_async(negotiate, self.subscribe, topic, self.handle_order_book_subscription, symbol, params)
        return await self.after(future, self.limit_order_book, symbol, limit, params)

    def limit_order_book(self, orderbook, symbol, limit=None, params={}):
        return orderbook.limit(limit)

    async def fetch_order_book_snapshot(self, client, message, subscription):
        symbol = self.safe_string(subscription, 'symbol')
        limit = self.safe_integer(subscription, 'limit')
        messageHash = self.safe_string(subscription, 'messageHash')
        # 2. Initiate a REST request to get the snapshot data of Level 2 order book.
        # todo: self is a synch blocking call in ccxt.php - make it async
        snapshot = await self.fetch_order_book(symbol, limit)
        orderbook = self.orderbooks[symbol]
        orderbook.reset(snapshot)
        # unroll the accumulated deltas
        messages = orderbook.cache
        # 3. Playback the cached Level 2 data flow.
        for i in range(0, len(messages)):
            message = messages[i]
            self.handle_order_book_message(client, message, orderbook)
        self.orderbooks[symbol] = orderbook
        client.resolve(orderbook, messageHash)

    def handle_delta(self, bookside, delta, nonce):
        price = self.safe_float(delta, 0)
        if price > 0:
            sequence = self.safe_integer(delta, 2)
            if sequence > nonce:
                amount = self.safe_float(delta, 1)
                bookside.store(price, amount)

    def handle_deltas(self, bookside, deltas, nonce):
        for i in range(0, len(deltas)):
            self.handle_delta(bookside, deltas[i], nonce)

    def handle_order_book_message(self, client, message, orderbook):
        #
        #     {
        #         "type":"message",
        #         "topic":"/market/level2:BTC-USDT",
        #         "subject":"trade.l2update",
        #         "data":{
        #             "sequenceStart":1545896669105,
        #             "sequenceEnd":1545896669106,
        #             "symbol":"BTC-USDT",
        #             "changes": {
        #                 "asks": [["6","1","1545896669105"]],  # price, size, sequence
        #                 "bids": [["4","1","1545896669106"]]
        #             }
        #         }
        #     }
        #
        data = self.safe_value(message, 'data', {})
        sequenceEnd = self.safe_integer(data, 'sequenceEnd')
        # 4. Apply the new Level 2 data flow to the local snapshot to ensure that
        # the sequence of the new Level 2 update lines up with the sequence of
        # the previous Level 2 data. Discard all the message prior to that
        # sequence, and then playback the change to snapshot.
        if sequenceEnd > orderbook['nonce']:
            sequenceStart = self.safe_integer(message, 'sequenceStart')
            if (sequenceStart is not None) and ((sequenceStart - 1) > orderbook['nonce']):
                # todo: client.reject from handleOrderBookMessage properly
                raise ExchangeError(self.id + ' handleOrderBook received an out-of-order nonce')
            changes = self.safe_value(data, 'changes', {})
            asks = self.safe_value(changes, 'asks', [])
            bids = self.safe_value(changes, 'bids', [])
            asks = self.sort_by(asks, 2)  # sort by sequence
            bids = self.sort_by(bids, 2)
            # 5. Update the level2 full data based on sequence according to the
            # size. If the price is 0, ignore the messages and update the sequence.
            # If the size=0, update the sequence and remove the price of which the
            # size is 0 out of level 2. For other cases, please update the price.
            self.handle_deltas(orderbook['asks'], asks, orderbook['nonce'])
            self.handle_deltas(orderbook['bids'], bids, orderbook['nonce'])
            orderbook['nonce'] = sequenceEnd
            orderbook['timestamp'] = None
            orderbook['datetime'] = None
        return orderbook

    def handle_order_book(self, client, message):
        #
        # initial snapshot is fetched with ccxt's fetchOrderBook
        # the feed does not include a snapshot, just the deltas
        #
        #     {
        #         "type":"message",
        #         "topic":"/market/level2:BTC-USDT",
        #         "subject":"trade.l2update",
        #         "data":{
        #             "sequenceStart":1545896669105,
        #             "sequenceEnd":1545896669106,
        #             "symbol":"BTC-USDT",
        #             "changes": {
        #                 "asks": [["6","1","1545896669105"]],  # price, size, sequence
        #                 "bids": [["4","1","1545896669106"]]
        #             }
        #         }
        #     }
        #
        messageHash = self.safe_string(message, 'topic')
        data = self.safe_value(message, 'data')
        marketId = self.safe_string(data, 'symbol')
        market = None
        symbol = None
        if marketId is not None:
            if marketId in self.markets_by_id:
                market = self.markets_by_id[marketId]
                symbol = market['symbol']
        orderbook = self.orderbooks[symbol]
        if orderbook['nonce'] is None:
            # 1. After receiving the websocket Level 2 data flow, cache the data.
            orderbook.cache.append(message)
        else:
            self.handle_order_book_message(client, message, orderbook)
            client.resolve(orderbook, messageHash)

    def sign_message(self, client, messageHash, message, params={}):
        # todo: implement kucoin signMessage
        return message

    def handle_order_book_subscription(self, client, message, subscription):
        symbol = self.safe_string(subscription, 'symbol')
        limit = self.safe_string(subscription, 'limit')
        if symbol in self.orderbooks:
            del self.orderbooks[symbol]
        self.orderbooks[symbol] = self.order_book({}, limit)
        # fetch the snapshot in a separate async call
        self.spawn(self.fetch_order_book_snapshot, client, message, subscription)

    def handle_subscription_status(self, client, message):
        #
        #     {
        #         id: '1578090438322',
        #         type: 'ack'
        #     }
        #
        id = self.safe_string(message, 'id')
        subscriptionsById = self.index_by(client.subscriptions, 'id')
        subscription = self.safe_value(subscriptionsById, id, {})
        method = self.safe_value(subscription, 'method')
        if method is not None:
            method(client, message, subscription)
        return message

    def handle_system_status(self, client, message):
        #
        # todo: answer the question whether handleSystemStatus should be renamed
        # and unified as handleStatus for any usage pattern that
        # involves system status and maintenance updates
        #
        #     {
        #         id: '1578090234088',  # connectId
        #         type: 'welcome',
        #     }
        #
        return message

    def handle_subject(self, client, message):
        #
        #     {
        #         "type":"message",
        #         "topic":"/market/level2:BTC-USDT",
        #         "subject":"trade.l2update",
        #         "data":{
        #             "sequenceStart":1545896669105,
        #             "sequenceEnd":1545896669106,
        #             "symbol":"BTC-USDT",
        #             "changes": {
        #                 "asks": [["6","1","1545896669105"]],  # price, size, sequence
        #                 "bids": [["4","1","1545896669106"]]
        #             }
        #         }
        #     }
        #
        subject = self.safe_string(message, 'subject')
        methods = {
            'trade.l2update': self.handle_order_book,
            'trade.snapshot': self.handle_ticker,
            'trade.l3match': self.handle_trade,
        }
        method = self.safe_value(methods, subject)
        if method is None:
            return message
        else:
            return method(client, message)

    def ping(self, client):
        # kucoin does not support built-in ws protocol-level ping-pong
        # instead it requires a custom json-based text ping-pong
        # https://docs.kucoin.com/#ping
        id = str(self.nonce())
        return {
            'id': id,
            'type': 'ping',
        }

    def handle_pong(self, client, message):
        # https://docs.kucoin.com/#ping
        client.lastPong = self.milliseconds()
        return message

    def handle_error_message(self, client, message):
        return message

    def handle_message(self, client, message):
        if self.handle_error_message(client, message):
            type = self.safe_string(message, 'type')
            methods = {
                # 'heartbeat': self.handleHeartbeat,
                'welcome': self.handle_system_status,
                'ack': self.handle_subscription_status,
                'message': self.handle_subject,
                'pong': self.handle_pong,
            }
            method = self.safe_value(methods, type)
            if method is None:
                return message
            else:
                return method(client, message)
