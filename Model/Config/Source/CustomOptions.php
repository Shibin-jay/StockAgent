<?php
namespace Codilar\NotifyStock\Model\Config\Source;

/**
 * Class CustomOptions
 *
 * This class provides custom options for a configuration field.
 */
class CustomOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Retrieve options array
     *
     * @return array
     */
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
