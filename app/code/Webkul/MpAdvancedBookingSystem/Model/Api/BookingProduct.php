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
namespace Webkul\MpAdvancedBookingSystem\Model\Api;

use Webkul\MpAdvancedBookingSystem\Api\BookingProductInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class BookingProduct implements BookingProductInterface
{
    /**
     * @var \Webkul\Marketplace\Model\SellerFactory
     */
    protected $sellerFactory;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\InfoFactory
     */
    protected $_info;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\SlotFactory
     */
    protected $_slot;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_bookingHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepo;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface
     */
    protected $responseInterface;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Api
     */
    protected $helperApi;

    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $catalogProductOptionFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Catalog\Api\AttributeSetRepositoryInterface
     */
    protected $attributeSetRepoInterface;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    protected $productInterfaceFactory;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductFactory;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    protected $userContext;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @param \Webkul\MpAdvancedBookingSystem\Model\InfoFactory $info
     * @param \Webkul\MpAdvancedBookingSystem\Model\SlotFactory $slot
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepo
     * @param \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface $responseInterface
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Api $helperApi
     * @param Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\OptionFactory $catalogProductOptionFactory
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Api\AttributeSetRepositoryInterface $attributeSetRepoInterface
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Magento\Authorization\Model\CompositeUserContext $userContext
     * @param \Webkul\Marketplace\Model\SellerFactory $sellerFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     */
    public function __construct(
        \Webkul\MpAdvancedBookingSystem\Model\InfoFactory $info,
        \Webkul\MpAdvancedBookingSystem\Model\SlotFactory $slot,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepo,
        \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface $responseInterface,
        \Webkul\MpAdvancedBookingSystem\Helper\Api $helperApi,
        Filesystem $filesystem,
        \Magento\Catalog\Model\Product\OptionFactory $catalogProductOptionFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Api\AttributeSetRepositoryInterface $attributeSetRepoInterface,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Authorization\Model\CompositeUserContext $userContext,
        \Webkul\Marketplace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        $this->_info = $info;
        $this->_slot = $slot;
        $this->_bookingHelper = $bookingHelper;
        $this->_product = $productFactory;
        $this->_productRepo = $productRepo;
        $this->responseInterface = $responseInterface;
        $this->helperApi = $helperApi;
        $this->catalogProductOptionFactory = $catalogProductOptionFactory;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
        $this->emulate = $emulate;
        $this->stockRegistry  = $stockRegistry;
        $this->attributeSetRepoInterface  = $attributeSetRepoInterface;
        $this->productInterfaceFactory  = $productInterfaceFactory;
        $this->mpProductFactory  = $mpProductFactory;
        $this->userContext  = $userContext;
        $this->sellerFactory  = $sellerFactory;
        $this->date  = $date;
        $this->urlDecoder  = $urlDecoder;
    }

    /**
     * Save Booking Product
     *
     * @api
     * @param \Magento\Catalog\Api\Data\ProductInterface $data
     * @param mixed $bookingData
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface
     */
    public function saveBookingProduct($data, $bookingData)
    {
        try {
            $response = $this->responseInterface;
            if ($data->getTypeId() != 'booking') {
                $response->setSuccess(false);
                $response->setMessage(__('This is not a booking product'));
                $response->setResponseData('');
                return $response;
            }
            if (isset($bookingData['booking_type']) && $booking_type = $bookingData['booking_type']) {
                $attributeSetId = $data->getAttributeSetId();
                $attributeSetName = $this->attributeSetRepoInterface->get($attributeSetId)->getAttributeSetName();
                $helper = $this->_bookingHelper;
                switch ($booking_type) {
                    case "rental":
                        if ($attributeSetName != 'Rental Booking') {
                            $response->setSuccess(false);
                            $response->setMessage(__('Wrong Attribute Set Id'));
                            $response->setResponseData('');
                            return $response;
                        }
                        $result = $this->rentalAttributeValidation($data, $bookingData);
                        if ($result === true) {
                            return $this->saveRentalBooking($data, $bookingData);
                        }
                        return $result;
                    case "event":
                        if ($attributeSetName != 'Event Booking') {
                            $response->setSuccess(false);
                            $response->setMessage(__('Wrong Attribute Set Id'));
                            $response->setResponseData('');
                            return $response;
                        }
                        $result = $this->eventAttributeValidation($data, $bookingData);
                        if ($result === true) {
                            return $this->saveEventBooking($data, $bookingData);
                        }
                        return $result;
                    case "appointment":
                        if ($attributeSetName != 'Appointment Booking') {
                            $response->setSuccess(false);
                            $response->setMessage(__('Wrong Attribute Set Id'));
                            $response->setResponseData('');
                            return $response;
                        }
                        $result = $this->appointmentAttributeValidation($data, $bookingData);
                        if ($result === true) {
                            return $this->saveAppointmentBooking($data, $bookingData);
                        }
                        return $result;
                    case "table":
                        if ($attributeSetName != 'Table Booking') {
                            $response->setSuccess(false);
                            $response->setMessage(__('Wrong Attribute Set Id'));
                            $response->setResponseData('');
                            return $response;
                        }
                        $result = $this->tableAttributeValidation($data, $bookingData);
                        if ($result === true) {
                            return $this->savetableBooking($data, $bookingData);
                        }
                        return $result;
                    case "default":
                        if ($attributeSetName != 'Default') {
                            $response->setSuccess(false);
                            $response->setMessage(__('Wrong Attribute Set Id'));
                            $response->setResponseData('');
                            return $response;
                        }
                        $result = $this->defaultBookingAttributeValidation($data, $bookingData);
                        if ($result === true) {
                            return $this->saveDefaultBooking($data, $bookingData);
                        }
                        return $result;
                    default:
                }
            }
            $response->setSuccess(false);
            $response->setMessage(__('Something Went Wrong !!'));
            $response->setResponseData('');
            return $response;
        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->setMessage(__($e->getMessage()));
            $response->setResponseData('');
            return $response;
        }
    }

    /**
     * EventAttributeValidation
     *
     * @api
     * @param \Magento\Catalog\Api\Data\ProductInterface $data
     * @param mixed $bookingData
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface
     */
    public function eventAttributeValidation($data, $bookingData)
    {
        $response = $this->responseInterface;
        if (!isset($bookingData['options'])) {
            $response->setSuccess(false);
            $response->setMessage(__('Event Option is required.'));
            $response->setResponseData('');
            return $response;
        }
        if (empty($data->getCustomAttributes())) {
            $response->setSuccess(false);
            $response->setMessage(__('Please add required Attributes'));
            $response->setResponseData('');
            return $response;
        }
        $flag = false;
        foreach ($data->getCustomAttributes() as $value) {
            if ($value->getAttributeCode() == 'event_chart_available'
                && $value->getValue()
            ) {
                $flag = true;
            }
            $customAttributes[] = $value->getAttributeCode();
        }
        $requiredEventAtt = $this->helperApi->getEventBookingRequiredAttributes($flag);
        $arrIntersect = array_intersect($customAttributes, $requiredEventAtt);
        $diffArr = array_diff($requiredEventAtt, $arrIntersect);

        if (!empty($diffArr)) {
            $requiredAttributeString = implode(", ", $diffArr);
            $response->setSuccess(false);
            $response->setResponseData('');
            if (count($diffArr) > 0) {
                $response->setMessage($requiredAttributeString.' are required attributes');
            } else {
                $response->setMessage($requiredAttributeString.' is required attribute');
            }
            return $response;
        }
        return true;
    }

    /**
     * RentalAttributeValidation
     *
     * @api
     * @param \Magento\Catalog\Api\Data\ProductInterface $data
     * @param mixed $bookingData
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface
     */
    public function rentalAttributeValidation($data, $bookingData)
    {

        $hasHourlyPrice = false;
        $hasDailyPrice = false;
        foreach ($data->getOptions() as $optionData) {
            foreach ($optionData->getValues() as $optionValue) {
                if ($optionValue->getTitle()=='Hourly Basis') {
                    $hasHourlyPrice = true;
                }
                if ($optionValue->getTitle()=='Daily Basis') {
                    $hasDailyPrice = true;
                }
            }
        }

        $response = $this->responseInterface;
        if (!isset($bookingData['slot_data'])) {
            $response->setSuccess(false);
            $response->setMessage(__('Slot Data is required.'));
            $response->setResponseData('');
            return $response;
        }
        if (empty($data->getCustomAttributes())) {
            $response->setSuccess(false);
            $response->setMessage(__('Please add required Attributes'));
            $response->setResponseData('');
            return $response;
        }
        $availableEveryWeek = false;
        $rentingType = 3;
        $slotHasQuantity = false;
        foreach ($data->getCustomAttributes() as $value) {
            if ($value->getAttributeCode() == 'renting_type') {
                $rentingType = $value->getValue();
            }
            if ($value->getAttributeCode() == 'available_every_week'
                && $value->getValue() == 1
            ) {
                $availableEveryWeek = true;
            }
            if ($value->getAttributeCode() == 'slot_has_quantity' && $value->getValue() == 1) {
                $slotHasQuantity = true;
            }
            $customAttributes[] = $value->getAttributeCode();
        }

        //check slot data quantity
        if ($slotHasQuantity) {
            $isSucess = true;
            foreach ($bookingData['slot_data'] as $slotData) {
                foreach ($slotData as $slotDataArr) {
                    if (!isset($slotDataArr['qty']) || !$slotDataArr['qty']) {
                        $isSucess = false;
                        break;
                    }
                }
            }
            if (!$isSucess) {
                $response->setSuccess(false);
                $response->setMessage(__("Slot Data's qty field is required"));
                $response->setResponseData('');
                return $response;
            }
        }

        //check of hourly and daily price
        if ($rentingType == 1 && !$hasDailyPrice) {
            //Daily Basis
            $response->setSuccess(false);
            $response->setMessage(__('Daily Price is required.'));
            $response->setResponseData('');
            return $response;
        } elseif ($rentingType == 2 && (!$hasDailyPrice || !$hasHourlyPrice)) {
            //Both(Hourly + Daily Basis)
            $response->setSuccess(false);
            if (!$hasHourlyPrice) {
                $response->setMessage(__('Hourly Price is required.'));
            } else {
                $response->setMessage(__('Daily Price is required.'));
            }
            $response->setResponseData('');
            return $response;
        } elseif ($rentingType == 3 && !$hasHourlyPrice) {
            //Hourly Basis
            $response->setSuccess(false);
            $response->setMessage(__('Hourly Price is required.'));
            $response->setResponseData('');
            return $response;
        }

        $requiredRentalAtt = $this->helperApi->getRentalBookingRequiredAttributes($rentingType);
        if (!$availableEveryWeek) {
            $requiredRentalAtt[] = 'booking_available_from';
            $requiredRentalAtt[] = 'booking_available_to';
        }
        $arrIntersect = array_intersect($customAttributes, $requiredRentalAtt);
        $diffArr = array_diff($requiredRentalAtt, $arrIntersect);

        if (!isset($bookingData['available_qty'])) {
            $diffArr[] = 'available_qty';
        }
        if (!empty($diffArr)) {
            $requiredAttributeString = implode(", ", $diffArr);
            $response->setSuccess(false);
            $response->setResponseData('');
            if (count($diffArr) > 0) {
                $response->setMessage($requiredAttributeString.' are required attributes');
            } else {
                $response->setMessage($requiredAttributeString.' is required attribute');
            }
            return $response;
        }
        return true;
    }

    /**
     * TableAttributeValidation
     *
     * @api
     * @param \Magento\Catalog\Api\Data\ProductInterface $data
     * @param mixed $bookingData
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface
     */
    public function tableAttributeValidation($data, $bookingData)
    {
        $response = $this->responseInterface;
        if (!isset($bookingData['slot_data'])) {
            $response->setSuccess(false);
            $response->setMessage(__('Slot Data is required.'));
            $response->setResponseData('');
            return $response;
        }
        if (empty($data->getCustomAttributes())) {
            $response->setSuccess(false);
            $response->setMessage(__('Please add required Attributes'));
            $response->setResponseData('');
            return $response;
        }
        foreach ($data->getCustomAttributes() as $value) {
            $flag = false;
            if ($value->getAttributeCode() == 'price_charged_per_table'
                && $value->getValue() == 2
            ) {
                $flag = true;
            }
            $customAttributes[] = $value->getAttributeCode();
        }
        $requiredAppointmentAtt = $this->helperApi->getRequiredAttributesForTableBooking($flag);
        $arrIntersect = array_intersect($customAttributes, $requiredAppointmentAtt);
        $diffArr = array_diff($requiredAppointmentAtt, $arrIntersect);
        if (!empty($diffArr)) {
            $requiredAttributeString = implode(", ", $diffArr);
            $response->setSuccess(false);
            $response->setResponseData('');
            if (count($diffArr) > 0) {
                $response->setMessage($requiredAttributeString.' are required attributes');
            } else {
                $response->setMessage($requiredAttributeString.' is required attribute');
            }
            return $response;
        }
        return true;
    }

    /**
     * AppointmentAttributeValidation
     *
     * @api
     * @param \Magento\Catalog\Api\Data\ProductInterface $data
     * @param mixed $bookingData
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface
     */
    public function appointmentAttributeValidation($data, $bookingData)
    {
        $response = $this->responseInterface;
        if (!isset($bookingData['slot_data'])) {
            $response->setSuccess(false);
            $response->setMessage(__('Slot Data is required.'));
            $response->setResponseData('');
            return $response;
        }
        if (empty($data->getCustomAttributes())) {
            $response->setSuccess(false);
            $response->setMessage(__('Please add required Attributes'));
            $response->setResponseData('');
            return $response;
        }
        $customAttributes = [];
        $flag = false;
        $slotHasQuantity = false;
        foreach ($data->getCustomAttributes() as $value) {
            if ($value->getAttributeCode() == 'available_every_week'
                && !$value->getValue()
            ) {
                $flag = true;
            }
            if ($value->getAttributeCode() == 'slot_has_quantity' && $value->getValue() == 1) {
                $slotHasQuantity = true;
            }
            $customAttributes[] = $value->getAttributeCode();
        }

        //check slot data quantity
        if ($slotHasQuantity) {
            $isSucess = true;
            foreach ($bookingData['slot_data'] as $slotData) {
                foreach ($slotData as $slotDataArr) {
                    if (!isset($slotDataArr['qty']) || !$slotDataArr['qty']) {
                        $isSucess = false;
                        break;
                    }
                }
            }
            if (!$isSucess) {
                $response->setSuccess(false);
                $response->setMessage(__("Slot Data's qty field is required"));
                $response->setResponseData('');
                return $response;
            }
        }
        $requiredAppointmentAtt = $this->helperApi->getRequiredAttributesForAppointmentBooking($flag);

        $arrIntersect = array_intersect($customAttributes, $requiredAppointmentAtt);
        $diffArr = array_diff($requiredAppointmentAtt, $arrIntersect);
        if (!empty($diffArr)) {
            $requiredAttributeString = implode(", ", $diffArr);
            $response->setSuccess(false);
            $response->setResponseData('');
            if (count($diffArr) > 0) {
                $response->setMessage($requiredAttributeString.' are required attributes');
            } else {
                $response->setMessage($requiredAttributeString.' is required attribute');
            }
            return $response;
        }
        return true;
    }

    /**
     * DefaultBookingAttributeValidation
     *
     * @api
     * @param \Magento\Catalog\Api\Data\ProductInterface $data
     * @param mixed $bookingData
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface
     */
    public function defaultBookingAttributeValidation($data, $bookingData)
    {
        $response = $this->responseInterface;
        if (!isset($bookingData['default_booking_type'])) {
            $bookingData['default_booking_type'] = 0;
        }

        $defaultBookingType = $bookingData['default_booking_type'];
        if ($defaultBookingType > 0 && !isset($bookingData['info'])) {
            $response->setSuccess(false);
            $response->setMessage(__('Please add required Info Parameters'));
            $response->setResponseData('');
            return $response;
        }

        if ($defaultBookingType == 0) {
            return true;
        } elseif ($defaultBookingType == 1 || $defaultBookingType == 2) {
            $requiredDefaultParams = $this->helperApi->getRequiredParamsForDefaultBooking($defaultBookingType);
            $passedParams = $bookingData;
            unset($passedParams['info']);
            foreach ($passedParams as $key => $param) {
                $passedParamsArr[] = $key;
            }
        }
        $arrIntersect = array_intersect($passedParamsArr, $requiredDefaultParams);
        $diffArr = array_diff($requiredDefaultParams, $arrIntersect);

        if (!empty($diffArr)) {
            $requiredAttributeString = implode(", ", $diffArr);
            $response->setSuccess(false);
            $response->setResponseData('');
            if (count($diffArr) > 0) {
                $response->setMessage($requiredAttributeString.' are required Parameters');
            } else {
                $response->setMessage($requiredAttributeString.' is required Parameters');
            }
            return $response;
        }
        return true;
    }

    /**
     * Save Rental Booking Product.
     *
     * @param object $data
     * @param array $bookingData
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface
     */
    public function saveRentalBooking($data, $bookingData)
    {
        try {
            $sellerId = $this->userContext->getUserId();
            $storeId = 0;
            if (isset($bookingData['store_id'])) {
                $storeId = $bookingData['store_id'];
            }
            $environment = $this->emulate->startEnvironmentEmulation($storeId);
            /** response object */
            $response = $this->responseInterface;

            /** saving default product */
            $productSaveReturn = $this->_productRepo->save(
                $data
            );
            $productId = $productSaveReturn->getId();
            $productSetId = $productSaveReturn->getAttributeSetId();
            $availableQtyParam = $bookingData['available_qty'];
            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();
            $rentType = $productSaveReturn->getRentingType();
            $qty = 0;

            $stockData = $this->stockRegistry->getStockItem($productId);
            if (isset($stockData->getData()['qty'])) {
                $qty = $stockData->getData()['qty'];
            } elseif (isset($productSaveReturn->getQuantityAndStockStatus()['qty'])) {
                $qty = $productSaveReturn->getQuantityAndStockStatus()['qty'];
            }

            $availableQty = $qty;
            if (!empty($availableQtyParam) && $availableQtyParam) {
                $availableQty = $availableQtyParam;
            }
            if ($rentType != 1) {
                $dataProduct = $bookingData;
                $dataProduct['product'] = $this->getCalculatedRentSlotData($productSaveReturn, $bookingData);
                $totalSlots = $dataProduct['product']['total_slots'];
                $totalSlotTime = 60; //60 mins

                foreach ($dataProduct['slot_data'] as $dayKey => $dayValue) {
                    unset($dataProduct['slot_data'][$dayKey]);
                    $dataProduct['slot_data'][$dayKey+1] = $dayValue;
                }

                foreach ($dataProduct['slot_data'] as $dayKey => $dayValue) {
                    $slotIndex = 1;
                    $currentSlots = 0;
                    if (empty($dayValue)) {
                        continue;
                    }
                    foreach ($dayValue as $key => $value) {
                        if (empty($value['qty'])) {
                            $value['qty'] = $availableQty;
                        }
                        if (!$productSaveReturn->getSlotHasQuantity()) {
                            $value['qty'] = $availableQty;
                        }
                        $availableSlotArr = [];
                        $slotFromTimeStamp = strtotime($value['from']);
                        $slotToTimeStamp = strtotime($value['to']);
                        $totalTimeStamp = ($slotToTimeStamp - $slotFromTimeStamp)/60; // in hours
                        $currentSlots = $currentSlots + (int)($totalTimeStamp/$totalSlotTime);
                        $availableSlotArr[$slotIndex-1]['qty'] = $value['qty'];
                        $availableSlotArr[$slotIndex-1]['time'] = $value['from'];
                        for ($i=$slotIndex; $i <= $currentSlots; $i++) {
                            $availableSlotArr[$i]['qty'] = $value['qty'];
                            $availableSlotArr[$i]['time'] = date(
                                "h:i a",
                                strtotime(
                                    '+'.$totalSlotTime.' minutes',
                                    strtotime($availableSlotArr[$i-1]['time'])
                                )
                            );
                        }
                        $slotIndex = $currentSlots;
                        $slotIndex++;
                        $dataProduct['slot_data'][$dayKey][$key]['qty'] = $value['qty'];
                        $dataProduct['slot_data'][$dayKey][$key]['slots_info'] = $availableSlotArr;
                    }
                }
                if ($productSaveReturn->getSlotForAllDays()) {
                    $dataProduct['slot_data'][2] = $dataProduct['slot_data'][1];
                    $dataProduct['slot_data'][3] = $dataProduct['slot_data'][1];
                    $dataProduct['slot_data'][4] = $dataProduct['slot_data'][1];
                    $dataProduct['slot_data'][5] = $dataProduct['slot_data'][1];
                    $dataProduct['slot_data'][6] = $dataProduct['slot_data'][1];
                    $dataProduct['slot_data'][7] = $dataProduct['slot_data'][1];
                }
                $bookingSlotData = $helper->getJsonEcodedString($dataProduct['slot_data']);
            } else {
                if ($productSaveReturn->getAvailableEveryWeek()) {
                    $totalSlots = 99999999;
                } else {
                    $startDate = $productSaveReturn->getBookingAvailableFrom();
                    $endDate = $productSaveReturn->getBookingAvailableTo();
                    $numOfDays = $this->getDateDifference($startDate, $endDate);
                    $totalSlots = $availableQty * $numOfDays;
                }
                $bookingSlotData = $helper->getJsonEcodedString([]);
            }
            $slotProductId = 0;
            $slotCollection = $slotModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($slotCollection as $value) {
                $slotProductId = $value->getId();
            }

            $bookingProductId = 0;
            $infoModelOld = null;
            $bookingCollection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($bookingCollection as $value) {
                $bookingProductId = $value->getId();
                $infoModelOld = $value;
            }
            if ($this->canSaveRentalSlots(
                $infoModelOld,
                $bookingSlotData,
                $rentType,
                $productSaveReturn->getBookingAvailableFrom(),
                $productSaveReturn->getBookingAvailableTo(),
                $availableQty
            )) {
                $infoData = [
                    'attribute_set_id' => $productSetId,
                    'product_id' => $productId,
                    'type' => $rentType,
                    'start_date' => $productSaveReturn->getBookingAvailableFrom(),
                    'end_date' => $productSaveReturn->getBookingAvailableTo(),
                    'info' => $bookingSlotData,
                    'qty' => $qty,
                    'available_qty' => $availableQty,
                    'total_slots' => $totalSlots
                ];
                $slotData = [
                    'product_id' => $productId,
                    'type' => $rentType,
                    'status' => 1
                ];
                $slotModel->load($slotProductId)
                ->setData($slotData)
                ->save();
                
                $infoModel->load($bookingProductId)
                ->addData($infoData)
                ->save();
            }
            $this->assignProduct($sellerId, $productId);
            $this->checkEnableOptionsAndBookingProduct($productId);
            $this->emulate->stopEnvironmentEmulation($environment);
            $returnArr = ['product_id'=>$productId];
            $response->setSuccess(true);
            $response->setMessage(__('Product Saved Successfully !!'));
            $response->setResponseData(json_encode($returnArr, true));
            return $response;
        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->setMessage(__('Something Went Wrong !!'));
            $response->setResponseData('');
            return $response;
        }
    }

    /**
     * Get Rent Slot Data
     *
     * @param object $productObj
     * @param array $bookingData
     *
     * @return array $data
     */
    public function getCalculatedRentSlotData($productObj, $bookingData)
    {
        try {
            $count = 1;
            $startDate = $productObj->getBookingAvailableFrom();
            $endDate = $productObj->getBookingAvailableTo();
            $days = 7;
            $totalDaysCountPerWeek = 0;
            if ($productObj->getSlotForAllDays()) {
                $days = 1;
                $totalDaysCountPerWeek = 7;
            }
            $totalSlots = 0;
            for ($i = 1; $i <= $days; $i++) {
                if (!empty($bookingData['slot_data'][$i])) {
                    if (!$productObj->getSlotForAllDays()) {
                        $totalDaysCountPerWeek++;
                    }
                    foreach ($bookingData['slot_data'][$i] as $key => $value) {
                        if (empty($data['slot_data'][$i][$key]['qty'])) {
                            $bookingData['slot_data'][$i][$key]['qty'] = 1;
                        }
                        $totalSlots = $totalSlots + $bookingData['slot_data'][$i][$key]['qty'];
                    }
                }
            }
            if ($productObj->getAvailableEveryWeek()) {
                $totalSlots = 99999999;
            } else {
                $numOfDays = $this->getDateDifference($startDate, $endDate);
                $totalWeeks = round($numOfDays/7);
                if ($totalWeeks) {
                    $totalDays = $totalWeeks * $totalDaysCountPerWeek;
                    $totalSlots = $totalSlots * $totalDays;
                }
            }
        } catch (\Exception $e) {
            $totalSlots = 0;
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
        $bookingData['total_slots'] = $totalSlots;
        return $bookingData;
    }

    /**
     * SavetableBooking
     *
     * @api
     * @param mixed $data
     * @param array $bookingData
     *
     * @return mixed
     */
    public function savetableBooking($data, $bookingData)
    {
        try {
            $sellerId = $this->userContext->getUserId();
            $storeId = 0;
            if (isset($bookingData['store_id'])) {
                $storeId = $bookingData['store_id'];
            }
            $environment = $this->emulate->startEnvironmentEmulation($storeId);
            /** saving default product */
            $productSaveReturn = $this->_productRepo->save(
                $data
            );
            /** response object */
            $response = $this->responseInterface;
            /** Product Required Info */
            $productSetId = $productSaveReturn->getAttributeSetId();
            $productId = $productSaveReturn->getId();
            $slotForAllDay = $productSaveReturn->getSlotForAllDays();
            $slotDuration = $productSaveReturn->getSlotDuration();
            $slotBreakTime = $productSaveReturn->getBreakTimeBwSlot();

            $stockData = $this->stockRegistry->getStockItem($productId);

            /** initialize variable for booking helper, info Model and Slot Model */
            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();
            
            /** Get Calculated Slot Data for Table Booking */
            $slotDataBooking = $this->getCalculatedSlotDataForTableBooking($productSaveReturn, $bookingData);
            /**
             * Get total Slots value for booking product
             */
            $totalSlots = $slotDataBooking['total_slots'];
            $qty = 0;

            if (isset($stockData->getData()['qty'])) {
                $qty = $stockData->getData()['qty'];
            } elseif (isset($productSaveReturn->getQuantityAndStockStatus()['qty'])) {
                $qty = $productSaveReturn->getQuantityAndStockStatus()['qty'];
            }

            foreach ($bookingData['slot_data'] as $dayKey => $dayValue) {
                unset($bookingData['slot_data'][$dayKey]);
                $bookingData['slot_data'][$dayKey+1] = $dayValue;
            }

            if ($slotForAllDay) {
                $bookingData['slot_data'][2] = $bookingData['slot_data'][1];
                $bookingData['slot_data'][3] = $bookingData['slot_data'][1];
                $bookingData['slot_data'][4] = $bookingData['slot_data'][1];
                $bookingData['slot_data'][5] = $bookingData['slot_data'][1];
                $bookingData['slot_data'][6] = $bookingData['slot_data'][1];
                $bookingData['slot_data'][7] = $bookingData['slot_data'][1];
            }
            
            $totalSlotTime = $slotDuration + $slotBreakTime;
            foreach ($bookingData['slot_data'] as $dayKey => $dayValue) {
                $slotIndex = 1;
                $currentSlots = 0;
                if (!empty($dayValue)) {
                    foreach ($dayValue as $key => $value) {
                        if (empty($value['qty'])) {
                            $value['qty'] = $productSaveReturn->getMaxCapacity();
                        }
                        $availableSlotArr = [];
                        $slotFromTimeStamp = strtotime($value['from']);
                        $slotToTimeStamp = strtotime($value['to']);
                        $totalTimeStamp = ($slotToTimeStamp - $slotFromTimeStamp)/60; // in hours
                        $currentSlots = $currentSlots + (int)($totalTimeStamp/$totalSlotTime);
                        $availableSlotArr[$slotIndex-1]['qty'] = $value['qty'];
                        $availableSlotArr[$slotIndex-1]['time'] = $value['from'];
                        for ($i=$slotIndex; $i < $currentSlots; $i++) {
                            $availableSlotArr[$i]['qty'] = $value['qty'];
                            $availableSlotArr[$i]['time'] = date(
                                "h:i a",
                                strtotime(
                                    '+'.$totalSlotTime.' minutes',
                                    strtotime($availableSlotArr[$i-1]['time'])
                                )
                            );
                        }
                        $slotIndex = $currentSlots;
                        $slotIndex++;
                        $bookingData['slot_data'][$dayKey][$key]['slots_info'] = $availableSlotArr;
                    }
                }
            }
            $bookingSlotData = $helper->getJsonEcodedString($bookingData['slot_data']);

            $slotProductId = 0;
            $slotCollection = $slotModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($slotCollection as $value) {
                $slotProductId = $value->getId();
            }
            $infoModelOld = null;

            $bookingProductId = 0;
            $bookingCollection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($bookingCollection as $value) {
                $bookingProductId = $value->getId();
                $infoModelOld = $value;
            }
            $type = $productSaveReturn->getSlotForAllDays();
            $slotQty = $productSaveReturn->getMaxCapacity();
            if ($this->canSaveTableSlots($infoModelOld, $bookingSlotData, $type, $slotQty)) {
                $infoData = [
                    'attribute_set_id' => $productSetId,
                    'product_id' => $productId,
                    'type' => $type,
                    'info' => $bookingSlotData,
                    'qty' => $qty,
                    'total_slots' => $totalSlots
                ];
                $slotData = [
                    'product_id' => $productId,
                    'type' => $type,
                    'status' => 1
                ];
                $slotModel->load($slotProductId)
                ->setData($slotData)
                ->save();

                $infoModel->load($bookingProductId)
                ->addData($infoData)
                ->save();
            }
            $this->checkEnableOptionsAndBookingProduct($productId);
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->assignProduct($sellerId, $productId);

            $returnArr = ['product_id'=>$productId];
            $response->setSuccess(true);
            $response->setMessage(__('Product Saved Successfully !!'));
            $response->setResponseData(json_encode($returnArr, true));
            return $response;
        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->setMessage(__('Something Went Wrong !!3'));
            $response->setResponseData('');
            return $response;
        }
    }

    /**
     * CheckEnableOptionsAndBookingProduct
     *
     * @param int $productId
     */
    public function checkEnableOptionsAndBookingProduct($productId)
    {
        try {
            $this->_bookingHelper->enableOptions($productId);
            $this->_bookingHelper->checkBookingProduct($productId);
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_BeforeViewProduct execute : ".$e->getMessage()
            );
        }
    }

    /**
     * SaveAppointmentBooking
     *
     * @api
     * @param mixed $data
     * @param mixed $bookingData
     *
     * @return mixed
     */
    public function saveAppointmentBooking($data, $bookingData)
    {
        try {
            $sellerId = $this->userContext->getUserId();
            $storeId = 0;
            if (isset($bookingData['store_id'])) {
                $storeId = $bookingData['store_id'];
            }
            $environment = $this->emulate->startEnvironmentEmulation($storeId);
            /** response object */
            $response = $this->responseInterface;
            /** product save default */
            $productSaveReturn = $this->_productRepo->save(
                $data
            );
            /** Get required product values */
            $attributeSetId = $productSaveReturn->getAttributeSetId();
            $productId = $productSaveReturn->getId();
            $stockData = $this->stockRegistry->getStockItem($productId);

            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();
               
            $dataProduct = $this->getCalculatedSlotData($bookingData, $productSaveReturn);
            $totalSlots = $dataProduct['total_slots'];

                $qty = 0;
            if (isset($stockData->getData()['qty'])) {
                $qty = $stockData->getData()['qty'];
            } elseif (isset($productSaveReturn->getQuantityAndStockStatus()['qty'])) {
                $qty = $productSaveReturn->getQuantityAndStockStatus()['qty'];
            }
            foreach ($bookingData['slot_data'] as $dayKey => $dayValue) {
                unset($bookingData['slot_data'][$dayKey]);
                $bookingData['slot_data'][$dayKey+1] = $dayValue;
            }
            if ($productSaveReturn->getSlotForAllDays()) {
                $bookingData['slot_data'][2] = $bookingData['slot_data'][1];
                $bookingData['slot_data'][3] = $bookingData['slot_data'][1];
                $bookingData['slot_data'][4] = $bookingData['slot_data'][1];
                $bookingData['slot_data'][5] = $bookingData['slot_data'][1];
                $bookingData['slot_data'][6] = $bookingData['slot_data'][1];
                $bookingData['slot_data'][7] = $bookingData['slot_data'][1];
            }
            $slotDuration = $productSaveReturn->getSlotDuration();
            $slotBreakTime = $productSaveReturn->getBreakTimeBwSlot();
            $totalSlotTime = $slotDuration + $slotBreakTime;
            foreach ($bookingData['slot_data'] as $dayKey => $dayValue) {
                $slotIndex = 1;
                $currentSlots = 0;
                if (!empty($dayValue)) {
                    foreach ($dayValue as $key => $value) {
                        if (empty($value['qty'])) {
                            $value['qty'] = 1;
                        }
                        if (!$productSaveReturn->getSlotHasQuantity()) {
                            $value['qty'] = 1;
                        }
                        $availableSlotArr = [];
                        $slotFromTimeStamp = strtotime($value['from']);
                        $slotToTimeStamp = strtotime($value['to']);
                        $totalTimeStamp = ($slotToTimeStamp - $slotFromTimeStamp)/60; // in hours
                        $currentSlots = $currentSlots + (int)($totalTimeStamp/$totalSlotTime);
                        $availableSlotArr[$slotIndex-1]['qty'] = $value['qty'];
                        $availableSlotArr[$slotIndex-1]['time'] = $value['from'];
                        for ($i=$slotIndex; $i < $currentSlots; $i++) {
                            $availableSlotArr[$i]['qty'] = $value['qty'];
                            $availableSlotArr[$i]['time'] = date(
                                "h:i a",
                                strtotime(
                                    '+'.$totalSlotTime.' minutes',
                                    strtotime($availableSlotArr[$i-1]['time'])
                                )
                            );
                        }
                        $slotIndex = $currentSlots;
                        $slotIndex++;
                        $bookingData['slot_data'][$dayKey][$key]['qty'] = $value['qty'];
                        $bookingData['slot_data'][$dayKey][$key]['slots_info'] = $availableSlotArr;
                    }
                }
            }
            $bookingSlotData = $helper->getJsonEcodedString($bookingData['slot_data']);
    
            $slotProductId = 0;
            $slotCollection = $slotModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($slotCollection as $value) {
                $slotProductId = $value->getId();
            }
            $infoModelOld = null;
    
            $bookingProductId = 0;
            $bookingCollection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($bookingCollection as $value) {
                $bookingProductId = $value->getId();
                $infoModelOld = $value;
            }
    
            $type = $productSaveReturn->getSlotForAllDays();
            if ($this->canSaveAppointmentSlots($infoModelOld, $bookingSlotData, $type)) {
                $infoData = [
                    'attribute_set_id' => $attributeSetId,
                    'product_id' => $productId,
                    'type' => $type,
                    'start_date' => $productSaveReturn->getBookingAvailableFrom(),
                    'end_date' => $productSaveReturn->getBookingAvailableTo(),
                    'info' => $bookingSlotData,
                    'qty' => $qty,
                    'total_slots' => $totalSlots
                ];
                $slotData = [
                    'product_id' => $productId,
                    'type' => $type,
                    'status' => 1
                ];
                $slotModel->load($slotProductId)
                ->setData($slotData)
                 ->save();
    
                $infoModel->load($bookingProductId)
                ->addData($infoData)
                ->save();
            }
            $infoData = [
                'attribute_set_id' => $attributeSetId,
                'product_id' => $productId,
                'type' => $productSaveReturn->getSlotForAllDays(),
                'start_date' => $productSaveReturn->getBookingAvailableFrom(),
                'end_date' => $productSaveReturn->getBookingAvailableTo(),
                'info' => $bookingSlotData,
                'qty' => $qty,
                'total_slots' => $totalSlots
            ];
    
            $infoModel->load($bookingProductId)
            ->addData($infoData)
            ->save();
            $this->checkEnableOptionsAndBookingProduct($productId);
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->assignProduct($sellerId, $productId);
                
            $returnArr = ['product_id'=>$productId];
            $response->setSuccess(true);
            $response->setMessage(__('Product Saved Successfully !!'));
            $response->setResponseData(json_encode($returnArr, true));
            return $response;

        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->setMessage(__('Something Went Wrong !!4'));
            $response->setResponseData('');
            return $response;
        }
    }

    /**
     * GetCalculatedSlotData
     *
     * @api
     * @param mixed $data
     * @param mixed $productSaveReturn
     *
     * @return mixed
     */
    public function getCalculatedSlotData($data, $productSaveReturn)
    {
        try {
            $count = 1;
            $startDate = $productSaveReturn->getBookingAvailableFrom();
            $endDate = $productSaveReturn->getBookingAvailableTo();
            $days = 7;
            $totalDaysCountPerWeek = 0;
            if ($productSaveReturn->getSlotForAllDays()) {
                $days = 1;
                $totalDaysCountPerWeek = 7;
            }
            $totalSlots = 0;
            $dayWiseSlots = [];
            for ($i = 1; $i <= $days; $i++) {
                if (empty($data['slot_data'][$i])) {
                    continue;
                }

                if (!$productSaveReturn->getSlotForAllDays()) {
                    $totalDaysCountPerWeek++;
                }
                foreach ($data['slot_data'][$i] as $key => $value) {
                    $slotFromTimeStamp = strtotime($value['from']);
                    $slotToTimeStamp = strtotime($value['to']);
                    if ($slotToTimeStamp > $slotFromTimeStamp) {
                        $totalTimeStamp = ($slotToTimeStamp - $slotFromTimeStamp)/60; // in hours
                    } else {
                        unset($data['slot_data'][$i][$key]);
                        $totalTimeStamp = 0;
                    }
                    if (empty($totalTimeStamp)) {
                        continue;
                    }

                    $slotDuration = (float)$data['slot_duration'];
                    $slotBreakTime = (float)$productSaveReturn->getBreakTimeBwSlot();
                    $currentSlots = (int)(($totalTimeStamp)/($slotDuration + $slotBreakTime));
                    if (!empty($data['slot_data'][$i][$key]['qty'])) {
                        $currentSlots = $currentSlots * $data['slot_data'][$i][$key]['qty'];
                    } else {
                        $data['slot_data'][$i][$key]['qty'] = 1;
                    }
                    $totalSlots = $totalSlots + $currentSlots;
                    if (isset($dayWiseSlots[$i])) {
                        $dayWiseSlots[$i] = $dayWiseSlots[$i] + $currentSlots;
                    } else {
                        $dayWiseSlots[$i] = $currentSlots;
                    }
                }
            }

            if ($productSaveReturn->getAvailableEveryWeek()) {
                $data['total_slots'] = 99999999;
                return $data;
            }
            
            $numOfDays = $this->getDateDifference($startDate, $endDate);
            $daysCount = [];
            if (!$productSaveReturn->getSlotForAllDays()) {
                if ($numOfDays > 0) {
                    $from = date_create($startDate);
                    $to = date_create($endDate);
                    $to->modify('+1 day');
                    $interval = new \DateInterval('P1D');
                    $periods = new \DatePeriod($from, $interval, $to);
                    
                    foreach ($periods as $period) {
                        if (isset($daysCount[$period->format('w')])) {
                            $daysCount[$period->format('w')] = $daysCount[$period->format('w')] + 1;
                        } else {
                            $daysCount[$period->format('w')] = 1;
                        }
                    }
                }
            } else {
                if (!empty($dayWiseSlots)) {
                    $allSlots = 0;
                    foreach ($dayWiseSlots as $slots) {
                        $allSlots = $allSlots + $slots;
                    }
                    $totalSlots = ($numOfDays+1) * $allSlots;
                }
            }

            if (!empty($daysCount)) {
                $totalSlots = 0;
                foreach ($daysCount as $dayNumber => $count) {
                    $dayIndex = ($dayNumber == 0) ? 7 : $dayNumber;
                    
                    if (isset($dayWiseSlots[$dayIndex])) {
                        $totalSlots = $totalSlots + ($dayWiseSlots[$dayIndex]*$count);
                    }
                }
            }
        } catch (\Exception $e) {
            $totalSlots = 0;
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
        $data['total_slots'] = $totalSlots;
        return $data;
    }

    /**
     * GetDateDifference
     *
     * @param string $firstDate
     * @param string $lastDate
     */
    public function getDateDifference($firstDate, $lastDate)
    {
        try {
            $date1 = date_create($firstDate);
            $date2 = date_create($lastDate);
            $diff = date_diff($date1, $date2);
            $numOfDays = (int)$diff->format("%R%a");
            $numOfDays++; // TODO: temp. fix to get exact no. of days.
            return $numOfDays;
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger("Model_Api_BookingProduct getDateDifference : ".$e->getMessage());
        }
    }

    /**
     * CanSaveAppointmentSlots
     *
     * @api
     * @param mixed $infoModelOld
     * @param mixed $newJsonData
     * @param mixed $type
     *
     * @return mixed
     */
    public function canSaveAppointmentSlots($infoModelOld, $newJsonData, $type)
    {
        $helper = $this->_bookingHelper;
        if (empty($infoModelOld) || $infoModelOld->getType() != $type) {
            return true;
        }

        $oldJson = $helper->getJsonDecodedString($infoModelOld->getInfo());
        $newJson = $helper->getJsonDecodedString($newJsonData);

        if ($this->checkAppointmentSlotsData($oldJson, $newJson)) {
            return true;
        }
    }

    /**
     * CheckAppointmentSlotsData
     *
     * @api
     * @param mixed $oldSlots
     * @param mixed $newSlots
     *
     * @return mixed
     */
    public function checkAppointmentSlotsData($oldSlots, $newSlots)
    {
        if (count($oldSlots) !== count($newSlots)) {
            return true;
        }

        foreach ($newSlots as $key => $daysData) {
            if (!isset($oldSlots[$key]) || count($oldSlots[$key]) !== count($daysData)) {
                return true;
            }
            
            foreach ($daysData as $slotKey => $slotsData) {
                if (!isset($oldSlots[$key][$slotKey])) {
                    return true;
                }
                $oldSlotData = $oldSlots[$key][$slotKey];
                if (isset($oldSlotData['from']) && $oldSlotData['from'] !== $slotsData['from']) {
                    return true;
                }
                
                if (isset($oldSlotData['to']) && $oldSlotData['to'] !== $slotsData['to']) {
                    return true;
                }
                
                if (isset($slotsData['slots_info']) && isset($oldSlotData['slots_info'])) {
                    $oldSlotInfo = $oldSlotData['slots_info'];
                    foreach ($slotsData['slots_info'] as $slotInfoKey => $slotInfo) {
                        if (isset($oldSlotInfo[$slotInfoKey]['qty'])
                            && $oldSlotInfo[$slotInfoKey]['qty'] !== $slotInfo['qty']
                        ) {
                            return true;
                        }

                        if (isset($oldSlotInfo[$slotInfoKey]['time'])
                            && $oldSlotInfo[$slotInfoKey]['time'] !== $slotInfo['time']
                        ) {
                            return true;
                        }
                    }
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * Get Slot Data for Table type Booking
     *
     * @param object $productObject
     * @param object $bookingData
     * @return array
     */
    public function getCalculatedSlotDataForTableBooking($productObject, $bookingData)
    {
        $slotForAllDay = $productObject->getSlotForAllDays();
        $slotDuration = $productObject->getSlotDuration();
        $breakTimeBWSlot = $productObject->getBreakTimeBwSlot();
        try {
            $count = 1;
            $days = 7;
            $totalSlots = 0;
            $totalDaysCountPerWeek = 0;
            if ($slotForAllDay) {
                $days = 1;
                $totalDaysCountPerWeek = 7;
            }
            for ($i = 1; $i <= $days; $i++) {
                if (!empty($bookingData['slot_data'][$i])) {
                    if (!$slotForAllDay) {
                        $totalDaysCountPerWeek++;
                    }
                    foreach ($bookingData['slot_data'][$i] as $key => $value) {
                        $slotFromTimeStamp = strtotime($value['from']);
                        $slotToTimeStamp = strtotime($value['to']);
                        if ($slotToTimeStamp > $slotFromTimeStamp) {
                            $totalTimeStamp = ($slotToTimeStamp - $slotFromTimeStamp)/60; // in hours
                        } else {
                            unset($bookingData['slot_data'][$i][$key]);
                            $totalTimeStamp = 0;
                        }
                        if ($totalTimeStamp) {
                            $slotDuration = (float)$slotDuration;
                            $slotBreakTime = (float)$breakTimeBWSlot;
                            $currentSlots = (int)(($totalTimeStamp)/($slotDuration + $slotBreakTime));
                            $totalSlots += $currentSlots;
                        }
                    }
                }
            }
            $totalSlots = 99999999;
        } catch (\Exception $e) {
            $totalSlots = 0;
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
        $data['total_slots'] = $totalSlots;
        return $data;
    }

    /**
     * CanSaveTableSlots
     *
     * @param object $infoModelOld
     * @param string $newJsonData
     * @param int $type
     * @param int $slotQty
     * @return bool
     */
    public function canSaveTableSlots($infoModelOld, $newJsonData, $type, $slotQty)
    {
        $helper = $this->_bookingHelper;
        if (empty($infoModelOld) || $infoModelOld->getType() != $type) {
            return true;
        }

        $oldJson = $helper->getJsonDecodedString($infoModelOld->getInfo());
        $newJson = $helper->getJsonDecodedString($newJsonData);

        if ($this->checkTableSlotsData($oldJson, $newJson, $slotQty)) {
            return true;
        }
    }

    /**
     * CheckTableSlotsData
     *
     * @param array $oldSlots
     * @param array $newSlots
     * @param int $slotQty
     * @return bool
     */
    public function checkTableSlotsData($oldSlots, $newSlots, $slotQty)
    {
        if (count($oldSlots) !== count($newSlots)) {
            return true;
        }
        foreach ($newSlots as $key => $daysData) {
            if (!isset($oldSlots[$key]) || count($oldSlots[$key]) !== count($daysData)) {
                return true;
            }
            
            foreach ($daysData as $slotKey => $slotsData) {
                if (!isset($oldSlots[$key][$slotKey])) {
                    return true;
                }
                $oldSlotData = $oldSlots[$key][$slotKey];
                if (isset($oldSlotData['from']) && $oldSlotData['from'] !== $slotsData['from']) {
                    return true;
                }
                
                if (isset($oldSlotData['to']) && $oldSlotData['to'] !== $slotsData['to']) {
                    return true;
                }
                
                if (isset($slotsData['slots_info']) && isset($oldSlotData['slots_info'])) {
                    $oldSlotInfo = $oldSlotData['slots_info'];
                    foreach ($slotsData['slots_info'] as $slotInfoKey => $slotInfo) {
                        if (isset($oldSlotInfo[$slotInfoKey]['qty'])
                            && $oldSlotInfo[$slotInfoKey]['qty'] !== $slotInfo['qty']
                        ) {
                            return true;
                        }

                        if (isset($oldSlotInfo[$slotInfoKey]['time'])
                            && $oldSlotInfo[$slotInfoKey]['time'] !== $slotInfo['time']
                        ) {
                            return true;
                        }
                    }
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * Check Whether New Slots or Not
     *
     * @param object $infoModelOld
     * @param string $newJsonData
     * @param int    $type
     * @param string $dateFrom
     * @param string $dateTo
     * @param int    $availableQty
     * @return bool
     */
    public function canSaveRentalSlots($infoModelOld, $newJsonData, $type, $dateFrom, $dateTo, $availableQty)
    {
        $helper = $this->_bookingHelper;
        if ($infoModelOld === null) {
            return true;
        }

        if ($infoModelOld->getAvailableQty() != $availableQty) {
            return true;
        }

        if ($infoModelOld->getType() != $type) {
            return true;
        }

        if ($infoModelOld->getStartDate() != $dateFrom || $infoModelOld->getEndDate() != $dateTo) {
            return true;
        }
        
        $oldJson = $helper->getJsonDecodedString($infoModelOld->getInfo());
        $newJson = $helper->getJsonDecodedString($newJsonData);
        if ($this->checkRentalSlotsData($oldJson, $newJson)) {
            return true;
        }
    }

    /**
     * CheckRentalSlotsData
     *
     * @param array $oldSlots
     * @param array $newSlots
     * @return bool
     */
    public function checkRentalSlotsData($oldSlots, $newSlots)
    {
        foreach ($newSlots as $key => $daysData) {
            if (!isset($oldSlots[$key]) || count($oldSlots[$key]) !== count($daysData)) {
                return true;
            }
            
            foreach ($daysData as $slotKey => $slotsData) {
                if (isset($oldSlots[$key][$slotKey])) {
                    $oldSlotData = $oldSlots[$key][$slotKey];
                    if (isset($oldSlotData['from']) && $oldSlotData['from'] !== $slotsData['from']) {
                        return true;
                    }
                    
                    if (isset($oldSlotData['to']) && $oldSlotData['to'] !== $slotsData['to']) {
                        return true;
                    }
                    
                    if (isset($oldSlotData['qty']) && $oldSlotData['qty'] !== $slotsData['qty']) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * Save Event Booking
     *
     * @api
     * @param \Magento\Catalog\Api\Data\ProductInterface $data
     * @param mixed $bookingData
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface
     */
    public function saveEventBooking($data, $bookingData)
    {
        try {
            $sellerId = $this->userContext->getUserId();
            $storeId = 0;
            if (isset($bookingData['store_id'])) {
                $storeId = $bookingData['store_id'];
            }
            $environment = $this->emulate->startEnvironmentEmulation($storeId);
            $response = $this->responseInterface;
            /** product save default */
            $productSaveReturn = $this->_productRepo->save(
                $data
            );
            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();

            /** product id variable */
            $productId = $productSaveReturn->getId();
            /** stockData object */
            $stockData = $this->stockRegistry->getStockItem($productId);
            /** product's attribute set id */
            $productSetId = $productSaveReturn->getAttributeSetId();
            /** product object */
            $productObject = $this->_product->create()->load($productId);

            /** delete custom option section */
            if ($productObject->getOptions()) {
                foreach ($productObject->getOptions() as $opt) {
                    if ($opt->getTitle()=='Event Tickets') {
                        $opt->delete();
                    }
                }
            }
            /** delete custom option section end */

            /** add custom option section */
            $productObject->setHasOptions(1);
            $productObject->setCanSaveCustomOptions(true);
            $totalSlots = 0;
            
            foreach ($bookingData['options'] as $arrayOption) {
                $option = $this->catalogProductOptionFactory->create()
                        ->setProductId($productId)
                        ->setStoreId($productObject->getStoreId())
                        ->addData($arrayOption);
                $option->save();
                $productObject->addOption($option);
                if (isset($arrayOption['values'][0]['qty'])) {
                    $totalSlots +=  $arrayOption['values'][0]['qty'];
                }
            }
            /** add custom option section end */

            if ($data->getEventChartAvailable()) {
                $fileName =  $data->getEventChartImage();
                $image = $bookingData['base_64_img'];
                $mediaFullPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath('mpadvancedbookingsystem/eventChartImage');
                
                if (!$this->fileDriver->isExists($mediaFullPath)) {
                    $this->fileDriver->createDirectory($mediaFullPath, 0775);
                }
                /* Check File is exist or not */
                $fullFilepath = $mediaFullPath . $fileName;
                if ($this->fileDriver->isExists($fullFilepath)) {
                    $fileName = rand() . time() . $fileName;
                }
                $fileContent = $this->urlDecoder->decode($image);
                $savedFile = $this->fileDriver->fileOpen($mediaFullPath . '/' . $fileName, "wb");
                $this->fileDriver->fileWrite($savedFile, $fileContent);
                $this->fileDriver->fileClose($savedFile);
                $uploadedFileName = $fileName;
            }

            $eventOptions = $productObject->getCustomOptions();

            if (isset($stockData->getData()['qty'])) {
                $qty = $stockData->getData()['qty'];
            } elseif (isset($productSaveReturn->getQuantityAndStockStatus()['qty'])) {
                $qty = $productSaveReturn->getQuantityAndStockStatus()['qty'];
            }
            $optionIndex = 0;
            foreach ($eventOptions as $index => $option) {
                if ($option['title'] == 'Event Tickets') {
                    $optionIndex = $index;
                    break;
                }
            }

            $updatedBookingOptions = [];
            $eventOptions = $productObject->getOptions();
            $index = 0;

            foreach ($productObject->getProductOptionsCollection() as $key => $customOption) {
                $customOptionData = $customOption->getData();
                if (!empty($customOptionData['title']) && $customOptionData['title'] == 'Event Tickets') {
                    $updatedBookingOptions[$index] = $customOptionData;
                    $customOptionValues = $customOption->getValues() ?? [];
                    $optionValuesData = [];
                    foreach ($customOptionValues as $customOptionValue) {
                        $optionValuesData[] = $customOptionValue->getData();
                    }
                    $updatedBookingOptions[$index]['values'] = $optionValuesData;
                }
                $index++;
            }
            if (!empty($updatedBookingOptions)) {
                $bookingOptionsData = $helper->getJsonEcodedString($updatedBookingOptions);
            } else {
                $bookingOptionsData = $helper->getJsonEcodedString($eventOptions);
            }

            $slotProductId = 0;
            $slotCollection = $slotModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($slotCollection as $value) {
                $slotProductId = $value->getId();
            }
            $slotData = [
                'product_id' => $productId,
                'type' => 1,
                'status' => 1
            ];
            $slotModel->load($slotProductId)
            ->setData($slotData)
            ->save();
            $bookingProductId = 0;
            $bookingCollection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($bookingCollection as $value) {
                $bookingProductId = $value->getId();
            }
            $fromTime = date(
                'Y-m-d H:i',
                strtotime($productSaveReturn->getEventDateFrom())
            );
            $toTime = date(
                'Y-m-d H:i',
                strtotime($productSaveReturn->getEventDateTo())
            );

            $infoData = [
                'attribute_set_id' => $productSetId,
                'product_id' => $productId,
                'type' => 1,
                'start_date' => $fromTime,
                'end_date' => $toTime,
                'info' => $bookingOptionsData,
                'qty' => $qty,
                'total_slots' => $totalSlots
            ];
            $infoModel->load($bookingProductId)
            ->addData($infoData)
            ->save();

            $this->checkEnableOptionsAndBookingProduct($productId);
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->assignProduct($sellerId, $productId);

            $returnArr = ['product_id'=>$productId];
            $response->setSuccess(true);
            $response->setMessage(__('Product Saved Successfully !!'));
            $response->setResponseData(json_encode($returnArr, true));
            return $response;
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
    }

    /**
     * Save Default Booking Product.
     *
     * @param object $data
     * @param array $bookingData
     */
    public function saveDefaultBooking($data, $bookingData)
    {
        try {
            $sellerId = $this->userContext->getUserId();
            $storeId = 0;
            if (isset($bookingData['store_id'])) {
                $storeId = $bookingData['store_id'];
            }
            $this->emulate->startEnvironmentEmulation($storeId);
            /** response object */
            $response = $this->responseInterface;
            /** saving default product */
            $productSaveReturn = $this->_productRepo->save(
                $data
            );
            $productId = $productSaveReturn->getId();
            /** get object of stock data */
            $stockData = $this->stockRegistry->getStockItem($productId);

            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();
            $bookingType = $this->getBookingType($bookingData, $productId);
            $isNew = false;
            $collection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            if ($collection->getSize()<=0) {
                $isNew = true;
            }
            if ($collection->getSize()<=0 && $bookingType == 0) {
                $returnArr = ['product_id'=>$productId];
                $response->setSuccess(true);
                $response->setMessage(__('Product Saved Successfully !!'));
                $response->setResponseData(json_encode($returnArr, true));
                return $response;
            } else {
                $previousBookingType = $helper->getBookingType($productId);
                if ($bookingType == 0 && $previousBookingType == 0) {
                    $returnArr = ['product_id'=>$productId];
                    $response->setSuccess(true);
                    $response->setMessage(__('Product Saved Successfully !!'));
                    $response->setResponseData(json_encode($returnArr, true));
                    return $response;
                }
            }
            if ($bookingType == 0 || ($bookingType==2 && !array_key_exists("start", $bookingData['info']))) {
                $helper->disableSlots($productId);
                $helper->deleteInfo($productId);
                $returnArr = ['product_id'=>$productId];
                $response->setSuccess(true);
                $response->setMessage(__('Product Saved Successfully !!'));
                $response->setResponseData(json_encode($returnArr, true));
                return $response;
            }

            if (!array_key_exists("info", $bookingData)) {
                $returnArr = ['product_id'=>$productId];
                $response->setSuccess(true);
                $response->setMessage(__('Product Saved Successfully !!'));
                $response->setResponseData(json_encode($returnArr, true));
                return $response;
            }

            $startDate = $bookingData['start_date'];
            $endDate = $bookingData['end_date'];
            $qty = 0;

            if (isset($stockData->getData()['qty'])) {
                $qty = $stockData->getData()['qty'];
            } elseif (isset($productSaveReturn->getQuantityAndStockStatus()['qty'])) {
                $qty = $productSaveReturn->getQuantityAndStockStatus()['qty'];
            }

            $result = $this->prepareOptions($bookingData, $bookingType);
            if (!empty($result)) {
                $bookingInfo = $result['info'];

                $count = $result['total'];
                //Setting Booking Information
                // $bookingInfo = $helper->getSerializedString($bookingInfo);
                $bookingInfo = $helper->getJsonEcodedString($bookingInfo);
                if (!$isNew && $isNew==false) {
                    $bookingData = $helper->getBookingInfo($productId);

                    if ($this->canSaveSlots($bookingData, $bookingInfo, $qty)) {
                        $helper->disableSlots($productId);
                        $slotData = [
                            'product_id' => $productId,
                            'type' => $bookingType,
                            'status' => 1
                        ];

                        $slotModel->setData($slotData)->save();
                    }
                } else {
                    $slotData = [
                        'product_id' => $productId,
                        'type' => $bookingType,
                        'status' => 1
                    ];
                    $slotModel->setData($slotData)->save();
                }
                $infoData = [
                    'product_id' => $productId,
                    'type' => $bookingType,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'info' => $bookingInfo,
                    'qty' => $qty,
                    'total_slots' => $count
                ];
                $collection = $infoModel->getCollection();
                $item = $helper->getDataByField($productId, 'product_id', $collection);
                if ($item) {
                    $id = $item->getId();
                    $infoModel->addData($infoData)->setId($id)->save();
                } else {
                    $infoModel->setData($infoData)->save();
                }
                $product = $helper->getProduct($productId);
                $productExtension = $product->getExtensionAttributes();
                $manageStock = $productExtension->getStockItem()->getManageStock();
                if ($manageStock) {
                    $product->getExtensionAttributes()->getStockItem()->setUseConfigManageStock(0);
                    $product->getExtensionAttributes()->getStockItem()->setManageStock(0);
                    $product->save();
                }

            }
            $this->checkEnableOptionsAndBookingProduct($productId);
            $this->assignProduct($sellerId, $productId);
            $returnArr = ['product_id'=>$productId];
            $response->setSuccess(true);
            $response->setMessage(__('Product Saved Successfully !!'));
            $response->setResponseData(json_encode($returnArr, true));
            return $response;
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
    }

    /**
     * Get Booking Type
     *
     * @param array $bookingData
     * @param int $productId
     * @return int
     */
    public function getBookingType($bookingData, $productId)
    {
        $bookingType = 0;
        try {
            if (array_key_exists("default_booking_type", $bookingData)) {
                $bookingType = $bookingData['default_booking_type'];
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterProductSave getBookingType : ".$e->getMessage()
            );
        }

        return (int)$bookingType;
    }

    /**
     * Prepare Options
     *
     * @param array $data
     * @param int   $bookingType
     * @return array
     */
    public function prepareOptions($data, $bookingType)
    {
        $result = [];
        try {
            if ($bookingType == 1) {
                $result = $this->prepareManyBookingOptions($data);
            } elseif ($bookingType == 2) {
                $result = $this->prepareOneBookingOptions($data);
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger("Model_Api_BookingProduct prepareOptions : ".$e->getMessage());
        }
        return $result;
    }

    /**
     * Prepare Many Booking Options
     *
     * @param array $data
     * @return array
     */
    public function prepareManyBookingOptions($data)
    {
        try {
            $count = 1;
            $info = $data['info'];
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $slotTime = $data['time_slot'];
            $breakTime = $data['break_time'];
            $numOfDays = $this->getDateDifference($startDate, $endDate);
            for ($i = 0; $i <= $numOfDays; $i++) {
                $date = strtotime("+$i day", strtotime($startDate));
                $day = strtolower(date("l", $date));
                $status = $info[$day]['status'];

                if ($status == 1) {
                    $startHour = $info[$day]['start_hour'];
                    $startMinute = $info[$day]['start_minute'];
                    $endHour = $info[$day]['end_hour'];
                    $endMinute = $info[$day]['end_minute'];
                    $startCount = $startHour*60 + $startMinute;
                    $endCount = $endHour*60 + $endMinute;
                    $diff = $endCount - $startCount;
                    while ($diff >= $slotTime) {
                        $diff = $diff - ($breakTime + $slotTime);
                        $count++;
                    }
                }
            }

            unset($data['info']['start_hour']);
            unset($data['info']['start_minute']);
            unset($data['info']['end_hour']);
            unset($data['info']['end_minute']);
            $bookingInfo = $data['info'];
            $bookingInfo['time_slot'] = $slotTime;
            $bookingInfo['break_time'] = $breakTime;
            $result = [];
            $result['info'] = $bookingInfo;
            $result['start_date'] = $startDate;
            $result['end_date'] = $endDate;
            $result['total'] = $count-1;
            return $result;
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Model_Api_BookingProduct prepareManyBookingOptions : ".$e->getMessage()
            );
        }
    }

    /**
     * Prepare One Booking Options
     *
     * @param array $data
     * @return array
     */
    public function prepareOneBookingOptions($data)
    {
        try {
            $count = 1;
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $numOfDays = $this->getDateDifference($startDate, $endDate);
            $startData = $data['info']['start'];
            $endData = $data['info']['end'];
            $startDays = $startData['day'];

            for ($i = 0; $i <= $numOfDays; $i++) {
                $date = strtotime("+$i day", strtotime($startDate));
                $day = strtolower(date("l", $date));
                $date = strtolower(date("Y-m-d", $date));
                foreach ($startDays as $key => $startDay) {
                    if ($day == $startDay) {
                        $count++;
                    }
                }
            }

            $bookingInfo = ['start' => $startData, 'end' => $endData];
            $result = [];
            $result['info'] = $bookingInfo;
            $result['total'] = $count-1;
            return $result;
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Model_Api_BookingProduct Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * Check Whether New Slots or Not
     *
     * @param array  $bookingData
     * @param string $bookingInfo
     * @param int    $qty
     * @return bool
     */
    public function canSaveSlots($bookingData, $bookingInfo, $qty)
    {
        try {
            $helper = $this->_bookingHelper;
            if ($bookingData['is_booking']) {
                if (is_array($bookingData['info'])) {
                    $tempInfo = $helper->getJsonEcodedString($bookingData['info']);
                } else {
                    $tempInfo = $bookingData['info'];
                }

                if (strcmp($bookingInfo, $tempInfo) !== 0) {
                    return true;
                } else {
                    if ($bookingData['qty'] != $qty) {
                        return true;
                    }
                }
            } else {
                return true;
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Model_Api_BookingProduct_canSaveSlots Exception : ".$e->getMessage()
            );
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function assignProduct($sellerId, $productId)
    {
        try {
            $returnArray = [];
            $collection = $this->sellerFactory->create()
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('seller_id', $sellerId)
                ->addFieldToFilter('is_seller', 1);
            if ($collection->getSize() <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Seller not found')
                );
            }
            // set product status to 1 to assign selected products from seller
            $productCollection = $this->productInterfaceFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', ['in' => $productId]);
            if ($productCollection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Product(s) are not found')
                );
            }
            $prdAssignTosellerCount = 0;
            foreach ($productCollection as $product) {
                $proid = $product->getId();
                $userid = '';
                $collection = $this->mpProductFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('mageproduct_id', $proid);
                $flag = 1;
                foreach ($collection as $coll) {
                    $flag = 0;
                    if ($sellerId != $coll['seller_id']) {
                        $returnArray['message'][] = __(
                            'The product with id %1 is already assigned to other seller.',
                            $proid
                        );
                    } else {
                        $returnArray['message'][] = __(
                            'The product with id %1 is already assigned to the seller.',
                            $proid
                        );
                        $coll->setAdminassign(1)->save();
                    }
                }
                if ($flag) {
                    $prdAssignTosellerCount++;
                    $collection1 = $this->mpProductFactory->create();
                    $collection1->setMageproductId($proid);
                    $collection1->setSellerId($sellerId);
                    $collection1->setStatus($product->getStatus());
                    $collection1->setAdminassign(1);
                    $collection1->setCreatedAt($this->date->gmtDate());
                    $collection1->setUpdatedAt($this->date->gmtDate());
                    $collection1->save();
                }
            }
            return true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
