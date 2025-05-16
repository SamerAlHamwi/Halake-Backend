<?php

namespace App\Models;

use Illuminate\Validation\Rules\Enum;

final class Constants
{
    // Tax Type
    const taxPercent = 0;
    const taxFixed = 1;

    // Tax Type
    const paymentTypePrepaid = 0;
    const paymentTypePostpaid = 1;

    // const Salon Gender server
    const salonGenderMale = 0;
    const salonGenderFemale = 1;
    const salonGenderUnisex = 2;

    // Salon Availability
    const orderPlacedPending = 0;
    const orderAccepted = 1;
    const orderCompleted = 2;
    const orderDeclined = 3;
    const orderCancelled = 4;

    // Credit/Debit
    const credit = 1;
    const debit = 0;

    //User Statement Entries
    const deposit = 0;
    const purchase = 1;
    const withdraw = 2;
    const refund = 3;

    // Salon Statement Entries
    const salonWalletEarning = 0;
    const salonWalletCommission = 1;
    const salonWalletWithdraw = 2;
    const salonWalletOrderRefund = 3;
    const salonWalletPayoutReject = 4;

    // Prefixes
    const prefixPlatformEarningHistory = "PLEAR";
    const prefixSalonEarningHistory = "SLEAR";
    const prefixServiceNumber = "SER";
    const prefixSalonNumber = "SL";
    const prefixUserWithDrawRequestNumber = "URWTH";
    const prefixSalonWithDrawRequestNumber = "SLWTH";
    const prefixBookingId = "BOKID";
    const prefixSalonTransactionId = "SLTRID";
    const prefixUserTransactionId = "URTRID";

    // Service Status
    const statusServiceOn = 1;
    const statusServiceOff = 0;

    // Salon Status
    const statusSalonSignUpOnly = -1;
    const statusSalonPending = 0;
    const statusSalonActive = 1;
    const statusSalonBanned = 2;

    // Device Type
    const deviceAndroid = 1;
    const deviceIOS = 2;

    // Payment Gateways
    const stripe = 1;
    const addedByAdmin = 2;
    const razorPay = 3;
    const payStack = 4;
    const payPal = 5;
    const flutterWave = 6;
    const sslCommerze = 7;


    // Withdrawals Status
    const statusWithdrawalPending = 0;
    const statusWithdrawalCompleted = 1;
    const statusWithdrawalRejected = 2;
}
