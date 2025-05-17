<?php

use App\Http\Controllers\BookingsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PagesController;

use App\Http\Controllers\SalonController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/linkstorage', function () {
    Artisan::call('storage:link');
});

Route::get('/', [LoginController::class, 'login'])->name('/');
Route::post('login', [LoginController::class, 'checklogin'])->middleware(['checkLogin'])->name('login');
Route::get('index', [SettingsController::class, 'index'])->middleware(['checkLogin'])->name('index');
Route::get('logout', [LoginController::class, 'logout'])->middleware(['checkLogin'])->name('logout');

// Users
Route::get('users', [UsersController::class, 'users'])->middleware(['checkLogin'])->name('users');
Route::post('fetchUsersList', [UsersController::class, 'fetchUsersList'])->middleware(['checkLogin'])->name('fetchUsersList');
Route::get('blockUserFromAdmin/{id}', [UsersController::class, 'blockUserFromAdmin'])->middleware(['checkLogin'])->name('blockUserFromAdmin');
Route::get('unblockUserFromAdmin/{id}', [UsersController::class, 'unblockUserFromAdmin'])->middleware(['checkLogin'])->name('unblockUserFromAdmin');

// View User
Route::get('viewUserProfile/{id}', [UsersController::class, 'viewUserProfile'])->middleware(['checkLogin'])->name('viewUserProfile');
Route::post('fetchUserBookingsList', [UsersController::class, 'fetchUserBookingsList'])->middleware(['checkLogin'])->name('fetchUserBookingsList');
Route::post('fetchUserWalletStatementList', [UsersController::class, 'fetchUserWalletStatementList'])->middleware(['checkLogin'])->name('fetchUserWalletStatementList');
Route::post('fetchUserWithdrawRequestsList', [UsersController::class, 'fetchUserWithdrawRequestsList'])->middleware(['checkLogin'])->name('fetchUserWithdrawRequestsList');
Route::post('fetchUserWalletRechargeLogsList', [UsersController::class, 'fetchUserWalletRechargeLogsList'])->middleware(['checkLogin'])->name('fetchUserWalletRechargeLogsList');
Route::post('rechargeWalletFromAdmin', [UsersController::class, 'addMoneyToUserWallet'])->middleware(['checkLogin'])->name('rechargeWalletFromAdmin');

// Salons
Route::get('salons', [SalonController::class, 'salons'])->middleware(['checkLogin'])->name('salons');
Route::post('fetchActiveSalonList', [SalonController::class, 'fetchActiveSalonList'])->middleware(['checkLogin'])->name('fetchActiveSalonList');
Route::get('banSalon/{id}', [SalonController::class, 'banSalon'])->middleware(['checkLogin'])->name('banSalon');
Route::get('activateSalon/{id}', [SalonController::class, 'activateSalon'])->middleware(['checkLogin'])->name('activateSalon');
Route::post('fetchBannedSalonList', [SalonController::class, 'fetchBannedSalonList'])->middleware(['checkLogin'])->name('fetchBannedSalonList');
Route::post('fetchPendingSalonList', [SalonController::class, 'fetchPendingSalonList'])->middleware(['checkLogin'])->name('fetchPendingSalonList');
Route::post('fetchSignUpOnlySalonList', [SalonController::class, 'fetchSignUpOnlySalonList'])->middleware(['checkLogin'])->name('fetchSignUpOnlySalonList');
Route::get('changeSalonTopRatedStatus/{id}/{status}', [SalonController::class, 'changeSalonTopRatedStatus'])->middleware(['checkLogin'])->name('changeSalonTopRatedStatus');

