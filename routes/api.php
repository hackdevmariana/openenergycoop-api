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
use App\Http\Controllers\Api\V1\ChallengeController;
use App\Http\Controllers\Api\V1\InvitationTokenController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\UserProfileController;
use App\Http\Controllers\Api\V1\UserAchievementController;
use App\Http\Controllers\Api\V1\ImageController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\UserDeviceController;
use App\Http\Controllers\Api\V1\UserSettingsController;
use App\Http\Controllers\Api\V1\ConsentLogController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\PageController;

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
    
    // Rutas para Teams
    Route::get('teams/recommendations', [TeamController::class, 'recommendations']);
    Route::get('teams/my-teams', [TeamController::class, 'myTeams']);
    Route::post('teams/{team}/join', [TeamController::class, 'join']);
    Route::post('teams/{team}/leave', [TeamController::class, 'leave']);
    Route::get('teams/{team}/members', [TeamController::class, 'members']);
    Route::apiResource('teams', TeamController::class);
    
    // Rutas para Challenges
    Route::get('challenges/current', [ChallengeController::class, 'current']);
    Route::get('challenges/statistics', [ChallengeController::class, 'statistics']);
    Route::get('challenges/{challenge}/leaderboard', [ChallengeController::class, 'leaderboard']);
    Route::get('challenges/recommendations/{team}', [ChallengeController::class, 'recommendations']);
    Route::apiResource('challenges', ChallengeController::class)->only(['index', 'show']);
    
    // Rutas para Images (CMS)
    Route::get('images/featured', [ImageController::class, 'featured']);
    Route::get('images/stats', [ImageController::class, 'stats']);
    Route::post('images/{image}/download', [ImageController::class, 'download']);
    Route::apiResource('images', ImageController::class);
    
    // Rutas para Categories (CMS)
    Route::get('categories/tree', [CategoryController::class, 'tree']);
    Route::get('categories/{category}/content', [CategoryController::class, 'content']);
    Route::apiResource('categories', CategoryController::class);
    
    // Rutas para User Devices
    Route::get('user-devices/current', [UserDeviceController::class, 'current']);
    Route::post('user-devices/{userDevice}/set-current', [UserDeviceController::class, 'setCurrent']);
    Route::post('user-devices/{userDevice}/update-activity', [UserDeviceController::class, 'updateActivity']);
    Route::apiResource('user-devices', UserDeviceController::class);
    
    // Rutas para User Settings
    Route::get('user-settings', [UserSettingsController::class, 'show']);
    Route::put('user-settings', [UserSettingsController::class, 'update']);
    Route::get('user-settings/notifications', [UserSettingsController::class, 'notifications']);
    Route::put('user-settings/notifications', [UserSettingsController::class, 'updateNotifications']);
    Route::get('user-settings/privacy', [UserSettingsController::class, 'privacy']);
    Route::put('user-settings/privacy', [UserSettingsController::class, 'updatePrivacy']);
    Route::post('user-settings/reset', [UserSettingsController::class, 'reset']);
    
    // Rutas para Consent Logs
    Route::get('consent-logs/current-status', [ConsentLogController::class, 'currentStatus']);
    Route::get('consent-logs/history/{type}', [ConsentLogController::class, 'history']);
    Route::get('consent-logs/gdpr-report', [ConsentLogController::class, 'gdprReport']);
    Route::post('consent-logs/{consentLog}/revoke', [ConsentLogController::class, 'revoke']);
    Route::apiResource('consent-logs', ConsentLogController::class)->except(['update', 'destroy']);
    
    // Rutas para Articles (CMS)
    Route::get('articles/featured', [ArticleController::class, 'featured']);
    Route::get('articles/recent', [ArticleController::class, 'recent']);
    Route::get('articles/popular', [ArticleController::class, 'popular']);
    Route::apiResource('articles', ArticleController::class);
    
    // Rutas para Pages (CMS)
    Route::get('pages/hierarchy', [PageController::class, 'hierarchy']);
    Route::get('pages/search', [PageController::class, 'search']);
    Route::get('pages/by-route/{route}', [PageController::class, 'byRoute'])->where('route', '.*');
    Route::apiResource('pages', PageController::class);
});

// Rutas públicas
Route::prefix('v1')->group(function () {
    // Validación de tokens de invitación
    Route::get('invitation-tokens/validate/{token}', [InvitationTokenController::class, 'validateToken']);
    
    // Rutas públicas para Images (solo lectura)
    Route::get('images', [ImageController::class, 'index']);
    Route::get('images/featured', [ImageController::class, 'featured']);
    Route::get('images/stats', [ImageController::class, 'stats']);
    Route::get('images/{image}', [ImageController::class, 'show']);
    Route::post('images/{image}/download', [ImageController::class, 'download']);
    
    // Rutas públicas para Categories (solo lectura)
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/tree', [CategoryController::class, 'tree']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::get('categories/{category}/content', [CategoryController::class, 'content']);
    
    // Rutas públicas para Articles (solo lectura)
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/featured', [ArticleController::class, 'featured']);
    Route::get('articles/recent', [ArticleController::class, 'recent']);
    Route::get('articles/popular', [ArticleController::class, 'popular']);
    Route::get('articles/{article}', [ArticleController::class, 'show']);
    
    // Rutas públicas para Pages (solo lectura)
    Route::get('pages', [PageController::class, 'index']);
    Route::get('pages/hierarchy', [PageController::class, 'hierarchy']);
    Route::get('pages/search', [PageController::class, 'search']);
    Route::get('pages/by-route/{route}', [PageController::class, 'byRoute'])->where('route', '.*');
    Route::get('pages/{page}', [PageController::class, 'show']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
