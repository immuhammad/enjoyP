<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Sellerquote;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Webkul\Mpquotesystem\Model\ResourceModel\Quotes\CollectionFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;

/**
 * Webkul Mpquotesystem MassDelete controller.
 */
class MassDelete extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var CustomerUrl
     */
    protected $customerUrl;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Registry $coreRegistry
     * @param CollectionFactory $mpquoteCollectionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param HelperData $helper
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Customer\Model\Url $modelUrl
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $coreRegistry,
        CollectionFactory $mpquoteCollectionFactory,
        FormKeyValidator $formKeyValidator,
        HelperData $helper,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Customer\Model\Url $modelUrl,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        $this->collectionFactory = $mpquoteCollectionFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->helper = $helper;
        $this->filter = $filter;
        $this->modelUrl = $modelUrl;
        $this->messageManager = $messageManager;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        parent::__construct(
            $context
        );
    }

    /**
     * CreateCsrfValidationException
     *
     * @param RequestInterface $request
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * Validate For Csrf
     *
     * @param RequestInterface $request
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->modelUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Mass delete seller quote action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $isPartner = $this->helper->isSeller();
            $assignIds = [];
            if ($isPartner == 1) {
                try {
                    $collection = $this->filter->getCollection($this->collectionFactory->create());
                    $recordDeleted = 0;
                    foreach ($collection->getItems() as $quote) {
                        $quote->delete();
                        $recordDeleted++;
                    }
                    $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $recordDeleted));
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/managequote',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Something went wrong.'));

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/managequote',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/managequote',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
