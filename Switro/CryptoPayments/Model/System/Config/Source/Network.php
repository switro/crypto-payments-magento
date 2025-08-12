<?php
namespace Switro\CryptoPayments\Model\System\Config\Source;

class Network implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'mainnet', 'label' => __('Mainnet')],
            ['value' => 'devnet', 'label' => __('Devnet')],
        ];
    }
}
