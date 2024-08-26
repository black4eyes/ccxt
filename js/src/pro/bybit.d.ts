import bybitRest from '../bybit.js';
import type { Int, OHLCV, Str, Strings, Ticker, OrderBook, Order, Trade, Tickers, Position, Balances, OrderType, OrderSide, Num, Dict, Liquidation } from '../base/types.js';
import Client from '../base/ws/Client.js';
export default class bybit extends bybitRest {
    describe(): any;
    requestId(): any;
    getUrlByMarketType(symbol?: Str, isPrivate?: boolean, method?: Str, params?: {}): Promise<any>;
    cleanParams(params: any): any;
    createOrderWs(symbol: string, type: OrderType, side: OrderSide, amount: number, price?: Num, params?: {}): Promise<Order>;
    editOrderWs(id: string, symbol: string, type: OrderType, side: OrderSide, amount?: Num, price?: Num, params?: {}): Promise<Order>;
    cancelOrderWs(id: string, symbol?: Str, params?: {}): Promise<Order>;
    watchTicker(symbol: string, params?: {}): Promise<Ticker>;
    watchTickers(symbols?: Strings, params?: {}): Promise<Tickers>;
    unWatchTickers(symbols?: Strings, params?: {}): Promise<any>;
    unWatchTicker(symbols: string, params?: {}): Promise<any>;
    handleTicker(client: Client, message: any): void;
    watchOHLCV(symbol: string, timeframe?: string, since?: Int, limit?: Int, params?: {}): Promise<OHLCV[]>;
    watchOHLCVForSymbols(symbolsAndTimeframes: string[][], since?: Int, limit?: Int, params?: {}): Promise<import("../base/types.js").Dictionary<import("../base/types.js").Dictionary<OHLCV[]>>>;
    handleOHLCV(client: Client, message: any): void;
    parseWsOHLCV(ohlcv: any, market?: any): OHLCV;
    watchOrderBook(symbol: string, limit?: Int, params?: {}): Promise<OrderBook>;
    watchOrderBookForSymbols(symbols: string[], limit?: Int, params?: {}): Promise<OrderBook>;
    unWatchOrderBookForSymbols(symbols: Strings, params?: {}): Promise<any>;
    unWatchOrderBook(symbol: string, params?: {}): Promise<any>;
    handleOrderBook(client: Client, message: any): void;
    handleDelta(bookside: any, delta: any): void;
    handleDeltas(bookside: any, deltas: any): void;
    watchTrades(symbol: string, since?: Int, limit?: Int, params?: {}): Promise<Trade[]>;
    watchTradesForSymbols(symbols: string[], since?: Int, limit?: Int, params?: {}): Promise<Trade[]>;
    unWatchTradesForSymbols(symbols: Strings, params?: {}): Promise<any>;
    unWatchTrades(symbol: string, params?: {}): Promise<any>;
    handleTrades(client: Client, message: any): void;
    parseWsTrade(trade: any, market?: any): Trade;
    getPrivateType(url: any): "spot" | "unified" | "usdc";
    watchMyTrades(symbol?: Str, since?: Int, limit?: Int, params?: {}): Promise<Trade[]>;
    unWatchMyTrades(symbol?: Str, params?: {}): Promise<any>;
    handleMyTrades(client: Client, message: any): void;
    watchPositions(symbols?: Strings, since?: Int, limit?: Int, params?: {}): Promise<Position[]>;
    setPositionsCache(client: Client, symbols?: Strings): void;
    loadPositionsSnapshot(client: any, messageHash: any): Promise<void>;
    handlePositions(client: any, message: any): void;
    watchLiquidations(symbol: string, since?: Int, limit?: Int, params?: {}): Promise<Liquidation[]>;
    handleLiquidation(client: Client, message: any): void;
    parseWsLiquidation(liquidation: any, market?: any): Liquidation;
    watchOrders(symbol?: Str, since?: Int, limit?: Int, params?: {}): Promise<Order[]>;
    unWatchOrders(symbol?: Str, params?: {}): Promise<any>;
    handleOrderWs(client: Client, message: any): void;
    handleOrder(client: Client, message: any): void;
    parseWsSpotOrder(order: any, market?: any): Order;
    watchBalance(params?: {}): Promise<Balances>;
    handleBalance(client: Client, message: any): void;
    parseWsBalance(balance: any, accountType?: any): void;
    watchTopics(url: any, messageHashes: any, topics: any, params?: {}): Promise<any>;
    unWatchTopics(url: string, topic: string, symbols: string[], messageHashes: string[], subMessageHashes: string[], topics: any, params?: {}): Promise<any>;
    authenticate(url: any, params?: {}): Promise<any>;
    handleErrorMessage(client: Client, message: any): boolean;
    handleMessage(client: Client, message: any): void;
    ping(client: Client): {
        req_id: any;
        op: string;
    };
    handlePong(client: Client, message: any): any;
    handleAuthenticate(client: Client, message: any): any;
    handleSubscriptionStatus(client: Client, message: any): any;
    handleUnSubscribe(client: Client, message: any): any;
    cleanCache(subscription: Dict): void;
}
