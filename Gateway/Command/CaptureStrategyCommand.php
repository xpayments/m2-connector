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

namespace XPay\XPaymentsConnector\Gateway\Command;

/**
 * Capture strategy command (sale == auth&capture || settlement == capture)
 */
class CaptureStrategyCommand implements \Magento\Payment\Gateway\CommandInterface
{
    /**
     * Authorize and capture command
     */
    const SALE = 'sale';

    /**
     * Capture command
     */
    const CAPTURE = 'settlement';

    /**
     * CommandPool interface
     */
    private $commandPool;

    /**
     * Transaction repository interface
     */
    private $transactionRepository;

    /**
     * Filter builder
     */
    private $filterBuilder;

    /**
     * Search criteria builder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $repository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
     *
     * @return void
     */
    public function __construct(
        \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool,
        \Magento\Sales\Api\TransactionRepositoryInterface $repository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Execute command
     *
     * @param array $commandSubject
     *
     * @return void
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        /** @var \Magento\Sales\Api\Data\OrderPaymentInterface $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        \Magento\Payment\Gateway\Helper\ContextHelper::assertOrderPayment($paymentInfo); // TODO: What is this magic for?

        $command = $this->getCommand($paymentInfo);
        $this->commandPool->get($command)->execute($commandSubject);
    }

    /**
     * Get command name
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     *
     * @return string
     */
    private function getCommand(\Magento\Sales\Api\Data\OrderPaymentInterface $payment)
    {
        // if auth transaction is not exists execute authorize&capture command
        $existsCapture = $this->isExistsCaptureTransaction($payment);

        if (!$payment->getAuthorizationTransaction() && !$existsCapture) {
            $command = self::SALE;
        } else {
            $command = self::CAPTURE;
        }

        return $command;
    }

    /**
     * Check if capture transaction already exists
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     *
     * @return bool
     */
    private function isExistsCaptureTransaction(\Magento\Sales\Api\Data\OrderPaymentInterface $payment)
    {
        $this->searchCriteriaBuilder->addFilters(
            array(
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create(),
            )
        );

        $this->searchCriteriaBuilder->addFilters(
            array(
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(\Magento\Sales\Api\Data\TransactionInterface::TYPE_CAPTURE)
                    ->create(),
            )
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();

        return (bool)$count;
    }
}
