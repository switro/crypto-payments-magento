<?php

namespace Switro\CryptoPayments\Model;

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    const PAYMENT_METHOD_CODE = 'switro_cryptopayments';
    protected $_code = self::PAYMENT_METHOD_CODE;
    protected $_isGateway = true;
    protected $_isOffline = true;
    protected $_countryFactory;
    protected $_minAmount = null;
    protected $_maxAmount = null;

    /** @var UrlInterface */
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );
        $this->_countryFactory = $countryFactory;
        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
        $this->urlBuilder = $urlBuilder;
    }



    public function isAvailable($quote = null)
    {
        return (bool)$this->_scopeConfig->getValue('payment/' . self::PAYMENT_METHOD_CODE . '/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getOrderPlaceRedirectUrl()
    {
        return $this->urlBuilder->getUrl('switro/payment/redirect', ['_secure' => true]);
    }
}
