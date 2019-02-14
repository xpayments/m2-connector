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

namespace CDev\XPaymentsConnector\Block\Adminhtml\PaymentCard;

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Add New Payment Card page
 */
class Add extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * Customer repository
     */
    protected $customerRepository = null;

    /**
     * Address repository
     */
    protected $addressRepository = null;

    /**
     * Address config
     */
    protected $addressConfig = null;

    /**
     * Address mapper
     */
    protected $addressMapper = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        array $data = array()
    ) {

        $this->addressRepository = $addressRepository;
        $this->addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->customerRepository = $customerRepository;

        $this->coreRegistry = $registry;

        $this->_template = 'paymentcard/add.phtml';

        parent::__construct($context, $data);
    }

    /**
     * Add elements in layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $params = array(
            'user_id' => $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID),
        );

        $url = $this->getUrl('xpc/payment_card/index', $params);

        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Back'),
                'title' => __('Back'),
                'onclick' => 'setLocation("' . $url . '")',
                'class' => 'action-default back'
            )
        );

        $this->getToolbar()->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Save Payment Card'),
                'title' => __('Save Payment Card'),
                'onclick' => 'submitPaymentForm()',
                'class' => 'action-default primary'
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Get Billing Address HTML representation
     *
     * @return string
     */
    public function getAddressHtml()
    {
        $customer = $this->customerRepository->getById(
            $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );

        $address = $this->addressRepository->getById(
            $customer->getDefaultBilling()
        );

        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();

        return $renderer->renderArray($this->addressMapper->toFlatArray($address));
    }

    /**
     * Get iframe URL
     *
     * @return string
     */
    public function getIframeUrl()
    {
        $params = array(
            'token'    => $this->coreRegistry->registry(RegistryConstants::ZERO_AUTH_TOKEN),
            'store_id' => $this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID),
        );

        return $this->getUrl('xpc/payment_card/iframe', $params);
    }
}
