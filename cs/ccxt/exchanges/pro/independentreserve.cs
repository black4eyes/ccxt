namespace ccxt.pro;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code


public partial class independentreserve { public independentreserve(object args = null) : base(args) { } }
public partial class independentreserve : ccxt.independentreserve
{
    public override object describe()
    {
        return this.deepExtend(base.describe(), new Dictionary<string, object>() {
            { "has", new Dictionary<string, object>() {
                { "ws", true },
                { "watchBalance", false },
                { "watchTicker", false },
                { "watchTickers", false },
                { "watchTrades", true },
                { "watchMyTrades", false },
                { "watchOrders", false },
                { "watchOrderBook", true },
                { "watchOHLCV", false },
            } },
            { "urls", new Dictionary<string, object>() {
                { "api", new Dictionary<string, object>() {
                    { "ws", "wss://websockets.independentreserve.com" },
                } },
            } },
            { "options", new Dictionary<string, object>() {
                { "watchOrderBook", new Dictionary<string, object>() {
                    { "checksum", true },
                } },
            } },
            { "streaming", new Dictionary<string, object>() {} },
            { "exceptions", new Dictionary<string, object>() {} },
        });
    }

    public async override Task<object> watchTrades(object symbol, object since = null, object limit = null, object parameters = null)
    {
        /**
        * @method
        * @name independentreserve#watchTrades
        * @description get the list of most recent trades for a particular symbol
        * @param {string} symbol unified symbol of the market to fetch trades for
        * @param {int} [since] timestamp in ms of the earliest trade to fetch
        * @param {int} [limit] the maximum amount of trades to fetch
        * @param {object} [params] extra parameters specific to the exchange API endpoint
        * @returns {object[]} a list of [trade structures]{@link https://docs.ccxt.com/#/?id=public-trades}
        */
        parameters ??= new Dictionary<string, object>();
        await this.loadMarkets();
        object market = this.market(symbol);
        symbol = getValue(market, "symbol");
        object url = add(add(add(add(getValue(getValue(this.urls, "api"), "ws"), "?subscribe=ticker-"), getValue(market, "base")), "-"), getValue(market, "quote"));
        object messageHash = add("trades:", symbol);
        object trades = await this.watch(url, messageHash, null, messageHash);
        return this.filterBySinceLimit(trades, since, limit, "timestamp", true);
    }

    public virtual void handleTrades(WebSocketClient client, object message)
    {
        //
        //    {
        //        "Channel": "ticker-btc-usd",
        //        "Nonce": 130,
        //        "Data": {
        //          "TradeGuid": "7a669f2a-d564-472b-8493-6ef982eb1e96",
        //          "Pair": "btc-aud",
        //          "TradeDate": "2023-02-12T10:04:13.0804889+11:00",
        //          "Price": 31640,
        //          "Volume": 0.00079029,
        //          "BidGuid": "ba8a78b5-be69-4d33-92bb-9df0daa6314e",
        //          "OfferGuid": "27d20270-f21f-4c25-9905-152e70b2f6ec",
        //          "Side": "Buy"
        //        },
        //        "Time": 1676156653111,
        //        "Event": "Trade"
        //    }
        //
        object data = this.safeValue(message, "Data", new Dictionary<string, object>() {});
        object marketId = this.safeString(data, "Pair");
        object symbol = this.safeSymbol(marketId, null, "-");
        object messageHash = add("trades:", symbol);
        object stored = this.safeValue(this.trades, symbol);
        if (isTrue(isEqual(stored, null)))
        {
            object limit = this.safeInteger(this.options, "tradesLimit", 1000);
            stored = new ArrayCache(limit);
            ((IDictionary<string,object>)this.trades)[(string)symbol] = stored;
        }
        object trade = this.parseWsTrade(data);
        callDynamically(stored, "append", new object[] {trade});
        ((IDictionary<string,object>)this.trades)[(string)symbol] = stored;
        callDynamically(client as WebSocketClient, "resolve", new object[] {getValue(this.trades, symbol), messageHash});
    }

