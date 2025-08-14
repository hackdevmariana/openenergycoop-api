<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AppSettingController;
use App\Http\Controllers\Api\V1\CustomerProfileController;
use App\Http\Controllers\Api\V1\CustomerProfileContactInfoController;
use App\Http\Controllers\Api\V1\LegalDocumentController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\SubscriptionRequestController;
use App\Http\Controllers\Api\V1\OrganizationRoleController;
use App\Http\Controllers\Api\V1\UserOrganizationRoleController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('app-settings', AppSettingController::class)->only(['index', 'show']);
    Route::apiResource('customer-profiles', CustomerProfileController::class);
    Route::apiResource('customer-profile-contact-infos', CustomerProfileContactInfoController::class);
    Route::apiResource('legal-documents', LegalDocumentController::class);
    Route::post('legal-documents/{id}/verify', [LegalDocumentController::class, 'verify']);
    
    // Nuevas rutas para las entidades del sistema
    Route::apiResource('companies', CompanyController::class);
    
    Route::apiResource('subscription-requests', SubscriptionRequestController::class);
    Route::post('subscription-requests/{id}/approve', [SubscriptionRequestController::class, 'approve']);
    Route::post('subscription-requests/{id}/reject', [SubscriptionRequestController::class, 'reject']);
    Route::post('subscription-requests/{id}/review', [SubscriptionRequestController::class, 'review']);
    
    Route::apiResource('organization-roles', OrganizationRoleController::class);
    Route::apiResource('user-organization-roles', UserOrganizationRoleController::class);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
