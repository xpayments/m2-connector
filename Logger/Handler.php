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
 * @category   XPay
 * @package    XPay_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
namespace XPay\XPaymentsConnector\Logger;

/**
 * Logger handler
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * File name
     */
    protected $fileName = '/var/log/xpc.XPC-DATE.log';

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\DriverInterface $filesystem
     * @param string $filePath
     * @param string $fileName
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\Filesystem\DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    ) {

        // TODO: Timezone once a month
        $this->fileName = str_replace('XPC-DATE', date('Y-m'), $this->fileName);

        parent::__construct($filesystem, $filePath, $fileName);
    }
}
