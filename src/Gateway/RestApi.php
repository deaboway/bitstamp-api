<?php

namespace Shapecode\Bitstamp\Gateway;

use Doctrine\Common\Collections\ArrayCollection;
use Shapecode\Bitstamp\Model\OrderBook;
use Shapecode\Bitstamp\Model\OrderBookCollection;
use Shapecode\Bitstamp\Model\Ticker;
use Shapecode\Bitstamp\Model\Transaction;

/**
 * Class RestApi
 *
 * @package Shapecode\Bitstamp\Gateway
 * @author  Nikita Loges
 */
class RestApi
{

    const GET = 'GET';
    const POST = 'POST';

    const BTCUSD = 'btcusd';
    const BTCEUR = 'btceur';
    const EURUSD = 'eurusd';
    const XRPUSD = 'xrpusd';
    const XRPEUR = 'xrpeur';
    const XRPBTC = 'xrpbtc';
    const LTCUSD = 'ltcusd';
    const LTCEUR = 'ltceur';
    const LTCBTC = 'ltcbtc';

    const SELL = 'sell';
    const BUY = 'buy';

    /** @var string */
    protected $customerId;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $secret;

    /** @var Gateway */
    protected $gateway;

    /**
     * @param Gateway $gateway
     * @param string  $apiKey
     * @param string  $secret
     */
    public function __construct(Gateway $gateway, string $customerId, string $apiKey, string $secret)
    {
        $this->gateway = $gateway;
        $this->customerId = $customerId;
        $this->apiKey = $apiKey;
        $this->secret = $secret;
    }

    /**
     * @return Gateway
     */
    public function getGateway(): Gateway
    {
        return $this->gateway;
    }

    /**
     * @param string $pair
     * @param bool   $mapping
     *
     * @return array
     */
    public function ticker(string $pair, bool $mapping = false)
    {
        $response = $this->sendRequest(
            RestApi::GET,
            $this->getGateway()->getApiUrn(['ticker', $pair])
        );

        if ($mapping && $response) {
            $response = $this->getGateway()->deserializeItem(
                $response,
                Ticker::class
            );
        }

        return $response;
    }

    /**
     * @param string $pair
     * @param bool   $mapping
     *
     * @return array
     */
    public function hourlyTicker(string $pair, bool $mapping = false)
    {
        $response = $this->sendRequest(
            RestApi::GET,
            $this->getGateway()->getApiUrn(['ticker_hour', $pair])
        );

        if ($mapping && $response) {
            $response = $this->getGateway()->deserializeItem(
                $response,
                Ticker::class
            );
        }

        return $response;
    }

    /**
     * @param string $pair
     * @param bool   $mapping
     *
     * @return array|OrderBookCollection
     */
    public function orderBook(string $pair, bool $mapping = false)
    {
        $response = $this->sendRequest(
            RestApi::GET,
            $this->getGateway()->getApiUrn(['order_book', $pair])
        );

        if ($mapping && $response) {
            $collection = new OrderBookCollection();
            $collection->setTimestamp($response['timestamp']);
            $collection->setPair($pair);

            $deserializer = function ($item) {
                return $this->getGateway()->deserializeItem(array_combine(['price', 'amount'], $item), OrderBook::class);
            };
            $asks = array_map($deserializer, $response['asks']);
            $bids = array_map($deserializer, $response['bids']);

            $collection->setAsks(new ArrayCollection($asks));
            $collection->setBids(new ArrayCollection($bids));

            $response = $collection;
        }

        return $response;
    }

    /**
     * @param string $pair
     * @param string $time
     * @param bool   $mapping
     *
     * @return array
     */
    public function transactions(string $pair, string $time = 'minute', bool $mapping = false): array
    {
        $options = [
            'query' => ['time' => $time]
        ];
        $response = $this->sendRequest(
            RestApi::GET,
            $this->getGateway()->getApiUrn(['transactions', $pair]),
            $options
        );

        if ($mapping && $response) {
            $response = $this->getGateway()->deserializeItems(
                $response,
                Transaction::class
            );
        }

        return $response;
    }

