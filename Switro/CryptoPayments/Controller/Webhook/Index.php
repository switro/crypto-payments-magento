<?php
namespace Switro\CryptoPayments\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\HTTP\Client\Curl;
use Switro\CryptoPayments\Helper\Data as SwitroHelper;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Index extends Action implements CsrfAwareActionInterface
{
    protected $curl;
    protected $switroHelper;
    protected $orderFactory;
    protected $orderRepository;

    public function __construct(
        Context $context,
        Curl $curl,
        SwitroHelper $switroHelper,
        OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->switroHelper = $switroHelper;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        // Allow public POSTs - no auth. Expect JSON body.
        $content = $this->getRequest()->getContent();
        $body = json_decode($content, true);

        if (empty($body['checkout_id']) || empty($body['payment_id'])) {
            $this->getResponse()->setStatusCode(400);
            $this->getResponse()->setBody(json_encode(['error' => 'Invalid payload']));
            return;
        }

        $checkoutId = (string) $body['checkout_id'];
        $paymentId = (string) $body['payment_id'];

        // find order by switro_checkout_id
        $order = null;
        $collection = $this->orderFactory->create()->getCollection()
            ->addFieldToFilter('switro_checkout_id', $checkoutId)
            ->setPageSize(1);

        if ($collection->getSize() == 0) {
            $this->getResponse()->setStatusCode(404);
            $this->getResponse()->setBody(json_encode(['error' => 'Order not found']));
            return;
        }

        $order = $collection->getFirstItem();
        if (!$order || !$order->getId()) {
            $this->getResponse()->setStatusCode(404);
            $this->getResponse()->setBody(json_encode(['error' => 'Order not found']));
            return;
        }

        $apiKey = trim($this->switroHelper->getConfig('api_key'));
        if (empty($apiKey)) {
            $this->getResponse()->setStatusCode(403);
            $this->getResponse()->setBody(json_encode(['error' => 'API key not configured']));
            return;
        }

        try {
            $this->curl->addHeader('Authorization', 'Bearer ' . $apiKey);
            $this->curl->get('https://www.switro.com/api/v1/payment/' . $paymentId);
            $status = $this->curl->getStatus();
            $response = json_decode($this->curl->getBody(), true);

            if ($status != 200 || empty($response) || empty($response['status'])) {
                $this->getResponse()->setStatusCode(422);
                $this->getResponse()->setBody(json_encode(['error' => 'Invalid payment response']));
                return;
            }

            if ($response['status'] === 'confirmed') {
                // mark order paid/processing
                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                $order->addStatusHistoryComment('Switro Payment Confirmed. Transaction: ' . ($response['transaction_url'] ?? ''));
                $order->setData('switro_payment_id', $paymentId);
                $order->setData('switro_network', isset($response['network']) ? $response['network'] : '');
                $order->setData('switro_amount_original', isset($response['amount_original']) ? $response['amount_original'] : '');
                // register payment transaction on the order
                try {
                    $payment = $order->getPayment();
                    $payment->setTransactionId($paymentId);
                    $payment->setIsTransactionClosed(0);
                    $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
                } catch (\Exception $e) {
                    // ignore transaction attach errors
                }
                $this->orderRepository->save($order);
            } else {
                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                $order->addStatusHistoryComment('Switro payment failed: ' . ($response['transaction_error'] ?? ''));
                $this->orderRepository->save($order);
            }

            $this->getResponse()->setStatusCode(200);
            $this->getResponse()->setBody(json_encode(['success' => true]));
            return;
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(500);
            $this->getResponse()->setBody(json_encode(['error' => 'Failed to contact Switro']));
            return;
        }
    }

    /**
     * Disable CSRF validation for this controller (needed for webhook POST).
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true; // Always allow POST requests without form_key
    }
}
