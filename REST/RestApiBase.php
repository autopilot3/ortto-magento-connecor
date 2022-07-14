<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\ScopeManagerInterface;

class RestApiBase
{
    private ScopeManagerInterface $scopeManager;

    public function __construct(ScopeManagerInterface $scopeManager)
    {
        $this->scopeManager = $scopeManager;
    }

    /**
     * @throws Exception
     */
    protected function validateScope(
        string $scopeType,
        int $scopeId,
        bool $validateConnection = true
    ): ConfigScopeInterface {
        try {
            $scope = $this->scopeManager->initialiseScope($scopeType, $scopeId);
        } catch (\InvalidArgumentException $e) {
            throw $this->httpError(sprintf('Invalid scope: %s', $e->getMessage()), 400);
        } catch (NoSuchEntityException $e) {
            throw $this->httpError('Scope not found', 404);
        } catch (\Exception $e) {
            throw $this->httpError($e->getMessage());
        }

        if ($validateConnection && !$scope->isConnected()) {
            throw $this->httpError('Scope is not connected', 406);
        }
        return $scope;
    }

    protected function httpError(string $message, int $code = 500): Exception
    {
        return new Exception(__($message), $code, $code);
    }
}
