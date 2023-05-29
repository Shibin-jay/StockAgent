<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codilar\NotifyStock\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Checks product salability
 */
class ProductSalability
{
    /**
     * To check product salability
     *
     * @param ProductInterface $product
     * @param WebsiteInterface $website
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isSalable(ProductInterface $product, WebsiteInterface $website): bool
    {
        return $product->isSalable();
    }
}