    /**
     * @param bool $mapping
     *
     * @return array
     */
    public function tradingPairsInfo(bool $mapping = false): array
    {
        $response = $this->sendRequest(
            RestApi::GET,
            $this->getGateway()->getApiUrn(['trading-pairs-info'])
        );
//
//        if ($mapping && $response) {
//            $response = $this->getGateway()->deserializeItems(
//                $response,
//                Transaction::class
//            );
//        }

        return $response;
    }

    /**
     * @param bool $mapping
     *
     * @return array
     */
    public function conversion(string $pair = 'eur_usd'): array
    {
        $response = $this->sendRequest(
            RestApi::GET,
            $this->getGateway()->getApiUrn(['trading-pairs-info'])
        );
//
//        if ($mapping && $response) {
//            $response = $this->getGateway()->deserializeItems(
//                $response,
//                Transaction::class
//            );
//        }

        return $response;
    }

    /**
     * @param bool $mapping
     *
     * @return array
     */
    public function balance(string $pair = 'eur_usd'): array
    {
        $response = $this->sendRequest(
            'POST',
            $this->getGateway()->getApiUrn(['balance'])
        );
//
//        if ($mapping && $response) {
//            $response = $this->getGateway()->deserializeItems(
//                $response,
//                Transaction::class
//            );
//        }

        return $response;
    }

    /**
     * @param bool $mapping
     *
     * @return array
     */
    public function userTransactions(string $pair = 'eur_usd'): array
    {
        $response = $this->sendRequest(
            'POST',
            $this->getGateway()->getApiUrn(['user_transactions'])
        );
//
//        if ($mapping && $response) {
//            $response = $this->getGateway()->deserializeItems(
//                $response,
//                Transaction::class
//            );
//        }

        return $response;
    }

    /**
     * @param bool $mapping
     *
     * @return array
     */
    public function openOrders(string $pair = 'all'): array
    {
        $response = $this->sendRequest(
            'POST',
            $this->getGateway()->getApiUrn(['open_orders', $pair])
        );
//
//        if ($mapping && $response) {
//            $response = $this->getGateway()->deserializeItems(
//                $response,
//                Transaction::class
//            );
//        }

        return $response;
    }

    /**
     * @param bool $mapping
     *
     * @return array
     */
    public function orderStatus(string $orderId): array
    {
        $options['form_params']['id'] = $orderId;

        $response = $this->sendRequest(
            'POST',
            $this->getGateway()->getApiUrn(['order_status'])
        );
//
//        if ($mapping && $response) {
//            $response = $this->getGateway()->deserializeItems(
//                $response,
//                Transaction::class
//            );
//        }

        return $response;
    }

    /**
     * @param bool $mapping
     *
     * @return array
     */
    public function cancelOrder(string $orderId): array
    {
        $options['form_params']['id'] = $orderId;

        $response = $this->sendRequest(
            'POST',
            $this->getGateway()->getApiUrn(['cancel_order'])
        );
//
//        if ($mapping && $response) {
//            $response = $this->getGateway()->deserializeItems(
//                $response,
//                Transaction::class
//            );
//        }

        return $response;
    }

    /**
     * @param bool $mapping
     *
     * @return array
     */
    public function cancelAllOrders(): array
    {
        $response = $this->sendRequest(
            'POST',
            $this->getGateway()->getApiUrn(['cancel_all_orders'])
        );
//
//        if ($mapping && $response) {
//            $response = $this->getGateway()->deserializeItems(
//                $response,
//                Transaction::class
//            );
//        }

        return $response;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @return array
     */
    protected function sendRequest(string $method, string $uri, array $options = []): array
    {
        $nonce = time();

        $options['form_params']['key'] = $this->apiKey;
        $options['form_params']['nonce'] = $nonce;
        $options['form_params']['signature'] = $this->getSignature($nonce);

        return $this->getGateway()->processResponse(
            $this->getGateway()->send($method, $uri, $options)
        );
    }

    protected function getSignature($nonce)
    {
        $message = $nonce . $this->customerId . $this->apiKey;
        $hash = hash_hmac('sha256', $message, $this->secret);
        $signature = strtoupper($hash);

        return $signature;
    }
}
