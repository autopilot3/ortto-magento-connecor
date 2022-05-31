<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\LocalizedException;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\Logger;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\JsonConverter;

class CategoryData
{
    private Logger $logger;
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        Logger $logger,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
    }

    /** @var CategoryInterface|Category */
    private $category;

    /**
     * @param int $categoryID
     * @return bool
     */
    public function loadById(int $categoryID)
    {
        try {
            $this->category = $this->categoryRepository->get($categoryID);
            return true;
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e, sprintf("Category ID %d could not be found.", $categoryID));
            return false;
        }
    }

    public function toArray(): array
    {
        if (empty($this->category)) {
            return [];
        }
        $result = [
            'id' => To::int($this->category->getId()),
            'name' => $this->category->getName(),
            'description' => $this->category->getDescription() ?? '',
            'products_count' => $this->category->getProductCount(),
        ];
        try {
            if ($imageURL = $this->category->getImageUrl()) {
                $result['image_url'] = $imageURL;
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e, "Failed to fetch product category image");
        }
        return $result;
    }

    /**
     * @return string|bool
     */
    public function toJSON()
    {
        return JsonConverter::convert($this->toArray());
    }
}