// View Salon
Route::get('viewSalonProfile/{id}', [SalonController::class, 'viewSalonProfile'])->middleware(['checkLogin'])->name('viewSalonProfile');
Route::post('updateSalonDetails_Admin', [SalonController::class, 'updateSalonDetails_Admin'])->middleware(['checkLogin'])->name('updateSalonDetails_Admin');
Route::post('fetchSalonServicesList', [ServiceController::class, 'fetchSalonServicesList'])->middleware(['checkLogin'])->name('fetchSalonServicesList');
Route::get('changeServiceStatus/{id}/{status}', [ServiceController::class, 'changeServiceStatus_Admin'])->middleware(['checkLogin'])->name('changeServiceStatus');
Route::get('deleteService/{id}', [ServiceController::class, 'deleteService_Admin'])->middleware(['checkLogin'])->name('deleteService');
Route::get('deleteSalonImage/{id}', [SalonController::class, 'deleteSalonImage'])->middleware(['checkLogin'])->name('deleteSalonImage');
Route::post('addImagesToSalon', [SalonController::class, 'addImagesToSalon'])->middleware(['checkLogin'])->name('addImagesToSalon');
Route::post('fetchSalonBookingsList', [BookingsController::class, 'fetchSalonBookingsList'])->middleware(['checkLogin'])->name('fetchSalonBookingsList');
Route::post('fetchSalonWalletStatementList', [BookingsController::class, 'fetchSalonWalletStatementList'])->middleware(['checkLogin'])->name('fetchSalonWalletStatementList');
Route::post('fetchSalonPayoutRequestsList', [SalonController::class, 'fetchSalonPayoutRequestsList'])->middleware(['checkLogin'])->name('fetchSalonPayoutRequestsList');
Route::post('fetchSalonGalleryList', [SalonController::class, 'fetchSalonGalleryList'])->middleware(['checkLogin'])->name('fetchSalonGalleryList');
Route::post('fetchSalonStaffList', [SalonController::class, 'fetchSalonStaffList'])->middleware(['checkLogin'])->name('fetchSalonStaffList');
Route::get('deleteStaffItem/{id}', [SalonController::class, 'deleteStaffItem'])->middleware(['checkLogin'])->name('deleteStaffItem');
Route::get('changeStaffStatus/{id}/{status}', [ServiceController::class, 'changeStaffStatus_Admin'])->middleware(['checkLogin'])->name('changeStaffStatus');
Route::get('deleteGalleryItem/{id}', [SalonController::class, 'deleteGalleryItem'])->middleware(['checkLogin'])->name('deleteGalleryItem');
Route::post('fetchSalonReviewsList', [SalonController::class, 'fetchSalonReviewsList'])->middleware(['checkLogin'])->name('fetchSalonReviewsList');
Route::get('deleteReview/{id}', [SalonController::class, 'deleteReview'])->middleware(['checkLogin'])->name('deleteReview');
Route::post('fetchSalonAwardsList', [SalonController::class, 'fetchSalonAwardsList'])->middleware(['checkLogin'])->name('fetchSalonAwardsList');
Route::get('deleteAward/{id}', [SalonController::class, 'deleteAward'])->middleware(['checkLogin'])->name('deleteAward');
Route::post('fetchSalonEarningsList', [SalonController::class, 'fetchSalonEarningsList'])->middleware(['checkLogin'])->name('fetchSalonEarningsList');

// Staff
Route::get('staff', [SalonController::class, 'staff'])->middleware(['checkLogin'])->name('staff');
Route::post('fetchStaffList', [SalonController::class, 'fetchStaffList'])->middleware(['checkLogin'])->name('fetchStaffList');

// View Staff
Route::get('viewStaff/{id}', [SalonController::class, 'viewStaff'])->middleware(['checkLogin'])->name('viewStaff');
Route::post('fetchStaffBookingList', [BookingsController::class, 'fetchStaffBookingList'])->middleware(['checkLogin'])->name('fetchStaffBookingList');
Route::post('editStaff_Admin', [SalonController::class, 'editStaff_Admin'])->middleware(['checkLogin'])->name('editStaff_Admin');


// View Service
Route::get('viewService/{id}', [ServiceController::class, 'viewService'])->middleware(['checkLogin'])->name('viewService');
Route::post('updateService_Admin', [ServiceController::class, 'updateService_Admin'])->middleware(['checkLogin'])->name('updateService_Admin');
Route::get('deleteServiceImage/{id}', [ServiceController::class, 'deleteServiceImage'])->middleware(['checkLogin'])->name('deleteServiceImage');

