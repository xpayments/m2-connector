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
 * @category   CDev
 * @package    CDev_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace CDev\XPaymentsConnector\Block\Adminhtml\Settings\Field;

/**
 * Input configuretion field
 */
class Input extends \Magento\Backend\Block\Template
{
    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        array $data = array()
    ) {
        $this->_template = 'settings/field/input.phtml';

        parent::__construct($context, $data);

        $this->helper = $helper;
    }

    /**
     * Escape HTML entities
     *
     * @param string|array $data
     * @param array|null $allowedTags
     *
     * @return string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        if (empty($allowedTags) || !is_array($allowedTags)) {
            $allowedTags = array();
        }

        $allowedTags += array('br', 'strong');

        return parent::escapeHtml($data, $allowedTags);
    }

    /**
     * Set params for config field
     *
     * @param string $title
     * @param string $name
     * @param string $key Key from config (last from core_config_data)
     *
     * @return void
     */
    public function setFieldParams($title = null, $name = null, $key = null)
    {
        if ($title) {
            $this->setFieldTitle($title);
        }

        if ($name) {
            $this->setFieldName(sprintf('payment_method[%s]', $name));
        }

        if ($key) {
            $this->setFieldValue($this->helper->settings->getPaymentConfig($key));
        }
    }

    /**
     * Get params for zero-auth amount input
     *
     * @return void
     */
    public function setZeroAuthAmountParams()
    {
        $amount = $this->helper->settings->getXpcConfig('zero_auth_amount');

        if (null === $amount) {
            $amount = 1.00;
        }

        $amount = $this->helper->settings->preparePrice($amount);

        $comment = 'X-Payments will authorize this amount on a customer\'s credit card to save it in his customer profile.' . PHP_EOL
            . 'Recommended values: <strong>$0</strong> if your payment processor supports <strong>$0</strong> authorizations, '
            . 'or <strong>$0.1</strong> or <strong>$1.00</strong> if it doesn\'t.';

        $this->setFieldValue($amount);
        $this->setFieldName('zero_auth_amount');
        $this->setComment(nl2br($comment));
    }

    /**
     * Get params for zero-auth description input
     *
     * @return void
     */
    public function setZeroAuthDescriptionParams()
    {
        $description = $this->helper->settings->getXpcConfig('zero_auth_description');

        if (null === $description) {
            $description = 'Card Setup';
        }

        $comment = 'This text will appear as the name of card saving transaction on customer\'s credit card statement/balance.';

        $this->setFieldValue($description);
        $this->setFieldName('zero_auth_description');
        $this->setComment($comment);
    }
}
