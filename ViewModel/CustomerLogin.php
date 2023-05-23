<?php

namespace Codilar\NotifyStock\ViewModel;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;

class CustomerLogin implements ArgumentInterface
{
    protected $customerSession;
    protected $request;
    protected $getSalableQuantityDataBySku;

    public function __construct(
        Session $customerSession,
        RequestInterface $request,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
    }

    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getStockStatus($block)
    {
        $product = $block->getProduct();
        $productId = $product->getId();
        $sku = $product->getSku();
        $qty = $this->getSalableQuantityDataBySku->execute($sku);
        $salableQty = !empty($qty) && isset($qty[0]['qty']) ? $qty[0]['qty'] : 0;
        if ($salableQty > 10) {
            $stockStatus = "In Stock";
        } elseif ($salableQty > 0 && $salableQty <= 10) {
            $stockStatus = "Few Stock";
        } else {
            $stockStatus = "Out of Stock";
        }
        return $stockStatus;
    }
}