// Service
Route::get('services', [ServiceController::class, 'services'])->middleware(['checkLogin'])->name('services');
Route::post('fetchAllServicesList', [ServiceController::class, 'fetchAllServicesList'])->middleware(['checkLogin'])->name('fetchAllServicesList');

// Bookings
Route::get('bookings', [BookingsController::class, 'bookings'])->middleware(['checkLogin'])->name('bookings');
Route::post('fetchAllBookingsList', [BookingsController::class, 'fetchAllBookingsList'])->middleware(['checkLogin'])->name('fetchAllBookingsList');
Route::post('fetchPendingBookingsList', [BookingsController::class, 'fetchPendingBookingsList'])->middleware(['checkLogin'])->name('fetchPendingBookingsList');
Route::get('viewBookingDetails/{id}', [BookingsController::class, 'viewBookingDetails'])->middleware(['checkLogin'])->name('viewBookingDetails');
Route::post('fetchAcceptedBookingsList', [BookingsController::class, 'fetchAcceptedBookingsList'])->middleware(['checkLogin'])->name('fetchAcceptedBookingsList');
Route::post('fetchCompletedBookingsList', [BookingsController::class, 'fetchCompletedBookingsList'])->middleware(['checkLogin'])->name('fetchCompletedBookingsList');
Route::post('fetchCancelledBookingsList', [BookingsController::class, 'fetchCancelledBookingsList'])->middleware(['checkLogin'])->name('fetchCancelledBookingsList');
Route::post('fetchDeclinedBookingsList', [BookingsController::class, 'fetchDeclinedBookingsList'])->middleware(['checkLogin'])->name('fetchDeclinedBookingsList');

// Coupons
Route::get('coupons', [SettingsController::class, 'coupons'])->middleware(['checkLogin'])->name('coupons');
Route::post('fetchAllCouponsList', [SettingsController::class, 'fetchAllCouponsList'])->middleware(['checkLogin'])->name('fetchAllCouponsList');
Route::post('addCouponItem', [SettingsController::class, 'addCouponItem'])->middleware(['checkLogin'])->name('addCouponItem');
Route::post('editCouponItem', [SettingsController::class, 'editCouponItem'])->middleware(['checkLogin'])->name('editCouponItem');
Route::get('deleteCoupon/{id}', [SettingsController::class, 'deleteCoupon'])->middleware(['checkLogin'])->name('deleteCoupon');

// Reviews
Route::get('reviews', [SettingsController::class, 'reviews'])->middleware(['checkLogin'])->name('reviews');
Route::post('fetchAllReviewsList', [SettingsController::class, 'fetchAllReviewsList'])->middleware(['checkLogin'])->name('fetchAllReviewsList');

// Faqs
Route::get('faqs', [SettingsController::class, 'faqs'])->middleware(['checkLogin'])->name('faqs');
Route::post('fetchFaqCatsList', [SettingsController::class, 'fetchFaqCatsList'])->middleware(['checkLogin'])->name('fetchFaqCatsList');
Route::post('addFaqCategory', [SettingsController::class, 'addFaqCategory'])->middleware(['checkLogin'])->name('addFaqCategory');
Route::post('editFaqCategory', [SettingsController::class, 'editFaqCategory'])->middleware(['checkLogin'])->name('editFaqCategory');
Route::get('deleteFaqCat/{id}', [SettingsController::class, 'deleteFaqCat'])->middleware(['checkLogin'])->name('deleteFaqCat');
Route::post('addFaq', [SettingsController::class, 'addFaq'])->middleware(['checkLogin'])->name('addFaq');
Route::post('fetchFaqList', [SettingsController::class, 'fetchFaqList'])->middleware(['checkLogin'])->name('fetchFaqList');
Route::get('deleteFaq/{id}', [SettingsController::class, 'deleteFaq'])->middleware(['checkLogin'])->name('deleteFaq');
Route::get('getFaqCats', [SettingsController::class, 'getFaqCats'])->middleware(['checkLogin'])->name('getFaqCats');
Route::post('editFaq', [SettingsController::class, 'editFaq'])->middleware(['checkLogin'])->name('editFaq');