    public override object parseWsTrade(object trade, object market = null)
    {
        //
        //    {
        //        "TradeGuid": "2f316718-0d0b-4e33-a30c-c2c06f3cfb34",
        //        "Pair": "xbt-aud",
        //        "TradeDate": "2023-02-12T09:22:35.4207494+11:00",
        //        "Price": 31573.8,
        //        "Volume": 0.05,
        //        "BidGuid": "adb63d74-4c02-47f9-9cc3-f287e3b48ab6",
        //        "OfferGuid": "b94d9bc4-addd-4633-a18f-69cf7e1b6f47",
        //        "Side": "Buy"
        //    }
        //
        object datetime = this.safeString(trade, "TradeDate");
        object marketId = this.safeString(market, "Pair");
        return this.safeTrade(new Dictionary<string, object>() {
            { "info", trade },
            { "id", this.safeString(trade, "TradeGuid") },
            { "order", this.safeString(trade, "orderNo") },
            { "symbol", this.safeSymbol(marketId, market, "-") },
            { "side", this.safeStringLower(trade, "Side") },
            { "type", null },
            { "takerOrMaker", null },
            { "price", this.safeString(trade, "Price") },
            { "amount", this.safeString(trade, "Volume") },
            { "cost", null },
            { "fee", null },
            { "timestamp", this.parse8601(datetime) },
            { "datetime", datetime },
        }, market);
    }

    public async override Task<object> watchOrderBook(object symbol, object limit = null, object parameters = null)
    {
        /**
        * @method
        * @name independentreserve#watchOrderBook
        * @description watches information on open orders with bid (buy) and ask (sell) prices, volumes and other data
        * @param {string} symbol unified symbol of the market to fetch the order book for
        * @param {int} [limit] the maximum amount of order book entries to return
        * @param {object} [params] extra parameters specific to the exchange API endpoint
        * @returns {object} A dictionary of [order book structures]{@link https://docs.ccxt.com/#/?id=order-book-structure} indexed by market symbols
        */
        parameters ??= new Dictionary<string, object>();
        await this.loadMarkets();
        object market = this.market(symbol);
        symbol = getValue(market, "symbol");
        if (isTrue(isEqual(limit, null)))
        {
            limit = 100;
        }
        object limitString = this.numberToString(limit);
        object url = add(add(add(add(add(add(getValue(getValue(this.urls, "api"), "ws"), "/orderbook/"), limitString), "?subscribe="), getValue(market, "base")), "-"), getValue(market, "quote"));
        object messageHash = add(add(add("orderbook:", symbol), ":"), limitString);
        object subscription = new Dictionary<string, object>() {
            { "receivedSnapshot", false },
        };
        object orderbook = await this.watch(url, messageHash, null, messageHash, subscription);
        return (orderbook as IOrderBook).limit();
    }

