<?php

namespace Switro\CryptoPayments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;


class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'switro_cryptopayments';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'redirectUrl' => $this->urlBuilder->getUrl('switro/payment/redirect')
                ]
            ]
        ];
    }
}
