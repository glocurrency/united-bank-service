<?php

namespace GloCurrency\UnitedBank\Exceptions;

use GloCurrency\UnitedBank\Models\Transaction;
use GloCurrency\UnitedBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\UnitedBank\Models\AccountInformationResponse;
use BrokeYourBike\UnitedBank\Enums\ErrorCodeEnum;
use BrokeYourBike\UnitedBank\Client;

final class CreditTransactionException extends \RuntimeException
{
    private TransactionStateCodeEnum $stateCode;
    private string $stateCodeReason;

    public function __construct(TransactionStateCodeEnum $stateCode, string $stateCodeReason, ?\Throwable $previous = null)
    {
        $this->stateCode = $stateCode;
        $this->stateCodeReason = $stateCodeReason;

        parent::__construct($stateCodeReason, 0, $previous);
    }

    public function getStateCode(): TransactionStateCodeEnum
    {
        return $this->stateCode;
    }

    public function getStateCodeReason(): string
    {
        return $this->stateCodeReason;
    }

    public static function stateNotAllowed(Transaction $transaction): self
    {
        $className = $transaction::class;
        $message = "{$className} state_code `{$transaction->state_code->value}` not allowed";
        return new static(TransactionStateCodeEnum::STATE_NOT_ALLOWED, $message);
    }

    public static function apiRequestException(\Throwable $e): self
    {
        $className = Client::class;
        $message = "Exception during {$className} request with message: `{$e->getMessage()}`";
        return new static(TransactionStateCodeEnum::API_REQUEST_EXCEPTION, $message);
    }

    public static function unexpectedErrorCode(?string $code): self
    {
        $className = ErrorCodeEnum::class;
        $message = "Unexpected {$className}: `{$code}`";
        return new static(TransactionStateCodeEnum::UNEXPECTED_ERROR_CODE, $message);
    }

    public static function accountInformationResponseCodeNotSuccessful(AccountInformationResponse $response): self
    {
        $className = $response::class;
        $message = "{$className} json `{$response->getRawResponse()->getBody()}` `accountInformation.responseCode` not success";
        return new static(TransactionStateCodeEnum::ACCOUNT_INFORMATION_RESPONSE_CODE_NOT_SUCCESSFUL, $message);
    }
}
