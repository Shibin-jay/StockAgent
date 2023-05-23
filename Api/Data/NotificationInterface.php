<?php

namespace Codilar\NotifyStock\Api\Data;

interface NotificationInterface
{
    const NAME = 'name';
    const EMAIL = 'email';
    const PRODUCT_ID = 'product_id';
    const CUSTOMER_ID = 'customer_id';

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getEmail();

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);
    /**
     * Retrieve notification by id
     *
     * @param int $notificationId
     * @return $this|null
     */
    public function getById($notificationId);
        /**
     * Get product name by ID
     *
     * @param int $productId
     * @return string|null
     */
    public function getProductNameById($productId);
    /**
     * Retrieve the quantity of a product by its product ID
     *
     * @param int $productId
     * @return float|null
     */
    public function getProductQuantityById($productId);
}
