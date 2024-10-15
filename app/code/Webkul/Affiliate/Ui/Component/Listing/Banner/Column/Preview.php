<?php
/**
 * Webkul Affiliate Banner Preview Column.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Ui\Component\Listing\Banner\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Model\Template\FilterProvider;

class Preview extends Column
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;
    
    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterProvider $filterProvider,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->filterProvider = $filterProvider;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $size = explode('x', $item['banner_size']);
                if (count($size) == 1) {
                    $size = [0 => 'auto', 1 => 'auto'];
                }
                $item[$fieldName] = "<button class='button'><span>". __('Preview')."</span></button>";
                $item['text'] = $this->filterProvider->getPageFilter()
                                                    ->filter(str_replace(
                                                        ["<script>","</script>"],
                                                        ["&lt;script&gt;","&lt;/script&gt;"],
                                                        $item['text']
                                                    ));
                $item['text'] = "<div style='display: inline-block;padding: 10px; border: 1px solid #CCC; overflow:auto;
                 box-sizing:content-box; width:"
                    .$size[1]."px;height:".$size[0]."px;'><a href='"
                    .$item['link']."' style='text-decoration: none;'><strong>".$item['title']
                    ."</strong></a><br /><br /><a href='".$item['link']."' style='text-decoration: none;'>"
                    .$item['text']."</a></div>";
            }
        }
        return $dataSource;
    }
}
