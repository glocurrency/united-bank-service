<?php

namespace GloCurrency\UnitedBank\Enums;

use BrokeYourBike\UnitedBank\Enums\ErrorCodeEnum;

class ErrorCodeFactory
{
    public static function getTransactionStateCode(ErrorCodeEnum $errorCode): TransactionStateCodeEnum
    {
        return match ($errorCode) {
            ErrorCodeEnum::SUCCESS => TransactionStateCodeEnum::PAID,
            ErrorCodeEnum::INVALID_SCHEME_TYPE => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INVALID_ACCOUNT_NUMBER => TransactionStateCodeEnum::RECIPIENT_BANK_ACCOUNT_INVALID,
            ErrorCodeEnum::UNSUPPORTED_REQUEST_FUNCTION => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INSUFFICIENT_FUNDS => TransactionStateCodeEnum::INSUFFICIENT_FUNDS,
            ErrorCodeEnum::TRANSACTION_NOT_PERMITTED => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::WITHDRAWAL_AMOUNT_LIMIT_EXCEEDED => TransactionStateCodeEnum::RECIPIENT_TRANSFER_LIMIT_EXCEEDED,
            ErrorCodeEnum::FORMAT_ERROR => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::DATA_RETRIEVAL_ERROR => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::MISSING_COUNTRY_CODE => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INVALID_DATA_TYPE => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::BANK_CONFIG_ERROR => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::SOL_CONFIG_ERROR => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::UNKNOWN_ERROR => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::SECURITY_VIOLATION_LEVEL_0 => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::SECURITY_VIOLATION_LEVEL_1 => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INVALID_CHEQUE_STATUS => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::TRANSFER_LIMIT_EXCEEDED => TransactionStateCodeEnum::RECIPIENT_TRANSFER_LIMIT_EXCEEDED,
            ErrorCodeEnum::CHEQUES_ARE_IN_DIFFERENT_BOOKS => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::NOT_ALL_CHEQUES_COULD_BE_STOPPED => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::CHEQUE_NOT_ISSUED_TO_ACCOUNT => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::ACCOUNT_CLOSED => TransactionStateCodeEnum::RECIPIENT_BANK_ACCOUNT_INVALID,
            ErrorCodeEnum::INVALID_CURRENCY => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::BLOCK_NOT_FOUND => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::CHEQUE_STOPPED => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INVALID_RATE_CURRENCY_COMBINATION => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::CHEQUE_BOOK_ALREADY_ISSUED => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::DD_ALREADY_PAID => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::NETWORK_MESSAGE_WAS_ACCEPTED => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INVALID_TRANSACTION_CODE => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::CUT_OVER_IN_PROGRESS => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::SERVICE_ERROR => TransactionStateCodeEnum::API_TIMEOUT,
            ErrorCodeEnum::SERVICE_TIMEOUT => TransactionStateCodeEnum::API_TIMEOUT,
            ErrorCodeEnum::DUPLICATE_STAN => TransactionStateCodeEnum::DUPLICATE_TRANSACTION,
            ErrorCodeEnum::INVALID_REQUEST_JSON => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INVALID_TRANSACTION_ID => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::COUNTRIES_MISSMATCH => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INVALID_CREDENTIALS => TransactionStateCodeEnum::API_ERROR,
        };
    }
}
