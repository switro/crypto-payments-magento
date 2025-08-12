<?php
namespace Switro\CryptoPayments\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH = 'payment/switro_cryptopayments/';

    public function getConfig($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH . $field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getWebhookUrl()
    {
        return $this->_urlBuilder->getUrl('switro/webhook/index', ['_secure' => true]);
    }
}
