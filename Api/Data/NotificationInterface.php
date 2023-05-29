<?php

namespace Codilar\NotifyStock\Api\Data;

interface NotificationInterface
{
    public const NAME = 'name';
    public const EMAIL = 'email';
    public const PRODUCT_ID = 'product_id';
    public const CUSTOMER_ID = 'customer_id';

    /**
     * Retrieve the name.
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set the name.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Retrieve the email.
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Set the email.
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Retrieve the product ID.
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set the product ID.
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Retrieve the customer ID.
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set the customer ID.
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Retrieve notification by ID.
     *
     * @param int $notificationId
     * @return NotificationInterface|null
     */
    public function getById($notificationId);

    /**
     * Get the product name by ID.
     *
     * @param int $productId
     * @return string|null
     */
    public function getProductNameById($productId);

    /**
     * Retrieve the quantity of a product by its product ID.
     *
     * @param int $productId
     * @return float|null
     */
    public function getProductQuantityById($productId);
}
