<?php
// vim: set ts=4 sw=4 sts=4 et:
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/license/OSL-3.0
 *
 * @author     X-Cart Holdings LLC <info@x-cart.com>
 * @category   CDev
 * @package    CDev_XPaymentsConnector
 * @copyright  (c) 2010-present X-Cart Holdings LLC <info@x-cart.com>. All rights reserved
 * @license    https://opensource.org/license/OSL-3.0  Open Software License (OSL 3.0)
 */

namespace CDev\XPaymentsConnector\Transport;

/**
 * Response transport
 */
class ApiResponse extends \Magento\Framework\DataObject
{
    /**
     * Get status
     *
     * @return bool
     */
    public function getStatus()
    {
        return (bool)parent::getStatus()
            && !$this->getErrorCode()
            && !$this->getErrorMessage();
    }

    /**
     * Get field value from the response array
     *
     * @return string
     */
    public function getField($name)
    {
        $response = $this->getResponse();

        return !empty($response[$name]) ? $response[$name] : '';
    }

    /**
     * Get error message
     *
     * @param string $defaultMessage Default message
     *
     * @return string
     */
    public function getErrorMessage($defaultMessage = '')
    {
        $message = parent::getErrorMessage();

        if (empty($message)) {
            $message = parent::getMessage();
        }

        if (empty($message)) {
            $message = $defaultMessage;
        }

        return $message;
    }

    /**
     * Get error message as phrase
     *
     * @param string $defaultMessage Default message
     *
     * @return \Magento\Framework\Phrase
     */
    public function getErrorPhrase($defaultMessage = '')
    {
        return __($this->getErrorMessage($defaultMessage));
    }

    /**
     * Get message
     *
     * @param string $defaultMessage Default message
     *
     * @return string
     */
    public function getMessage($defaultMessage = '')
    {
        $message = parent::getMessage();

        if (empty($message)) {
            $message = $defaultMessage;
        }

        return $message;
    }

    /**
     * Get message as phrase
     *
     * @param string $defaultMessage Default message
     *
     * @return \Magento\Framework\Phrase
     */
    public function getPhrase($defaultMessage = '')
    {
        return __($this->getMessage($defaultMessage));
    }
}
