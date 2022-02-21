<?php

namespace GloCurrency\UnitedBank\Enums;

use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;

enum TransactionStateCodeEnum: string
{
    case LOCAL_UNPROCESSED = 'local_unprocessed';
    case LOCAL_EXCEPTION = 'local_exception';
    case STATE_NOT_ALLOWED = 'state_not_allowed';
    case API_REQUEST_EXCEPTION = 'api_request_exception';
    case NO_ERROR_CODE_PROPERTY = 'no_error_code_property';
    case UNEXPECTED_ERROR_CODE = 'unexpected_error_code';
    case PAID = 'paid';
    case API_ERROR = 'api_error';
    case API_TIMEOUT = 'api_timeout';
    case API_MALFUNCTION = 'api_malfunction';
    case INSUFFICIENT_FUNDS = 'insufficient_funds';
    case DUPLICATE_TRANSACTION = 'duplicate_transaction';
    case RECIPIENT_BANK_ACCOUNT_INVALID = 'recipient_bank_account_invalid';
    case RECIPIENT_TRANSFER_LIMIT_EXCEEDED = 'recipient_transfer_limit_exceeded';
    case NO_ACCOUNT_INFORMATION_ID = 'no_account_information_id';
    case NO_ACCOUNT_INFORMATION_RESPONSE_CODE = 'no_account_information_response_code';
    case ACCOUNT_INFORMATION_RESPONSE_CODE_NOT_SUCCESSFUL = 'account_information_response_code_not_successful';

    /**
     * Get the ProcessingItem state based on Transaction state.
     */
    public function getProcessingItemStateCode(): MProcessingItemStateCodeEnum
    {
        return match ($this) {
            self::LOCAL_UNPROCESSED => MProcessingItemStateCodeEnum::PENDING,
            self::LOCAL_EXCEPTION => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::STATE_NOT_ALLOWED => MProcessingItemStateCodeEnum::EXCEPTION,
            self::API_REQUEST_EXCEPTION => MProcessingItemStateCodeEnum::EXCEPTION,
            self::NO_ERROR_CODE_PROPERTY => MProcessingItemStateCodeEnum::EXCEPTION,
            self::UNEXPECTED_ERROR_CODE => MProcessingItemStateCodeEnum::EXCEPTION,
            self::PAID => MProcessingItemStateCodeEnum::PROCESSED,
            self::API_ERROR => MProcessingItemStateCodeEnum::PROVIDER_NOT_ACCEPTING_TRANSACTIONS,
            self::API_TIMEOUT => MProcessingItemStateCodeEnum::PROVIDER_TIMEOUT,
            self::API_MALFUNCTION => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::INSUFFICIENT_FUNDS => MProcessingItemStateCodeEnum::PROVIDER_NOT_ACCEPTING_TRANSACTIONS,
            self::DUPLICATE_TRANSACTION => MProcessingItemStateCodeEnum::EXCEPTION,
            self::RECIPIENT_BANK_ACCOUNT_INVALID => MProcessingItemStateCodeEnum::RECIPIENT_BANK_ACCOUNT_INVALID,
            self::RECIPIENT_TRANSFER_LIMIT_EXCEEDED => MProcessingItemStateCodeEnum::RECIPIENT_TRANSFER_LIMIT_EXCEEDED,
            self::NO_ACCOUNT_INFORMATION_ID => MProcessingItemStateCodeEnum::PROVIDER_NOT_ACCEPTING_TRANSACTIONS,
            self::NO_ACCOUNT_INFORMATION_RESPONSE_CODE => MProcessingItemStateCodeEnum::PROVIDER_NOT_ACCEPTING_TRANSACTIONS,
            self::ACCOUNT_INFORMATION_RESPONSE_CODE_NOT_SUCCESSFUL => MProcessingItemStateCodeEnum::RECIPIENT_BANK_ACCOUNT_INVALID,
        };
    }
}
