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
 
namespace Cdev\XPaymentsConnector\Logger;

/**
 * Logger
 */
class Logger extends \Monolog\Logger
{
    // Empty class. See: https://magento.stackexchange.com/questions/75935/how-to-create-custom-log-file-in-magento-2/75954#75954
    // "Note: This is not strictly required but allows the DI to pass specific arguments to the constructor.
    // If you do not do this step, then you need to adjust the constructor to set the handler."
}