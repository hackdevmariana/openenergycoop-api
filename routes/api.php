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
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\HeroController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\TextContentController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\SeoMetaDataController;
use App\Http\Controllers\Api\V1\PageComponentController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\FaqTopicController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\SocialLinkController;
use App\Http\Controllers\Api\V1\CollaboratorController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\NewsletterSubscriptionController;
use App\Http\Controllers\Api\V1\FormSubmissionController;
use App\Http\Controllers\Api\V1\OrganizationFeatureController;
use App\Http\Controllers\Api\V1\TeamMembershipController;
use App\Http\Controllers\Api\V1\TeamChallengeProgressController;

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
    
    // Rutas para Comments
    Route::post('comments/{comment}/like', [CommentController::class, 'like']);
    Route::post('comments/{comment}/approve', [CommentController::class, 'approve']);
    Route::post('comments/{comment}/reject', [CommentController::class, 'reject']);
    Route::get('comments/thread/{comment}', [CommentController::class, 'thread']);
    Route::apiResource('comments', CommentController::class);
    
    // Rutas para Menus
    Route::get('menus/hierarchy', [MenuController::class, 'hierarchy']);
    Route::get('menus/by-group/{group}', [MenuController::class, 'byGroup']);
    Route::apiResource('menus', MenuController::class);
    
    // Rutas para Organizations
    Route::get('organizations/{organization}/stats', [OrganizationController::class, 'stats']);
    Route::get('organizations/{organization}/features', [OrganizationController::class, 'features']);
    Route::apiResource('organizations', OrganizationController::class);
    
    // Rutas para Users
    Route::get('users/me', [UserController::class, 'me']);
    Route::put('users/me', [UserController::class, 'updateMe']);
    Route::apiResource('users', UserController::class);
    
    // Rutas para Heroes (solo escritura)
    Route::post('heroes', [HeroController::class, 'store']);
    Route::put('heroes/{hero}', [HeroController::class, 'update']);
    Route::patch('heroes/{hero}', [HeroController::class, 'update']);
    Route::delete('heroes/{hero}', [HeroController::class, 'destroy']);
    Route::post('heroes/{hero}/duplicate', [HeroController::class, 'duplicate']);
    
    // Rutas para Banners (CMS)
    Route::get('banners/active', [BannerController::class, 'active']);
    Route::get('banners/by-position/{position}', [BannerController::class, 'byPosition']);
    Route::apiResource('banners', BannerController::class);
    
    // Rutas para Text Contents (CMS)
    Route::apiResource('text-contents', TextContentController::class);
    
    // Rutas para Documents
    Route::get('documents/most-downloaded', [DocumentController::class, 'mostDownloaded'])->name('documents.most-downloaded');
    Route::get('documents/recent', [DocumentController::class, 'recent'])->name('documents.recent');
    Route::get('documents/popular', [DocumentController::class, 'popular'])->name('documents.popular');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::apiResource('documents', DocumentController::class);
    
    // Rutas para SEO Metadata
    Route::get('seo-metadata/for-model/{type}/{id}', [SeoMetaDataController::class, 'forModel']);
    Route::apiResource('seo-metadata', SeoMetaDataController::class);
    
    // Rutas para Page Components (CMS)
    Route::get('page-components/for-page/{pageId}', [PageComponentController::class, 'forPage']);
    Route::post('page-components/{pageComponent}/reorder', [PageComponentController::class, 'reorder']);
    Route::apiResource('page-components', PageComponentController::class);
    
    // Rutas para FAQs (solo escritura)
    Route::post('faqs', [FaqController::class, 'store']);
    Route::put('faqs/{faq}', [FaqController::class, 'update']);
    Route::patch('faqs/{faq}', [FaqController::class, 'update']);
    Route::delete('faqs/{faq}', [FaqController::class, 'destroy']);
    
    // Rutas para FAQ Topics (solo escritura)
    Route::post('faq-topics', [FaqTopicController::class, 'store']);
    Route::put('faq-topics/{faqTopic}', [FaqTopicController::class, 'update']);
    Route::patch('faq-topics/{faqTopic}', [FaqTopicController::class, 'update']);
    Route::delete('faq-topics/{faqTopic}', [FaqTopicController::class, 'destroy']);
    
    // Rutas para Contacts
    Route::get('contacts/by-type/{type}', [ContactController::class, 'byType']);
    Route::apiResource('contacts', ContactController::class);
    
    // Rutas para Social Links (solo escritura)
    Route::post('social-links', [SocialLinkController::class, 'store']);
    Route::put('social-links/{socialLink}', [SocialLinkController::class, 'update']);
    Route::patch('social-links/{socialLink}', [SocialLinkController::class, 'update']);
    Route::delete('social-links/{socialLink}', [SocialLinkController::class, 'destroy']);
    
    // Rutas para Collaborators
    Route::get('collaborators/active', [CollaboratorController::class, 'active']);
    Route::get('collaborators/by-type/{type}', [CollaboratorController::class, 'byType']);
    Route::apiResource('collaborators', CollaboratorController::class);
    
    // Rutas para Messages (gestión interna)
    Route::get('messages', [MessageController::class, 'index']); // Listado autenticado
    Route::get('messages/pending', [MessageController::class, 'pending']);
    Route::get('messages/unread', [MessageController::class, 'unread']);
    Route::get('messages/assigned', [MessageController::class, 'assigned']);
    Route::get('messages/stats', [MessageController::class, 'stats']);
    Route::get('messages/by-email/{email}', [MessageController::class, 'byEmail']);
    Route::get('messages/{message}', [MessageController::class, 'show']); // Show autenticado
    Route::post('messages/{message}/mark-as-read', [MessageController::class, 'markAsRead']);
    Route::post('messages/{message}/mark-as-replied', [MessageController::class, 'markAsReplied']);
    Route::post('messages/{message}/mark-as-spam', [MessageController::class, 'markAsSpam']);
    Route::post('messages/{message}/archive', [MessageController::class, 'archive']);
    Route::post('messages/{message}/assign', [MessageController::class, 'assign']);
    Route::delete('messages/{message}/assign', [MessageController::class, 'unassign']);
    Route::put('messages/{message}', [MessageController::class, 'update']);
    Route::delete('messages/{message}', [MessageController::class, 'destroy']);
    
    // Rutas para Newsletter Subscriptions (gestión interna)
    Route::get('newsletter-subscriptions', [NewsletterSubscriptionController::class, 'index']);
    Route::get('newsletter-subscriptions/stats', [NewsletterSubscriptionController::class, 'stats']);
    Route::get('newsletter-subscriptions/export', [NewsletterSubscriptionController::class, 'export']);
    Route::get('newsletter-subscriptions/{newsletterSubscription}', [NewsletterSubscriptionController::class, 'show']);
    Route::put('newsletter-subscriptions/{newsletterSubscription}', [NewsletterSubscriptionController::class, 'update']);
    Route::delete('newsletter-subscriptions/{newsletterSubscription}', [NewsletterSubscriptionController::class, 'destroy']);
    Route::post('newsletter-subscriptions/{newsletterSubscription}/mark-bounced', [NewsletterSubscriptionController::class, 'markBounced']);
    Route::post('newsletter-subscriptions/{newsletterSubscription}/mark-complaint', [NewsletterSubscriptionController::class, 'markComplaint']);
    Route::post('newsletter-subscriptions/{newsletterSubscription}/record-email', [NewsletterSubscriptionController::class, 'recordEmail']);
    
    // Rutas para Form Submissions
    Route::apiResource('form-submissions', FormSubmissionController::class);
    
    // Rutas para Organization Features
    Route::apiResource('organization-features', OrganizationFeatureController::class);
    
    // Rutas para Team Memberships
    Route::post('team-memberships/leave', [TeamMembershipController::class, 'leave']);
    Route::apiResource('team-memberships', TeamMembershipController::class);
    
    // Rutas para Team Challenge Progress
    Route::get('team-challenge-progress/leaderboard/{challengeId}', [TeamChallengeProgressController::class, 'leaderboard']);
    Route::post('team-challenge-progress/{teamChallengeProgress}/update-progress', [TeamChallengeProgressController::class, 'updateProgress']);
    Route::apiResource('team-challenge-progress', TeamChallengeProgressController::class);
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
    
    // Rutas públicas para Comments (solo lectura)
    Route::get('comments', [CommentController::class, 'index']);
    Route::get('comments/{comment}', [CommentController::class, 'show']);
    Route::get('comments/thread/{comment}', [CommentController::class, 'thread']);
    Route::post('comments/{comment}/like', [CommentController::class, 'like']);
    
    // Rutas públicas para Menus (solo lectura)
    Route::get('menus', [MenuController::class, 'index']);
    Route::get('menus/hierarchy', [MenuController::class, 'hierarchy']);
    Route::get('menus/by-group/{group}', [MenuController::class, 'byGroup']);
    Route::get('menus/{menu}', [MenuController::class, 'show']);
    
    // Rutas públicas para Organizations (solo lectura)
    Route::get('organizations', [OrganizationController::class, 'index']);
    Route::get('organizations/{organization}', [OrganizationController::class, 'show']);
    
    // Rutas públicas para Heroes (solo lectura)
    Route::get('heroes', [HeroController::class, 'index']);
    Route::get('heroes/slideshow', [HeroController::class, 'slideshow']);
    Route::get('heroes/active', [HeroController::class, 'active']);
    Route::get('heroes/{hero}', [HeroController::class, 'show']);
    
    // Rutas públicas para Banners (solo lectura)
    Route::get('banners', [BannerController::class, 'index']);
    Route::get('banners/active', [BannerController::class, 'active']);
    Route::get('banners/by-position/{position}', [BannerController::class, 'byPosition']);
    Route::get('banners/{banner}', [BannerController::class, 'show']);
    
    // Rutas públicas para Text Contents (solo lectura)
    Route::get('text-contents', [TextContentController::class, 'index']);
    Route::get('text-contents/{textContent}', [TextContentController::class, 'show']);
    
    // Rutas públicas para Documents (solo lectura)
    Route::get('documents', [DocumentController::class, 'index']);
    Route::get('documents/most-downloaded', [DocumentController::class, 'mostDownloaded']);
    Route::get('documents/recent', [DocumentController::class, 'recent']);
    Route::get('documents/popular', [DocumentController::class, 'popular']);
    Route::get('documents/{document}', [DocumentController::class, 'show']);
    Route::get('documents/{document}/download', [DocumentController::class, 'download']);
    
    // Rutas públicas para Page Components (solo lectura)
    Route::get('page-components/for-page/{pageId}', [PageComponentController::class, 'forPage']);
    
    // Rutas públicas para FAQs (solo lectura)
    Route::get('faqs', [FaqController::class, 'index']);
    Route::get('faqs/featured', [FaqController::class, 'featured']);
    Route::get('faqs/search', [FaqController::class, 'search']);
    Route::get('faqs/{faq}', [FaqController::class, 'show']);
    
    // Rutas públicas para FAQ Topics (solo lectura)
    Route::get('faq-topics', [FaqTopicController::class, 'index']);
    Route::get('faq-topics/{faqTopic}', [FaqTopicController::class, 'show']);
    Route::get('faq-topics/{faqTopic}/faqs', [FaqTopicController::class, 'faqs']);
    
    // Rutas públicas para Contacts (solo lectura)
    Route::get('contacts', [ContactController::class, 'index']);
    Route::get('contacts/by-type/{type}', [ContactController::class, 'byType']);
    Route::get('contacts/{contact}', [ContactController::class, 'show']);
    
    // Rutas públicas para Social Links (solo lectura)
    Route::get('social-links', [SocialLinkController::class, 'index']);
    Route::get('social-links/by-platform/{platform}', [SocialLinkController::class, 'byPlatform']);
    Route::get('social-links/popular', [SocialLinkController::class, 'popular']);
    Route::get('social-links/{socialLink}', [SocialLinkController::class, 'show']);

    // Rutas públicas para Messages (solo formulario de contacto)
    Route::post('messages', [MessageController::class, 'store']); // Público - envío de formulario
    
    // Rutas públicas para Collaborators (solo lectura)
    Route::get('collaborators', [CollaboratorController::class, 'index']);
    Route::get('collaborators/active', [CollaboratorController::class, 'active']);
    Route::get('collaborators/by-type/{type}', [CollaboratorController::class, 'byType']);
    Route::get('collaborators/{collaborator}', [CollaboratorController::class, 'show']);
    
    // Rutas públicas para Newsletter (suscripción pública)
    Route::post('newsletter-subscriptions', [NewsletterSubscriptionController::class, 'store']);
    Route::post('newsletter/confirm', [NewsletterSubscriptionController::class, 'confirm']);
    Route::post('newsletter/unsubscribe', [NewsletterSubscriptionController::class, 'unsubscribe']);
    Route::post('newsletter/resubscribe', [NewsletterSubscriptionController::class, 'resubscribe']);
    
    // Rutas públicas para Form Submissions (envío público)
    Route::post('form-submissions', [FormSubmissionController::class, 'store']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
