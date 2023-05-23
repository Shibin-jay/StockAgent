<?php
namespace Codilar\NotifyStock\Model\Config\Source;

class CustomOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [
            ['value' => '* * * * *', 'label' => __('Every Minute')],
            ['value' => '*/5 * * * *', 'label' => __('Every 5 Minutes')],
            ['value' => '0 * * * *', 'label' => __('Every Hour')],
        ];

        return $options;
    }
}
