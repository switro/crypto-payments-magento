<?php

namespace Switro\CryptoPayments\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Switro\CryptoPayments\Helper\Data as SwitroHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class Redirect extends Action
{
    protected $checkoutSession;
    protected $orderRepository;
    protected $switroHelper;
    protected $curl;
    protected $urlBuilder;
    protected $logger;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        SwitroHelper $switroHelper,
        Curl $curl,
        UrlInterface $urlBuilder,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->switroHelper = $switroHelper;
        $this->curl = $curl;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
    }

    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if (!$order || !$order->getId()) {
            $this->logger->error('Switro: order not found in checkout session.');
            $this->messageManager->addErrorMessage(__('Switro: order not found.'));
            return $this->_redirect('checkout/cart');
        }

        $apiKey = trim($this->switroHelper->getConfig('api_key'));
        if (empty($apiKey)) {
            $this->logger->error('Switro: API key is not configured.');
            $this->messageManager->addErrorMessage(__('Switro: API key not configured.'));
            return $this->_redirect('checkout/cart');
        }

        // build items
        $items = [];
        $store = $order->getStore();
        $mediaBaseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $itemData = [
                'item_title' => $item->getName(),
                'item_quantity' => (int)$item->getQtyOrdered(),
                'item_amount' => (float)$item->getRowTotal(),
                'item_description' => $product ? $product->getShortDescription() : '',
            ];

            if ($product && $product->getImage()) {
                $imageUrl = $mediaBaseUrl . 'catalog/product' . $product->getImage();
                $itemData['item_image_url'] = $imageUrl;
            }

            $items[] = $itemData;
        }

        $successUrl = $this->urlBuilder->getUrl('checkout/onepage/success', ['_secure' => true, 'switro_status' => 'success', 'order_id' => $order->getId()]);
        $cancelUrl = $this->urlBuilder->getUrl('checkout/cart', ['_secure' => true, 'switro_status' => 'cancelled', 'order_id' => $order->getId()]);

        $payload = [
            'customer_name' => trim($order->getCustomerName() ?: $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname()),
            'customer_email' => $order->getCustomerEmail(),
            'customer_phone' => $order->getBillingAddress() ? $order->getBillingAddress()->getTelephone() : '',
            'customer_address' => $order->getBillingAddress() ? $order->getBillingAddress()->getStreetLine(1) : '',
            'amount_total' => (float)$order->getGrandTotal(),
            'amount_currency' => $order->getOrderCurrencyCode(),
            'cancel_url' => $cancelUrl,
            'success_url' => $successUrl,
            'items' => $items,
        ];

        if ((float)$order->getShippingAmount() >= 0.01) {
            $payload['amount_shipping'] = (float)$order->getShippingAmount();
        }
        if ((float)$order->getTaxAmount() >= 0.01) {
            $payload['amount_tax'] = (float)$order->getTaxAmount();
        }

        try {
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->addHeader('Authorization', 'Bearer ' . $apiKey);
            $network = $this->switroHelper->getConfig('network') ?: 'mainnet';
            $this->curl->addHeader('x-network', $network);

            $this->curl->post('https://www.switro.com/api/v1/checkout', json_encode($payload));

            $status = $this->curl->getStatus();
            $responseBody = $this->curl->getBody();
            $response = json_decode($responseBody, true);

            if (!empty($response['url']) && !empty($response['id'])) {
                $order->setData('switro_checkout_id', (string)$response['id']);
                $order->save();
                $this->logger->info("Switro: Checkout created for order {$order->getId()}");
                return $this->resultRedirectFactory->create()->setUrl($response['url']);
            } else {
                $msg = __('Invalid response from Switro');
                if (!empty($response['message'])) {
                    $msg = is_array($response['message']) ? implode(', ', $response['message']) : $response['message'];
                }
                $this->logger->error("Switro API error for order {$order->getId()}: " . $msg);
                $this->messageManager->addErrorMessage(__('Switro: %1', $msg));
                return $this->_redirect('checkout/cart');
            }
        } catch (\Exception $e) {
            $this->logger->error("Switro API exception for order {$order->getId()}: " . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Switro: Could not contact Switro API.'));
            return $this->_redirect('checkout/cart');
        }
    }
}
