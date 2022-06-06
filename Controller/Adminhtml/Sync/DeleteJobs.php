<?php
declare(strict_types=1);

namespace Ortto\Connector\Controller\Adminhtml\Sync;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ortto\Connector\Api\Data\SyncJobInterface;
use Ortto\Connector\Api\SyncJobRepositoryInterface;
use Ortto\Connector\Model\ResourceModel\SyncJob\CollectionFactory;

class DeleteJobs extends Action
{
    protected $resultPageFactory = false;

    /**
     * @var Filter
     */
    private Filter $filter;
    private CollectionFactory $collectionFactory;
    private SyncJobRepositoryInterface $jobRepository;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SyncJobRepositoryInterface $jobRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->jobRepository = $jobRepository;
    }

    /**
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $jobsDeleted = 0;
        /** @var SyncJobInterface $record */
        foreach ($collection->getItems() as $record) {
            $this->jobRepository->deleteById($record->getEntityId());
            $jobsDeleted++;
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 job(s) have been deleted.', $jobsDeleted));

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/jobs');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ortto_Connector::sync_jobs_admin');
    }
}
