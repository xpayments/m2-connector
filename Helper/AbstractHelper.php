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
 
namespace Cdev\XPaymentsConnector\Helper;

/**
 * Abstract helper class
 */
abstract class AbstractHelper extends \Magento\Framework\DataObject
{
    /**
     * Helper
     */
    protected $helper = null;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        // Just a wrapper. Most possibly will used later.

        parent::__construct();
    }

    /**
     * Set helper with helpers
     *
     * @param Cdev\XPaymentsConnector\Helper\Data
     *
     * @return Cdev\XPaymentsConnector\Helper\AbstractHelper
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;

        return $this;
    }

    /**
     * Format price in 1234.56 format
     *
     * @param mixed $price
     *
     * @return string
     */
    public function preparePrice($price)
    {
        return $this->helper->preparePrice($price);
    }
}