// Platform Earning History
Route::get('platformEarnings', [SettingsController::class, 'platformEarnings'])->middleware(['checkLogin'])->name('platformEarnings');
Route::post('fetchPlatformEarningsList', [SettingsController::class, 'fetchPlatformEarningsList'])->middleware(['checkLogin'])->name('fetchPlatformEarningsList');
Route::get('deletePlatformEarningItem/{id}', [SettingsController::class, 'deletePlatformEarningItem'])->middleware(['checkLogin'])->name('deletePlatformEarningItem');

// Wallet recharge (user)
Route::get('userWalletRecharge', [SettingsController::class, 'userWalletRecharge'])->middleware(['checkLogin'])->name('userWalletRecharge');
Route::post('fetchWalletRechargeList', [SettingsController::class, 'fetchWalletRechargeList'])->middleware(['checkLogin'])->name('fetchWalletRechargeList');

// Salon Categories
Route::get('salonCategories', [SettingsController::class, 'salonCategories'])->middleware(['checkLogin'])->name('salonCategories');
Route::post('fetchSalonCategoriesList', [SettingsController::class, 'fetchSalonCategoriesList'])->middleware(['checkLogin'])->name('fetchSalonCategoriesList');
Route::post('addSalonCat', [SettingsController::class, 'addSalonCat'])->middleware(['checkLogin'])->name('addSalonCat');
Route::get('deleteSalonCat/{id}', [SettingsController::class, 'deleteSalonCat'])->middleware(['checkLogin'])->name('deleteSalonCat');
Route::post('editSalonCat', [SettingsController::class, 'editSalonCat'])->middleware(['checkLogin'])->name('editSalonCat');

// Banners
Route::get('banners', [SettingsController::class, 'banners'])->middleware(['checkLogin'])->name('banners');
Route::post('fetchBannersList', [SettingsController::class, 'fetchBannersList'])->middleware(['checkLogin'])->name('fetchBannersList');
Route::post('addBanner', [SettingsController::class, 'addBanner'])->middleware(['checkLogin'])->name('addBanner');
Route::get('deleteBanner/{id}', [SettingsController::class, 'deleteBanner'])->middleware(['checkLogin'])->name('deleteBanner');

// Notifications
Route::get('notifications', [SettingsController::class, 'notifications'])->middleware(['checkLogin'])->name('notifications');
Route::post('fetchUserNotificationList', [SettingsController::class, 'fetchUserNotificationList'])->middleware(['checkLogin'])->name('fetchUserNotificationList');
Route::get('deleteUserNotification/{id}', [SettingsController::class, 'deleteUserNotification'])->middleware(['checkLogin'])->name('deleteUserNotification');
Route::post('addUserNotification', [SettingsController::class, 'addUserNotification'])->middleware(['checkLogin'])->name('addUserNotification');
Route::post('editUserNotification', [SettingsController::class, 'editUserNotification'])->middleware(['checkLogin'])->name('editUserNotification');
Route::post('addSalonNotification', [SettingsController::class, 'addSalonNotification'])->middleware(['checkLogin'])->name('addSalonNotification');
Route::post('fetchSalonNotificationList', [SettingsController::class, 'fetchSalonNotificationList'])->middleware(['checkLogin'])->name('fetchSalonNotificationList');
Route::get('deleteSalonNotification/{id}', [SettingsController::class, 'deleteSalonNotification'])->middleware(['checkLogin'])->name('deleteSalonNotification');
Route::post('editSalonNotification', [SettingsController::class, 'editSalonNotification'])->middleware(['checkLogin'])->name('editSalonNotification');