    public virtual void handleOrderBook(WebSocketClient client, object message)
    {
        //
        //    {
        //        "Channel": "orderbook/1/eth/aud",
        //        "Data": {
        //          "Bids": [
        //            {
        //              "Price": 2198.09,
        //              "Volume": 0.16143952,
        //            },
        //          ],
        //          "Offers": [
        //            {
        //              "Price": 2201.25,
        //              "Volume": 15,
        //            },
        //          ],
        //          "Crc32": 1519697650,
        //        },
        //        "Time": 1676150558254,
        //        "Event": "OrderBookSnapshot",
        //    }
        //
        object eventVar = this.safeString(message, "Event");
        object channel = this.safeString(message, "Channel");
        object parts = ((string)channel).Split(new [] {((string)"/")}, StringSplitOptions.None).ToList<object>();
        object depth = this.safeString(parts, 1);
        object baseId = this.safeString(parts, 2);
        object quoteId = this.safeString(parts, 3);
        object bs = this.safeCurrencyCode(baseId);
        object quote = this.safeCurrencyCode(quoteId);
        object symbol = add(add(bs, "/"), quote);
        object orderBook = this.safeDict(message, "Data", new Dictionary<string, object>() {});
        object messageHash = add(add(add("orderbook:", symbol), ":"), depth);
        object subscription = this.safeValue(((WebSocketClient)client).subscriptions, messageHash, new Dictionary<string, object>() {});
        object receivedSnapshot = this.safeBool(subscription, "receivedSnapshot", false);
        object timestamp = this.safeInteger(message, "Time");
        // let orderbook = this.safeValue (this.orderbooks, symbol);
        if (!isTrue((inOp(this.orderbooks, symbol))))
        {
            ((IDictionary<string,object>)this.orderbooks)[(string)symbol] = this.orderBook(new Dictionary<string, object>() {});
        }
        object orderbook = getValue(this.orderbooks, symbol);
        if (isTrue(isEqual(eventVar, "OrderBookSnapshot")))
        {
            object snapshot = this.parseOrderBook(orderBook, symbol, timestamp, "Bids", "Offers", "Price", "Volume");
            (orderbook as IOrderBook).reset(snapshot);
            ((IDictionary<string,object>)subscription)["receivedSnapshot"] = true;
        } else
        {
            object asks = this.safeList(orderBook, "Offers", new List<object>() {});
            object bids = this.safeList(orderBook, "Bids", new List<object>() {});
            this.handleDeltas(getValue(orderbook, "asks"), asks);
            this.handleDeltas(getValue(orderbook, "bids"), bids);
            ((IDictionary<string,object>)orderbook)["timestamp"] = timestamp;
            ((IDictionary<string,object>)orderbook)["datetime"] = this.iso8601(timestamp);
        }
        object checksum = this.handleOption("watchOrderBook", "checksum", true);
        if (isTrue(isTrue(checksum) && isTrue(receivedSnapshot)))
        {
            object storedAsks = getValue(orderbook, "asks");
            object storedBids = getValue(orderbook, "bids");
            object asksLength = getArrayLength(storedAsks);
            object bidsLength = getArrayLength(storedBids);
            object payload = "";
            for (object i = 0; isLessThan(i, 10); postFixIncrement(ref i))
            {
                if (isTrue(isLessThan(i, bidsLength)))
                {
                    payload = add(add(payload, this.valueToChecksum(getValue(getValue(storedBids, i), 0))), this.valueToChecksum(getValue(getValue(storedBids, i), 1)));
                }
            }
            for (object i = 0; isLessThan(i, 10); postFixIncrement(ref i))
            {
                if (isTrue(isLessThan(i, asksLength)))
                {
                    payload = add(add(payload, this.valueToChecksum(getValue(getValue(storedAsks, i), 0))), this.valueToChecksum(getValue(getValue(storedAsks, i), 1)));
                }
            }
            object calculatedChecksum = this.crc32(payload, true);
            object responseChecksum = this.safeInteger(orderBook, "Crc32");
            if (isTrue(!isEqual(calculatedChecksum, responseChecksum)))
            {
                var error = new ChecksumError(add(add(this.id, " "), this.orderbookChecksumMessage(symbol)));


                ((WebSocketClient)client).reject(error, messageHash);
            }
        }
        if (isTrue(receivedSnapshot))
        {
            callDynamically(client as WebSocketClient, "resolve", new object[] {orderbook, messageHash});
        }
    }

    public virtual object valueToChecksum(object value)
    {
        object result = toFixed(value, 8);
        result = ((string)result).Replace((string)".", (string)"");
        // remove leading zeros
        result = this.parseNumber(result);
        result = this.numberToString(result);
        return result;
    }

    public override void handleDelta(object bookside, object delta)
    {
        object bidAsk = this.parseBidAsk(delta, "Price", "Volume");
        (bookside as IOrderBookSide).storeArray(bidAsk);
    }

    public override void handleDeltas(object bookside, object deltas)
    {
        for (object i = 0; isLessThan(i, getArrayLength(deltas)); postFixIncrement(ref i))
        {
            this.handleDelta(bookside, getValue(deltas, i));
        }
    }

    public virtual object handleHeartbeat(WebSocketClient client, object message)
    {
        //
        //    {
        //        "Time": 1676156208182,
        //        "Event": "Heartbeat"
        //    }
        //
        return message;
    }

    public virtual object handleSubscriptions(WebSocketClient client, object message)
    {
        //
        //    {
        //        "Data": [ "ticker-btc-sgd" ],
        //        "Time": 1676157556223,
        //        "Event": "Subscriptions"
        //    }
        //
        return message;
    }

    public override void handleMessage(WebSocketClient client, object message)
    {
        object eventVar = this.safeString(message, "Event");
        object handlers = new Dictionary<string, object>() {
            { "Subscriptions", this.handleSubscriptions },
            { "Heartbeat", this.handleHeartbeat },
            { "Trade", this.handleTrades },
            { "OrderBookSnapshot", this.handleOrderBook },
            { "OrderBookChange", this.handleOrderBook },
        };
        object handler = this.safeValue(handlers, eventVar);
        if (isTrue(!isEqual(handler, null)))
        {
            DynamicInvoker.InvokeMethod(handler, new object[] { client, message});
            return;
        }
        throw new NotSupported ((string)add(add(this.id, " received an unsupported message: "), this.json(message))) ;
    }
}
