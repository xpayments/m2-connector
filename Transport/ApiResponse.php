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
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @author     Qualiteam Software <info@x-cart.com>
 * @category   Cdev
 * @package    Cdev_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cdev\XPaymentsConnector\Transport;

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
}