// User Withdrawals
Route::get('userWithdraws', [UsersController::class, 'userWithdraws'])->middleware(['checkLogin'])->name('userWithdraws');
Route::post('fetchUserPendingWithdrawalsList', [UsersController::class, 'fetchUserPendingWithdrawalsList'])->middleware(['checkLogin'])->name('fetchUserPendingWithdrawalsList');
Route::post('fetchUserCompletedWithdrawalsList', [UsersController::class, 'fetchUserCompletedWithdrawalsList'])->middleware(['checkLogin'])->name('fetchUserCompletedWithdrawalsList');
Route::post('fetchUserRejectedWithdrawalsList', [UsersController::class, 'fetchUserRejectedWithdrawalsList'])->middleware(['checkLogin'])->name('fetchUserRejectedWithdrawalsList');
Route::post('completeUserWithdrawal', [UsersController::class, 'completeUserWithdrawal'])->middleware(['checkLogin'])->name('completeUserWithdrawal');
Route::post('rejectUserWithdrawal', [UsersController::class, 'rejectUserWithdrawal'])->middleware(['checkLogin'])->name('rejectUserWithdrawal');

// Salon Withdrawal
Route::get('salonWithdraws', [SalonController::class, 'salonWithdraws'])->middleware(['checkLogin'])->name('salonWithdraws');
Route::post('fetchSalonPendingWithdrawalsList', [SalonController::class, 'fetchSalonPendingWithdrawalsList'])->middleware(['checkLogin'])->name('fetchSalonPendingWithdrawalsList');
Route::post('fetchSalonCompletedWithdrawalsList', [SalonController::class, 'fetchSalonCompletedWithdrawalsList'])->middleware(['checkLogin'])->name('fetchSalonCompletedWithdrawalsList');
Route::post('fetchSalonRejectedWithdrawalsList', [SalonController::class, 'fetchSalonRejectedWithdrawalsList'])->middleware(['checkLogin'])->name('fetchSalonRejectedWithdrawalsList');
Route::post('completeSalonWithdrawal', [SalonController::class, 'completeSalonWithdrawal'])->middleware(['checkLogin'])->name('completeSalonWithdrawal');
Route::post('rejectSalonWithdrawal', [SalonController::class, 'rejectSalonWithdrawal'])->middleware(['checkLogin'])->name('rejectSalonWithdrawal');

// Settings
Route::get('settings', [SettingsController::class, 'settings'])->middleware(['checkLogin'])->name('settings');
Route::post('updateGlobalSettings', [SettingsController::class, 'updateGlobalSettings'])->middleware(['checkLogin'])->name('updateGlobalSettings');
Route::get('changeTaxStatus/{status}', [SettingsController::class, 'changeTaxStatus'])->middleware(['checkLogin'])->name('changeTaxStatus');
Route::post('changePassword', [SettingsController::class, 'changePassword'])->middleware(['checkLogin'])->name('changePassword');
Route::post('updatePaymentSettings', [SettingsController::class, 'updatePaymentSettings'])->middleware(['checkLogin'])->name('updatePaymentSettings');

Route::post('fetchAllTaxList', [SettingsController::class, 'fetchAllTaxList'])->middleware(['checkLogin'])->name('fetchAllTaxList');
Route::post('addTaxItem', [SettingsController::class, 'addTaxItem'])->middleware(['checkLogin'])->name('addTaxItem');
Route::post('editTaxItem', [SettingsController::class, 'editTaxItem'])->middleware(['checkLogin'])->name('editTaxItem');
Route::get('deleteTaxItem/{id}', [SettingsController::class, 'deleteTaxItem'])->middleware(['checkLogin'])->name('deleteTaxItem');
Route::get('changeTaxStatus/{id}/{value}', [SettingsController::class, 'changeTaxStatus'])->middleware(['checkLogin'])->name('changeTaxStatus');




// Pages Routes
Route::get('viewPrivacy', [PagesController::class, 'viewPrivacy'])->middleware(['checkLogin'])->name('viewPrivacy');
Route::post('updatePrivacy', [PagesController::class, 'updatePrivacy'])->middleware(['checkLogin'])->name('updatePrivacy');
Route::get('viewTerms', [PagesController::class, 'viewTerms'])->middleware(['checkLogin'])->name('viewTerms');
Route::post('updateTerms', [PagesController::class, 'updateTerms'])->middleware(['checkLogin'])->name('updateTerms');
Route::get('privacypolicy', [PagesController::class, 'privacypolicy'])->name('privacypolicy');
Route::get('termsOfUse', [PagesController::class, 'termsOfUse'])->name('termsOfUse');
