<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Controller\Booking;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;

class EventChartUpload extends Action
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * File Uploader.
     *
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * Store manager interface
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $file;

    /**
     * Constructor
     *
     * @param Context                               $context
     * @param Filesystem                            $filesystem
     * @param UploaderFactory                       $fileUploaderFactory
     * @param StoreManagerInterface                 $storeManager
     * @param Session                               $customerSession
     * @param \Magento\Framework\Filesystem\Io\File $file
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        parent::__construct($context);
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->file = $file;
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $result = [];
        if ($this->getRequest()->isPost()) {
            try {
                $fields = $this->getRequest()->getParams();
                $files = $this->getRequest()->getFiles();
                $dirPath = $this->_mediaDirectory->getAbsolutePath(
                    'mpadvancedbookingsystem'
                );
                $imageDirPath = $this->_mediaDirectory->getAbsolutePath(
                    'mpadvancedbookingsystem/eventChartImage'
                );

                $this->file->checkAndCreateFolder($dirPath);
                $this->file->checkAndCreateFolder($imageDirPath);

                $baseTmpPath = 'mpadvancedbookingsystem/eventChartImage/';
                $target = $this->_mediaDirectory->getAbsolutePath($baseTmpPath);
                try {
                    /**
                     * @var $uploader \Magento\MediaStorage\Model\File\Uploader
                     */
                    $uploader = $this->_fileUploaderFactory->create(
                        ['fileId' => $files['product']['event_chart_image']]
                    );
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $result = $uploader->save($target);
                    if (!$result) {
                        $result = [
                            'error' => __('File can not be saved to the destination folder.'),
                            'errorcode' => ''
                        ];
                    }

                    if (isset($result['file'])) {
                        try {
                            $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
                            $result['path'] = str_replace('\\', '/', $result['path']);
                            $result['url'] = $this->storeManager
                                ->getStore()
                                ->getBaseUrl(
                                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                                ) . $this->getFilePath($baseTmpPath, $result['file']);
                            $result['name'] = $result['file'];
                        } catch (\Exception $e) {
                            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
                        }
                    }
                } catch (\Exception $e) {
                    $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
                }
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            }
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * Retrieve path
     *
     * @param string $path
     * @param string $imageName
     *
     * @return string
     */
    public function getFilePath($path, $imageName)
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }
}
