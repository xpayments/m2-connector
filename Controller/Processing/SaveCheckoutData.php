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

namespace CDev\XPaymentsConnector\Controller\Processing;

/**
 * Save checkout data
 */
class SaveCheckoutData extends \Magento\Framework\App\Action\Action
{
    /**
     * Data hash
     */
    protected $data = array();

    /**
     * Billing data hash
     */
    protected $billingData = array();

    /**
     * Quote model
     */
    protected $quote = null;

    /**
     * Result factory
     */
    protected $resultFactory = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Checkout session
     */
    protected $checkoutSession = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \CDev\XPaymentsConnector\Helper\Data $helper
    ) {
        $this->resultFactory = $resultFactory;
        $this->checkoutSession = $checkoutSession;

        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * Parse POST-ed data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return $this
     */
    protected function prepareData()
    {
        $this->data = $this->getRequest()->getPostValue();

        if (!empty($this->data['data'])) {
            $this->data = json_decode($this->data['data'], true);
        }

        if (json_last_error()) {
            throw new \Magento\Framework\Exception\LocalizedException('Error in parsing checkout data');
        }

        if (!empty($this->data['billingAddress'])) {
            $this->billingData = $this->data['billingAddress'];
            unset($this->data['billingAddress']);
        }

        $this->quote = $this->checkoutSession->getQuote();

        return $this;
    }

    /**
     * Process regular data
     *
     * @return $this
     */
    protected function processData()
    {
        if (!empty($this->data['email'])) {
            $this->quote->getBillingAddress()->setEmail($this->data['email']);
            $this->quote->getShippingAddress()->setEmail($this->data['email']);
        }

        return $this;
    }

    /**
     * Process billing data
     *
     * @return $this
     */
    protected function processBillingData()
    {
        if (!empty($this->billingData['country_id'])) {
            $this->quote->getBillingAddress()->setCountryId($this->billingData['country_id']);
        }

        if (!empty($this->billingData['postcode'])) {
            $this->quote->getBillingAddress()->setPostcode($this->billingData['postcode']);
        }

        if (!empty($this->billingData['region_id'])) {
            $this->quote->getBillingAddress()->setRegionId($this->billingData['region_id']);
        }

        if (!empty($this->billingData['city'])) {
            $this->quote->getBillingAddress()->setCity($this->billingData['city']);
        }

        if (!empty($this->billingData['street'])) {
            $street = is_array($this->billingData['street'])
                ? implode(PHP_EOL, array_filter($this->billingData['street']))
                : $this->billingData['street'];

            $this->quote->getBillingAddress()->setStreet($street);
        }

        if (!empty($this->billingData['telephone'])) {
            $this->quote->getBillingAddress()->setTelephone($this->billingData['telephone']);
        }

        if (!empty($this->billingData['firstname'])) {
            $this->quote->getBillingAddress()->setFirstName($this->billingData['firstname']);
        }

        if (!empty($this->billingData['lastname'])) {
            $this->quote->getBillingAddress()->setLastName($this->billingData['lastname']);
        }

        return $this;
    }

    /**
     * Execute action
     *
     * @return void
     */
    public function execute()
    {
        $result = array();

        try {

            $this->prepareData()
                ->processData()
                ->processBillingData();

            $this->quote->save();

        } catch (\Exception $exception) {

            $result['error'] = $exception->getMessage();
        }

        return $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData($result);
    }
}
