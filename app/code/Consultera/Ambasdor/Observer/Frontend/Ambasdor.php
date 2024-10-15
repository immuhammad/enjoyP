<?php
declare(strict_types=1);

namespace Consultera\Ambasdor\Observer\Frontend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

class Ambasdor implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
    }


    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $customer = $this->customerRepository->getById($observer->getCustomer()->getId());
        $params = $observer->getAccountController()->getRequest()->getParams();

        if ($customer && isset($params['customer_type']) && $params['customer_type']) {
            try {
                $customer->setGroupId($params['customer_type']);
                $this->customerRepository->save($customer);
            } catch (LocalizedException $exception) {
                $this->logger->error($exception);
            }
        }
    }
}

