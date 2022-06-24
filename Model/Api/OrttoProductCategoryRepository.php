<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ortto\Connector\Api\OrttoProductCategoryRepositoryInterface;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Ortto\Connector\Model\Data\OrttoProductCategoryFactory;

class OrttoProductCategoryRepository implements OrttoProductCategoryRepositoryInterface
{
    private OrttoLogger $logger;
    private OrttoProductCategoryFactory $productCategoryFactory;
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        OrttoLogger $logger,
        OrttoProductCategoryFactory $productCategoryFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->logger = $logger;
        $this->productCategoryFactory = $productCategoryFactory;
        $this->categoryRepository = $categoryRepository;
    }

    /** @inheirtDoc */
    public function getById(int $categoryId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId);
            return $this->convert($category);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e, sprintf("Category ID %d could not be found.", $categoryId));
            return false;
        }
    }

    /**
     * @param CategoryInterface $category
     * @return \Ortto\Connector\Api\Data\OrttoProductCategoryInterface
     */
    private function convert($category)
    {
        $data = $this->productCategoryFactory->create();
        $data->setId(To::int($category->getEntityId()));
        $data->setName((string)$category->getName());
        $data->setDescription((string)$category->getDescription() ?? '');
        $data->setProductsCount(To::int($category->getProductCount()));
        try {
            if ($imageURL = $category->getImageUrl()) {
                $data->setImageURL((string)$imageURL);
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e, "Failed to fetch product category image");
        }
        return $data;
    }
}
