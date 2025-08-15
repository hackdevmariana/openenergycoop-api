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
use App\Http\Controllers\Api\V1\AchievementController;
use App\Http\Controllers\Api\V1\InvitationTokenController;
use App\Http\Controllers\Api\V1\UserProfileController;
use App\Http\Controllers\Api\V1\UserAchievementController;

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
    
    // Rutas para Achievements
    Route::get('achievements/types', [AchievementController::class, 'types']);
    Route::get('achievements/leaderboard', [AchievementController::class, 'leaderboard']);
    Route::apiResource('achievements', AchievementController::class)->only(['index', 'show']);
    
    // Rutas para Invitation Tokens
    Route::apiResource('invitation-tokens', InvitationTokenController::class)->except(['update', 'destroy']);
    Route::post('invitation-tokens/{invitationToken}/revoke', [InvitationTokenController::class, 'revoke']);
    
    // Rutas para User Profiles
    Route::get('user-profiles/me', [UserProfileController::class, 'me']);
    Route::put('user-profiles/me', [UserProfileController::class, 'updateMe']);
    Route::get('user-profiles/rankings/organization/{organizationId}', [UserProfileController::class, 'organizationRanking']);
    Route::get('user-profiles/rankings/municipality/{municipalityId}', [UserProfileController::class, 'municipalityRanking']);
    Route::get('user-profiles/statistics', [UserProfileController::class, 'statistics']);
    Route::apiResource('user-profiles', UserProfileController::class)->only(['index', 'show']);
    
    // Rutas para User Achievements
    Route::get('user-achievements/me', [UserAchievementController::class, 'me']);
    Route::get('user-achievements/me/recent', [UserAchievementController::class, 'recentMe']);
    Route::get('user-achievements/me/statistics', [UserAchievementController::class, 'statisticsMe']);
    Route::get('user-achievements/leaderboard', [UserAchievementController::class, 'leaderboard']);
    Route::post('user-achievements/{userAchievement}/grant-reward', [UserAchievementController::class, 'grantReward']);
    Route::apiResource('user-achievements', UserAchievementController::class)->only(['index', 'show']);
});

// Rutas públicas para validación de tokens de invitación
Route::prefix('v1')->group(function () {
    Route::get('invitation-tokens/validate/{token}', [InvitationTokenController::class, 'validateToken']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
