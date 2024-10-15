<?php
/**
 * Webkul Affiliate Banner Save Controller
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action\Context;
use Webkul\Affiliate\Model\TextBannerFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var TextBannerFactory
     */
    private $textBanner;

    /**
     * @param Context           $context,
     * @param TextBannerFactory $textBanner
     */
    public function __construct(
        Context $context,
        TextBannerFactory $textBanner
    ) {

        parent::__construct($context);
        $this->textBanner = $textBanner;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('affiliate/banner/index');
            return;
        }
        try {
            foreach ($data as $key => $value) {
                    $result= $this->descriptionValidateFunction($value, $key, $data);
                if ($result['error']) {
                    $errors[] = __('Description has to be completed');
                } else {
                    $data[$key] = $result['data'][$key];
                }
            }
            $textBanner = $this->textBanner->create();
            $textBanner->setData($data);
            if (isset($data['entity_id'])) {
                $textBanner->setEntityId($data['entity_id']);
            }
            $textBanner->save();
            $this->messageManager->addSuccess(__('Text Banner has been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('affiliate/banner/index');
    }

    /**
     * Check permission.
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Affiliate::banner_manage_text_ads');
    }
    private function descriptionValidateFunction($value, $code, $data)
    {

        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $value = str_replace("<script>", "", $value);
            $value = str_replace("</script>", "", $value);
            $data[$code] = $value;
        }
       
        return ['error' => $error, 'data' => $data];
    }
}
