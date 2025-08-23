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
use App\Http\Controllers\Api\V1\RegionController;
use App\Http\Controllers\Api\V1\MunicipalityController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\UserAssetController;
use App\Http\Controllers\Api\V1\BalanceController;
use App\Http\Controllers\Api\V1\EnergyContractController;
use App\Http\Controllers\Api\V1\EnergyConsumptionController;
use App\Http\Controllers\Api\V1\EnergyStorageController;
use App\Http\Controllers\Api\V1\EnergyProductionController;
use App\Http\Controllers\Api\V1\CarbonCreditController;
use App\Http\Controllers\Api\V1\MarketPriceController;
use App\Http\Controllers\Api\V1\EnergyCooperativeController;
use App\Http\Controllers\Api\V1\UserSubscriptionController;
use App\Http\Controllers\Api\V1\EnergySharingController;
use App\Http\Controllers\Api\V1\EnergyReportController;
use App\Http\Controllers\Api\V1\SustainabilityMetricController;
use App\Http\Controllers\Api\V1\PerformanceIndicatorController;
use App\Http\Controllers\Api\V1\EnergyBondController;
use App\Http\Controllers\Api\V1\MaintenanceTaskController;
use App\Http\Controllers\Api\V1\BondDonationController;
use App\Http\Controllers\Api\V1\AffiliateController;
use App\Http\Controllers\Api\V1\DiscountCodeController;
use App\Http\Controllers\Api\V1\SaleOrderController;
use App\Http\Controllers\Api\V1\PreSaleOfferController;
use App\Http\Controllers\Api\V1\ProductionProjectController;
use App\Http\Controllers\Api\V1\EnergySourceController;
use App\Http\Controllers\Api\V1\EnergyInstallationController;
use App\Http\Controllers\Api\V1\ConsumptionPointController;
use App\Http\Controllers\Api\V1\EnergyMeterController;
use App\Http\Controllers\Api\V1\EnergyReadingController;
use App\Http\Controllers\Api\V1\EnergyPoolController;
use App\Http\Controllers\Api\V1\EnergyTradingOrderController;
use App\Http\Controllers\Api\V1\EnergyForecastController;
use App\Http\Controllers\Api\V1\TaxCalculationController;
use App\Http\Controllers\Api\V1\EnergyTransferController;
use App\Http\Controllers\Api\V1\AutomationRuleController;
use App\Http\Controllers\Api\V1\MaintenanceScheduleController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('app-settings', AppSettingController::class)->only(['index', 'show']);
    Route::apiResource('customer-profiles', CustomerProfileController::class);
    Route::apiResource('customer-profile-contact-infos', CustomerProfileContactInfoController::class);
    Route::get('legal-documents/{id}/versions', [LegalDocumentController::class, 'versions']);
    Route::get('legal-documents/{id}/download', [LegalDocumentController::class, 'download']);
    Route::post('legal-documents/{id}/new-version', [LegalDocumentController::class, 'newVersion']);
    Route::post('legal-documents/{id}/verify', [LegalDocumentController::class, 'verify']);
    Route::apiResource('legal-documents', LegalDocumentController::class);
    
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
    
    // Rutas para Maintenance Tasks
    Route::get('maintenance-tasks/overdue', [MaintenanceTaskController::class, 'overdueTasks']);
    Route::get('maintenance-tasks/today', [MaintenanceTaskController::class, 'todayTasks']);
    Route::get('maintenance-tasks/week', [MaintenanceTaskController::class, 'weekTasks']);
    Route::get('maintenance-tasks/statistics', [MaintenanceTaskController::class, 'statistics']);
    Route::apiResource('maintenance-tasks', MaintenanceTaskController::class);
    Route::post('maintenance-tasks/{maintenanceTask}/start', [MaintenanceTaskController::class, 'startTask']);
    Route::post('maintenance-tasks/{maintenanceTask}/complete', [MaintenanceTaskController::class, 'completeTask']);
    Route::post('maintenance-tasks/{maintenanceTask}/pause', [MaintenanceTaskController::class, 'pauseTask']);
    Route::post('maintenance-tasks/{maintenanceTask}/resume', [MaintenanceTaskController::class, 'resumeTask']);
    Route::post('maintenance-tasks/{maintenanceTask}/cancel', [MaintenanceTaskController::class, 'cancelTask']);
    Route::post('maintenance-tasks/{maintenanceTask}/reassign', [MaintenanceTaskController::class, 'reassignTask']);
    Route::post('maintenance-tasks/{maintenanceTask}/update-progress', [MaintenanceTaskController::class, 'updateProgress']);
    Route::post('maintenance-tasks/{maintenanceTask}/duplicate', [MaintenanceTaskController::class, 'duplicate']);
    
    // Rutas para Bond Donations
    Route::get('bond-donations/public', [BondDonationController::class, 'publicDonations']);
    Route::get('bond-donations/recent', [BondDonationController::class, 'recentDonations']);
    Route::get('bond-donations/top-donors', [BondDonationController::class, 'topDonors']);
    Route::get('bond-donations/statistics', [BondDonationController::class, 'statistics']);
    Route::apiResource('bond-donations', BondDonationController::class);
    Route::post('bond-donations/{bondDonation}/confirm', [BondDonationController::class, 'confirm']);
    Route::post('bond-donations/{bondDonation}/reject', [BondDonationController::class, 'reject']);
    Route::post('bond-donations/{bondDonation}/process', [BondDonationController::class, 'process']);
    Route::post('bond-donations/{bondDonation}/refund', [BondDonationController::class, 'refund']);
    Route::post('bond-donations/{bondDonation}/make-public', [BondDonationController::class, 'makePublic']);
    Route::post('bond-donations/{bondDonation}/make-private', [BondDonationController::class, 'makePrivate']);
    Route::post('bond-donations/{bondDonation}/send-thank-you', [BondDonationController::class, 'sendThankYou']);
    Route::post('bond-donations/{bondDonation}/duplicate', [BondDonationController::class, 'duplicate']);
    
    // Rutas para Affiliates
    Route::get('affiliates/active', [AffiliateController::class, 'active']);
    Route::get('affiliates/by-type/{type}', [AffiliateController::class, 'byType']);
    Route::get('affiliates/top-performers', [AffiliateController::class, 'topPerformers']);
    Route::get('affiliates/statistics', [AffiliateController::class, 'statistics']);
    Route::apiResource('affiliates', AffiliateController::class);
    Route::post('affiliates/{affiliate}/verify', [AffiliateController::class, 'verify']);
    Route::post('affiliates/{affiliate}/update-performance-rating', [AffiliateController::class, 'updatePerformanceRating']);
    Route::post('affiliates/{affiliate}/update-commission-rate', [AffiliateController::class, 'updateCommissionRate']);
    Route::post('affiliates/{affiliate}/duplicate', [AffiliateController::class, 'duplicate']);
    
    // Rutas para Discount Codes
    Route::get('discount-codes/active', [DiscountCodeController::class, 'active']);
    Route::get('discount-codes/by-type/{type}', [DiscountCodeController::class, 'byType']);
    Route::post('discount-codes/validate', [DiscountCodeController::class, 'validate']);
    Route::get('discount-codes/statistics', [DiscountCodeController::class, 'statistics']);
    Route::apiResource('discount-codes', DiscountCodeController::class);
    Route::post('discount-codes/{discountCode}/activate', [DiscountCodeController::class, 'activate']);
    Route::post('discount-codes/{discountCode}/deactivate', [DiscountCodeController::class, 'deactivate']);
    Route::post('discount-codes/{discountCode}/duplicate', [DiscountCodeController::class, 'duplicate']);
    
    // Rutas para Sale Orders
    Route::get('sale-orders/pending', [SaleOrderController::class, 'pending']);
    Route::get('sale-orders/urgent', [SaleOrderController::class, 'urgent']);
    Route::get('sale-orders/by-status/{status}', [SaleOrderController::class, 'byStatus']);
    Route::get('sale-orders/by-customer', [SaleOrderController::class, 'byCustomer']);
    Route::get('sale-orders/statistics', [SaleOrderController::class, 'statistics']);
    Route::apiResource('sale-orders', SaleOrderController::class);
    Route::post('sale-orders/{saleOrder}/update-status', [SaleOrderController::class, 'updateStatus']);
    Route::post('sale-orders/{saleOrder}/update-payment-status', [SaleOrderController::class, 'updatePaymentStatus']);
    Route::post('sale-orders/{saleOrder}/update-urgency', [SaleOrderController::class, 'updateUrgency']);
    Route::post('sale-orders/{saleOrder}/duplicate', [SaleOrderController::class, 'duplicate']);
    
    // Rutas para Pre Sale Offers
    Route::get('pre-sale-offers/active', [PreSaleOfferController::class, 'active']);
    Route::get('pre-sale-offers/featured', [PreSaleOfferController::class, 'featured']);
    Route::get('pre-sale-offers/by-type/{type}', [PreSaleOfferController::class, 'byType']);
    Route::get('pre-sale-offers/by-product', [PreSaleOfferController::class, 'byProduct']);
    Route::get('pre-sale-offers/statistics', [PreSaleOfferController::class, 'statistics']);
    Route::apiResource('pre-sale-offers', PreSaleOfferController::class);
    Route::post('pre-sale-offers/{preSaleOffer}/activate', [PreSaleOfferController::class, 'activate']);
    Route::post('pre-sale-offers/{preSaleOffer}/deactivate', [PreSaleOfferController::class, 'deactivate']);
    Route::post('pre-sale-offers/{preSaleOffer}/toggle-featured', [PreSaleOfferController::class, 'toggleFeatured']);
    Route::post('pre-sale-offers/{preSaleOffer}/duplicate', [PreSaleOfferController::class, 'duplicate']);
    Route::post('pre-sale-offers/validate', [PreSaleOfferController::class, 'validate']);
    
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
    Route::get('seo-meta-data/for-model/{type}/{id}', [SeoMetaDataController::class, 'forModel']);
    Route::apiResource('seo-meta-data', SeoMetaDataController::class);
    
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
    
    // Rutas para Form Submissions (gestión interna)
    Route::get('form-submissions', [FormSubmissionController::class, 'index']);
    Route::get('form-submissions/stats', [FormSubmissionController::class, 'stats']);
    Route::get('form-submissions/{formSubmission}', [FormSubmissionController::class, 'show']);
    Route::put('form-submissions/{formSubmission}', [FormSubmissionController::class, 'update']);
    Route::delete('form-submissions/{formSubmission}', [FormSubmissionController::class, 'destroy']);
    Route::post('form-submissions/{formSubmission}/mark-as-processed', [FormSubmissionController::class, 'markAsProcessed']);
    Route::post('form-submissions/{formSubmission}/mark-as-spam', [FormSubmissionController::class, 'markAsSpam']);
    Route::post('form-submissions/{formSubmission}/archive', [FormSubmissionController::class, 'archive']);
    Route::post('form-submissions/{formSubmission}/reopen', [FormSubmissionController::class, 'reopen']);
    
    // Rutas para Organization Features
    Route::apiResource('organization-features', OrganizationFeatureController::class);
    
    // Rutas para Team Memberships
    Route::post('team-memberships/leave', [TeamMembershipController::class, 'leave']);
    Route::apiResource('team-memberships', TeamMembershipController::class);
    
    // Rutas para Team Challenge Progress
    Route::get('team-challenge-progress/leaderboard/{challengeId}', [TeamChallengeProgressController::class, 'leaderboard']);
    Route::post('team-challenge-progress/{teamChallengeProgress}/update-progress', [TeamChallengeProgressController::class, 'updateProgress']);
    Route::apiResource('team-challenge-progress', TeamChallengeProgressController::class);
    
    // Geographic API endpoints
    Route::get('regions/{id}/weather', [RegionController::class, 'weather']);
    Route::get('regions/slug/{slug}', [RegionController::class, 'showBySlug']);
    Route::apiResource('regions', RegionController::class)->only(['index', 'show']);
    
    Route::get('municipalities/{id}/weather', [MunicipalityController::class, 'weather']);
    Route::apiResource('municipalities', MunicipalityController::class)->only(['index', 'show']);
    
    // ========================================
    // ENERGY STORE API ENDPOINTS - TIENDA ENERGÉTICA
    // ========================================
    
    // Rutas para Providers (Proveedores Energéticos)
    Route::get('providers/top-rated', [ProviderController::class, 'topRated']);
    Route::get('providers/renewable', [ProviderController::class, 'renewable']);
    Route::get('providers/{provider}/certifications', [ProviderController::class, 'certifications']);
    Route::get('providers/{provider}/statistics', [ProviderController::class, 'statistics']);
    Route::apiResource('providers', ProviderController::class);
    
    // Rutas para Products (Productos Energéticos)
    Route::get('products/sustainable', [ProductController::class, 'sustainable']);
    Route::get('products/recommendations/{user}', [ProductController::class, 'recommendations']);
    Route::get('products/{product}/pricing', [ProductController::class, 'pricing']);
    Route::get('products/{product}/sustainability', [ProductController::class, 'sustainability']);
    Route::apiResource('products', ProductController::class);
    
    // Rutas para User Assets (Activos de Usuarios)
    Route::get('user-assets/my-assets', [UserAssetController::class, 'myAssets']);
    Route::get('user-assets/portfolio-summary', [UserAssetController::class, 'portfolioSummary']);
    Route::get('user-assets/{userAsset}/performance', [UserAssetController::class, 'performance']);
    Route::post('user-assets/{userAsset}/toggle-auto-reinvest', [UserAssetController::class, 'toggleAutoReinvest']);
    Route::post('user-assets/{userAsset}/process-yield', [UserAssetController::class, 'processYield']);
    Route::apiResource('user-assets', UserAssetController::class);
    
    // Rutas para Balances (Sistema Económico)
    Route::get('balances/my-balance', [BalanceController::class, 'myBalance']);
    Route::get('balances/transaction-history', [BalanceController::class, 'transactionHistory']);
    Route::get('balances/analytics', [BalanceController::class, 'analytics']);
    Route::post('balances/deposit', [BalanceController::class, 'deposit']);
    Route::post('balances/withdraw', [BalanceController::class, 'withdraw']);
    Route::post('balances/investment', [BalanceController::class, 'investment']);
    Route::post('balances/yield', [BalanceController::class, 'yield']);
    Route::apiResource('balances', BalanceController::class)->only(['index', 'show']);

    // ========================================
    // NUEVOS ENERGY STORE API ENDPOINTS - ECOSISTEMA ENERGÉTICO AVANZADO
    // ========================================

    // Rutas para Energy Contracts (Contratos Energéticos)
    Route::get('energy-contracts/my-contracts', [EnergyContractController::class, 'myContracts']);
    Route::get('energy-contracts/analytics', [EnergyContractController::class, 'analytics']);
    Route::post('energy-contracts/{energyContract}/approve', [EnergyContractController::class, 'approve']);
    Route::post('energy-contracts/{energyContract}/suspend', [EnergyContractController::class, 'suspend']);
    Route::post('energy-contracts/{energyContract}/terminate', [EnergyContractController::class, 'terminate']);
    Route::apiResource('energy-contracts', EnergyContractController::class);

    // Rutas para Energy Consumption (Consumo Energético)
    Route::get('energy-consumptions/my-consumptions', [EnergyConsumptionController::class, 'myConsumptions']);
    Route::get('energy-consumptions/analytics', [EnergyConsumptionController::class, 'analytics']);
    Route::apiResource('energy-consumptions', EnergyConsumptionController::class);

    // Rutas para Energy Storage (Almacenamiento Energético)
    Route::get('energy-storages/my-storage-systems', [EnergyStorageController::class, 'myStorageSystems']);
    Route::get('energy-storages/storage-overview', [EnergyStorageController::class, 'storageOverview']);
    Route::post('energy-storages/{energyStorage}/start-charging', [EnergyStorageController::class, 'startCharging']);
    Route::post('energy-storages/{energyStorage}/start-discharging', [EnergyStorageController::class, 'startDischarging']);
    Route::post('energy-storages/{energyStorage}/stop-operation', [EnergyStorageController::class, 'stopOperation']);
    Route::post('energy-storages/{energyStorage}/update-charge-level', [EnergyStorageController::class, 'updateChargeLevel']);
    Route::get('energy-storages/{energyStorage}/performance', [EnergyStorageController::class, 'performance']);
    Route::apiResource('energy-storages', EnergyStorageController::class);

    // Rutas para Energy Production (Producción Energética)
    Route::get('energy-productions/my-productions', [EnergyProductionController::class, 'myProductions']);
    Route::get('energy-productions/analytics', [EnergyProductionController::class, 'analytics']);
    Route::apiResource('energy-productions', EnergyProductionController::class);

    // Rutas para Energy Sources (Fuentes de Energía)
    Route::get('energy-sources/statistics', [EnergySourceController::class, 'statistics']);
    Route::get('energy-sources/categories', [EnergySourceController::class, 'categories']);
    Route::get('energy-sources/types', [EnergySourceController::class, 'types']);
    Route::get('energy-sources/statuses', [EnergySourceController::class, 'statuses']);
    Route::get('energy-sources/featured', [EnergySourceController::class, 'featured']);
    Route::get('energy-sources/renewable', [EnergySourceController::class, 'renewable']);
    Route::get('energy-sources/clean', [EnergySourceController::class, 'clean']);
    Route::post('energy-sources/{energySource}/toggle-active', [EnergySourceController::class, 'toggleActive']);
    Route::post('energy-sources/{energySource}/toggle-featured', [EnergySourceController::class, 'toggleFeatured']);
    Route::post('energy-sources/{energySource}/update-status', [EnergySourceController::class, 'updateStatus']);
    Route::post('energy-sources/{energySource}/duplicate', [EnergySourceController::class, 'duplicate']);
    Route::apiResource('energy-sources', EnergySourceController::class);

    // Rutas para Energy Installations (Instalaciones de Energía)
    Route::get('energy-installations/statistics', [EnergyInstallationController::class, 'statistics']);
    Route::get('energy-installations/types', [EnergyInstallationController::class, 'types']);
    Route::get('energy-installations/statuses', [EnergyInstallationController::class, 'statuses']);
    Route::get('energy-installations/priorities', [EnergyInstallationController::class, 'priorities']);
    Route::get('energy-installations/operational', [EnergyInstallationController::class, 'operational']);
    Route::get('energy-installations/maintenance', [EnergyInstallationController::class, 'maintenance']);
    Route::get('energy-installations/high-priority', [EnergyInstallationController::class, 'highPriority']);
    Route::get('energy-installations/by-type/{type}', [EnergyInstallationController::class, 'byType']);
    Route::get('energy-installations/by-customer/{customer_id}', [EnergyInstallationController::class, 'byCustomer']);
    Route::get('energy-installations/by-project/{project_id}', [EnergyInstallationController::class, 'byProject']);
    Route::post('energy-installations/{energyInstallation}/toggle-active', [EnergyInstallationController::class, 'toggleActive']);
    Route::post('energy-installations/{energyInstallation}/update-status', [EnergyInstallationController::class, 'updateStatus']);
    Route::post('energy-installations/{energyInstallation}/update-priority', [EnergyInstallationController::class, 'updatePriority']);
    Route::post('energy-installations/{energyInstallation}/duplicate', [EnergyInstallationController::class, 'duplicate']);
    Route::apiResource('energy-installations', EnergyInstallationController::class);

    // Rutas para Consumption Points (Puntos de Consumo)
    Route::get('consumption-points/statistics', [ConsumptionPointController::class, 'statistics']);
    Route::get('consumption-points/types', [ConsumptionPointController::class, 'types']);
    Route::get('consumption-points/statuses', [ConsumptionPointController::class, 'statuses']);
    Route::get('consumption-points/active', [ConsumptionPointController::class, 'active']);
    Route::get('consumption-points/maintenance', [ConsumptionPointController::class, 'maintenance']);
    Route::get('consumption-points/disconnected', [ConsumptionPointController::class, 'disconnected']);
    Route::get('consumption-points/by-type/{type}', [ConsumptionPointController::class, 'byType']);
    Route::get('consumption-points/by-customer/{customer_id}', [ConsumptionPointController::class, 'byCustomer']);
    Route::get('consumption-points/by-installation/{installation_id}', [ConsumptionPointController::class, 'byInstallation']);
    Route::get('consumption-points/high-consumption', [ConsumptionPointController::class, 'highConsumption']);
    Route::get('consumption-points/needs-calibration', [ConsumptionPointController::class, 'needsCalibration']);
    Route::post('consumption-points/{consumptionPoint}/update-status', [ConsumptionPointController::class, 'updateStatus']);
    Route::post('consumption-points/{consumptionPoint}/duplicate', [ConsumptionPointController::class, 'duplicate']);
    Route::apiResource('consumption-points', ConsumptionPointController::class);

    // Rutas para Energy Meters (Medidores de Energía)
    Route::get('energy-meters/statistics', [EnergyMeterController::class, 'statistics']);
    Route::get('energy-meters/types', [EnergyMeterController::class, 'types']);
    Route::get('energy-meters/statuses', [EnergyMeterController::class, 'statuses']);
    Route::get('energy-meters/categories', [EnergyMeterController::class, 'categories']);
    Route::get('energy-meters/active', [EnergyMeterController::class, 'active']);
    Route::get('energy-meters/smart-meters', [EnergyMeterController::class, 'smartMeters']);
    Route::get('energy-meters/needs-calibration', [EnergyMeterController::class, 'needsCalibration']);
    Route::get('energy-meters/by-type/{type}', [EnergyMeterController::class, 'byType']);
    Route::get('energy-meters/by-category/{category}', [EnergyMeterController::class, 'byCategory']);
    Route::get('energy-meters/by-customer/{customer_id}', [EnergyMeterController::class, 'byCustomer']);
    Route::get('energy-meters/by-installation/{installation_id}', [EnergyMeterController::class, 'byInstallation']);
    Route::get('energy-meters/high-accuracy', [EnergyMeterController::class, 'highAccuracy']);
    Route::post('energy-meters/{energyMeter}/update-status', [EnergyMeterController::class, 'updateStatus']);
    Route::post('energy-meters/{energyMeter}/duplicate', [EnergyMeterController::class, 'duplicate']);
    Route::apiResource('energy-meters', EnergyMeterController::class);

    // Rutas para Energy Readings (Lecturas de Energía)
    Route::get('energy-readings/statistics', [EnergyReadingController::class, 'statistics']);
    Route::get('energy-readings/types', [EnergyReadingController::class, 'types']);
    Route::get('energy-readings/sources', [EnergyReadingController::class, 'sources']);
    Route::get('energy-readings/statuses', [EnergyReadingController::class, 'statuses']);
    Route::get('energy-readings/update-status', [EnergyReadingController::class, 'updateStatus']);
    Route::post('energy-readings/{energyReading}/validate', [EnergyReadingController::class, 'validate']);
    Route::get('energy-readings/valid', [EnergyReadingController::class, 'valid']);
    Route::get('energy-readings/by-type/{type}', [EnergyReadingController::class, 'byType']);
    Route::get('energy-readings/by-meter/{meter_id}', [EnergyReadingController::class, 'byMeter']);
    Route::get('energy-readings/by-customer/{customer_id}', [EnergyReadingController::class, 'byCustomer']);
    Route::get('energy-readings/high-quality', [EnergyReadingController::class, 'highQuality']);
    Route::get('energy-readings/today', [EnergyReadingController::class, 'today']);
    Route::get('energy-readings/this-month', [EnergyReadingController::class, 'thisMonth']);
    Route::apiResource('energy-readings', EnergyReadingController::class);

// Energy Pool routes
Route::get('energy-pools/statistics', [EnergyPoolController::class, 'statistics']);
Route::get('energy-pools/types', [EnergyPoolController::class, 'types']);
Route::get('energy-pools/statuses', [EnergyPoolController::class, 'statuses']);
Route::get('energy-pools/categories', [EnergyPoolController::class, 'categories']);
Route::patch('energy-pools/{energyPool}/update-status', [EnergyPoolController::class, 'updateStatus']);
Route::post('energy-pools/{energyPool}/duplicate', [EnergyPoolController::class, 'duplicate']);
Route::get('energy-pools/active', [EnergyPoolController::class, 'active']);
Route::get('energy-pools/by-type/{type}', [EnergyPoolController::class, 'byType']);
Route::get('energy-pools/by-category/{category}', [EnergyPoolController::class, 'byCategory']);
Route::get('energy-pools/high-efficiency', [EnergyPoolController::class, 'highEfficiency']);
Route::get('energy-pools/high-availability', [EnergyPoolController::class, 'highAvailability']);
Route::get('energy-pools/by-region/{region}', [EnergyPoolController::class, 'byRegion']);
Route::get('energy-pools/by-country/{country}', [EnergyPoolController::class, 'byCountry']);
Route::get('energy-pools/pending-approval', [EnergyPoolController::class, 'pendingApproval']);
Route::get('energy-pools/approved', [EnergyPoolController::class, 'approved']);
Route::apiResource('energy-pools', EnergyPoolController::class);

// Energy Trading Order routes
Route::get('energy-trading-orders/statistics', [EnergyTradingOrderController::class, 'statistics']);
Route::get('energy-trading-orders/types', [EnergyTradingOrderController::class, 'types']);
Route::get('energy-trading-orders/statuses', [EnergyTradingOrderController::class, 'statuses']);
Route::get('energy-trading-orders/sides', [EnergyTradingOrderController::class, 'sides']);
Route::get('energy-trading-orders/price-types', [EnergyTradingOrderController::class, 'priceTypes']);
Route::get('energy-trading-orders/execution-types', [EnergyTradingOrderController::class, 'executionTypes']);
Route::get('energy-trading-orders/priorities', [EnergyTradingOrderController::class, 'priorities']);
Route::patch('energy-trading-orders/{energyTradingOrder}/update-status', [EnergyTradingOrderController::class, 'updateStatus']);
Route::post('energy-trading-orders/{energyTradingOrder}/cancel', [EnergyTradingOrderController::class, 'cancel']);
Route::post('energy-trading-orders/{energyTradingOrder}/duplicate', [EnergyTradingOrderController::class, 'duplicate']);
Route::get('energy-trading-orders/active', [EnergyTradingOrderController::class, 'active']);
Route::get('energy-trading-orders/pending', [EnergyTradingOrderController::class, 'pending']);
Route::get('energy-trading-orders/filled', [EnergyTradingOrderController::class, 'filled']);
Route::get('energy-trading-orders/by-type/{type}', [EnergyTradingOrderController::class, 'byType']);
Route::get('energy-trading-orders/by-side/{side}', [EnergyTradingOrderController::class, 'bySide']);
Route::get('energy-trading-orders/by-trader/{trader_id}', [EnergyTradingOrderController::class, 'byTrader']);
Route::get('energy-trading-orders/by-pool/{pool_id}', [EnergyTradingOrderController::class, 'byPool']);
Route::get('energy-trading-orders/high-priority', [EnergyTradingOrderController::class, 'highPriority']);
Route::get('energy-trading-orders/negotiable', [EnergyTradingOrderController::class, 'negotiable']);
Route::get('energy-trading-orders/expiring', [EnergyTradingOrderController::class, 'expiring']);
Route::apiResource('energy-trading-orders', EnergyTradingOrderController::class);

// Energy Forecast Routes
Route::get('energy-forecasts/statistics', [EnergyForecastController::class, 'statistics']);
Route::get('energy-forecasts/types', [EnergyForecastController::class, 'types']);
Route::get('energy-forecasts/horizons', [EnergyForecastController::class, 'horizons']);
Route::get('energy-forecasts/methods', [EnergyForecastController::class, 'methods']);
Route::get('energy-forecasts/statuses', [EnergyForecastController::class, 'statuses']);
Route::get('energy-forecasts/accuracy-levels', [EnergyForecastController::class, 'accuracyLevels']);
Route::patch('energy-forecasts/{energyForecast}/update-status', [EnergyForecastController::class, 'updateStatus']);
Route::post('energy-forecasts/{energyForecast}/duplicate', [EnergyForecastController::class, 'duplicate']);
Route::get('energy-forecasts/active', [EnergyForecastController::class, 'active']);
Route::get('energy-forecasts/validated', [EnergyForecastController::class, 'validated']);
Route::get('energy-forecasts/by-type/{type}', [EnergyForecastController::class, 'byType']);
Route::get('energy-forecasts/by-horizon/{horizon}', [EnergyForecastController::class, 'byHorizon']);
Route::get('energy-forecasts/high-accuracy', [EnergyForecastController::class, 'highAccuracy']);
Route::get('energy-forecasts/expiring', [EnergyForecastController::class, 'expiring']);
Route::get('energy-forecasts/by-source/{source_id}', [EnergyForecastController::class, 'bySource']);
Route::get('energy-forecasts/by-target/{target_id}', [EnergyForecastController::class, 'byTarget']);
Route::apiResource('energy-forecasts', EnergyForecastController::class);

// TaxCalculation routes
Route::get('tax-calculations/statistics', [TaxCalculationController::class, 'statistics']);
Route::get('tax-calculations/types', [TaxCalculationController::class, 'types']);
Route::get('tax-calculations/calculation-types', [TaxCalculationController::class, 'calculationTypes']);
Route::get('tax-calculations/statuses', [TaxCalculationController::class, 'statuses']);
Route::get('tax-calculations/priorities', [TaxCalculationController::class, 'priorities']);
Route::patch('tax-calculations/{taxCalculation}/update-status', [TaxCalculationController::class, 'updateStatus']);
Route::post('tax-calculations/{taxCalculation}/duplicate', [TaxCalculationController::class, 'duplicate']);
Route::get('tax-calculations/overdue', [TaxCalculationController::class, 'overdue']);
Route::get('tax-calculations/due-soon', [TaxCalculationController::class, 'dueSoon']);
Route::get('tax-calculations/by-type/{type}', [TaxCalculationController::class, 'byType']);
Route::get('tax-calculations/by-calculation-type/{calculationType}', [TaxCalculationController::class, 'byCalculationType']);
Route::get('tax-calculations/high-priority', [TaxCalculationController::class, 'highPriority']);
Route::get('tax-calculations/estimated', [TaxCalculationController::class, 'estimated']);
Route::get('tax-calculations/final', [TaxCalculationController::class, 'final']);
Route::get('tax-calculations/by-entity/{entityType}/{entityId}', [TaxCalculationController::class, 'byEntity']);
Route::get('tax-calculations/by-transaction/{transactionType}/{transactionId}', [TaxCalculationController::class, 'byTransaction']);
Route::get('tax-calculations/by-currency/{currency}', [TaxCalculationController::class, 'byCurrency']);
Route::get('tax-calculations/by-amount-range', [TaxCalculationController::class, 'byAmountRange']);
Route::apiResource('tax-calculations', TaxCalculationController::class);

// EnergyTransfer routes
Route::get('energy-transfers/statistics', [EnergyTransferController::class, 'statistics']);
Route::get('energy-transfers/types', [EnergyTransferController::class, 'types']);
Route::get('energy-transfers/statuses', [EnergyTransferController::class, 'statuses']);
Route::get('energy-transfers/priorities', [EnergyTransferController::class, 'priorities']);
Route::patch('energy-transfers/{energyTransfer}/update-status', [EnergyTransferController::class, 'updateStatus']);
Route::post('energy-transfers/{energyTransfer}/duplicate', [EnergyTransferController::class, 'duplicate']);
Route::get('energy-transfers/overdue', [EnergyTransferController::class, 'overdue']);
Route::get('energy-transfers/due-soon', [EnergyTransferController::class, 'dueSoon']);
Route::get('energy-transfers/by-type/{type}', [EnergyTransferController::class, 'byType']);
Route::get('energy-transfers/high-priority', [EnergyTransferController::class, 'highPriority']);
Route::get('energy-transfers/automated', [EnergyTransferController::class, 'automated']);
Route::get('energy-transfers/manual', [EnergyTransferController::class, 'manual']);
Route::get('energy-transfers/requires-approval', [EnergyTransferController::class, 'requiresApproval']);
Route::get('energy-transfers/approved', [EnergyTransferController::class, 'approved']);
Route::get('energy-transfers/verified', [EnergyTransferController::class, 'verified']);
Route::get('energy-transfers/by-entity/{entityType}/{entityId}', [EnergyTransferController::class, 'byEntity']);
Route::get('energy-transfers/by-currency/{currency}', [EnergyTransferController::class, 'byCurrency']);
Route::get('energy-transfers/by-amount-range', [EnergyTransferController::class, 'byAmountRange']);
Route::get('energy-transfers/by-efficiency-range', [EnergyTransferController::class, 'byEfficiencyRange']);
Route::apiResource('energy-transfers', EnergyTransferController::class);

// AutomationRule routes
Route::get('automation-rules/statistics', [AutomationRuleController::class, 'statistics']);
Route::get('automation-rules/types', [AutomationRuleController::class, 'types']);
Route::get('automation-rules/trigger-types', [AutomationRuleController::class, 'triggerTypes']);
Route::get('automation-rules/action-types', [AutomationRuleController::class, 'actionTypes']);
Route::get('automation-rules/execution-frequencies', [AutomationRuleController::class, 'executionFrequencies']);
Route::patch('automation-rules/{automationRule}/toggle-active', [AutomationRuleController::class, 'toggleActive']);
Route::post('automation-rules/{automationRule}/duplicate', [AutomationRuleController::class, 'duplicate']);
Route::get('automation-rules/active', [AutomationRuleController::class, 'active']);
Route::get('automation-rules/ready-to-execute', [AutomationRuleController::class, 'readyToExecute']);
Route::get('automation-rules/failed', [AutomationRuleController::class, 'failed']);
Route::get('automation-rules/successful', [AutomationRuleController::class, 'successful']);
Route::get('automation-rules/high-priority', [AutomationRuleController::class, 'highPriority']);
Route::get('automation-rules/by-type/{type}', [AutomationRuleController::class, 'byType']);
Route::get('automation-rules/by-trigger-type/{triggerType}', [AutomationRuleController::class, 'byTriggerType']);
Route::get('automation-rules/by-action-type/{actionType}', [AutomationRuleController::class, 'byActionType']);
Route::get('automation-rules/by-execution-frequency/{frequency}', [AutomationRuleController::class, 'byExecutionFrequency']);
Route::apiResource('automation-rules', AutomationRuleController::class);

// MaintenanceSchedule routes
Route::get('maintenance-schedules/statistics', [MaintenanceScheduleController::class, 'statistics']);
Route::get('maintenance-schedules/schedule-types', [MaintenanceScheduleController::class, 'scheduleTypes']);
Route::get('maintenance-schedules/frequency-types', [MaintenanceScheduleController::class, 'frequencyTypes']);
Route::get('maintenance-schedules/priorities', [MaintenanceScheduleController::class, 'priorities']);
Route::patch('maintenance-schedules/{maintenanceSchedule}/toggle-active', [MaintenanceScheduleController::class, 'toggleActive']);
Route::post('maintenance-schedules/{maintenanceSchedule}/duplicate', [MaintenanceScheduleController::class, 'duplicate']);
Route::get('maintenance-schedules/active', [MaintenanceScheduleController::class, 'active']);
Route::get('maintenance-schedules/overdue', [MaintenanceScheduleController::class, 'overdue']);
Route::get('maintenance-schedules/due-soon', [MaintenanceScheduleController::class, 'dueSoon']);
Route::get('maintenance-schedules/high-priority', [MaintenanceScheduleController::class, 'highPriority']);
Route::get('maintenance-schedules/by-type/{type}', [MaintenanceScheduleController::class, 'byType']);
Route::get('maintenance-schedules/by-frequency-type/{frequencyType}', [MaintenanceScheduleController::class, 'byFrequencyType']);
Route::get('maintenance-schedules/by-priority/{priority}', [MaintenanceScheduleController::class, 'byPriority']);
Route::get('maintenance-schedules/by-department/{department}', [MaintenanceScheduleController::class, 'byDepartment']);
Route::get('maintenance-schedules/by-category/{category}', [MaintenanceScheduleController::class, 'byCategory']);
Route::apiResource('maintenance-schedules', MaintenanceScheduleController::class);

    // Rutas para Production Projects (Proyectos de Producción)
    Route::get('production-projects/statistics', [ProductionProjectController::class, 'statistics']);
    Route::get('production-projects/types', [ProductionProjectController::class, 'types']);
    Route::get('production-projects/statuses', [ProductionProjectController::class, 'statuses']);
    Route::get('production-projects/technology-types', [ProductionProjectController::class, 'technologyTypes']);
    Route::post('production-projects/{productionProject}/toggle-active', [ProductionProjectController::class, 'toggleActive']);
    Route::post('production-projects/{productionProject}/toggle-public', [ProductionProjectController::class, 'togglePublic']);
    Route::post('production-projects/{productionProject}/update-status', [ProductionProjectController::class, 'updateStatus']);
    Route::post('production-projects/{productionProject}/duplicate', [ProductionProjectController::class, 'duplicate']);
    Route::apiResource('production-projects', ProductionProjectController::class);

    // Rutas para Carbon Credits (Créditos de Carbono)
    Route::get('carbon-credits/my-credits', [CarbonCreditController::class, 'myCredits']);
    Route::get('carbon-credits/marketplace', [CarbonCreditController::class, 'marketplace']);
    Route::get('carbon-credits/analytics', [CarbonCreditController::class, 'analytics']);
    Route::post('carbon-credits/{carbonCredit}/verify', [CarbonCreditController::class, 'verify']);
    Route::post('carbon-credits/{carbonCredit}/retire', [CarbonCreditController::class, 'retire']);
    Route::post('carbon-credits/{carbonCredit}/transfer', [CarbonCreditController::class, 'transfer']);
    Route::get('carbon-credits/{carbonCredit}/traceability', [CarbonCreditController::class, 'traceability']);
    Route::apiResource('carbon-credits', CarbonCreditController::class);

    // Rutas para Market Prices (Precios de Mercado)
    Route::get('market-prices/latest', [MarketPriceController::class, 'latest']);
    Route::get('market-prices/analytics', [MarketPriceController::class, 'analytics']);
    Route::get('market-prices/markets', [MarketPriceController::class, 'markets']);
    Route::apiResource('market-prices', MarketPriceController::class);

    // ========================================
    // GESTIÓN DE COMUNIDAD - COOPERATIVAS Y SUSCRIPCIONES
    // ========================================

    // Rutas para Energy Cooperatives (Cooperativas Energéticas)
    Route::get('energy-cooperatives/{energyCooperative}/members', [EnergyCooperativeController::class, 'members']);
    Route::get('energy-cooperatives/{energyCooperative}/analytics', [EnergyCooperativeController::class, 'analytics']);
    Route::post('energy-cooperatives/{energyCooperative}/join', [EnergyCooperativeController::class, 'join']);
    Route::apiResource('energy-cooperatives', EnergyCooperativeController::class);

    // Rutas para User Subscriptions (Suscripciones de Usuario)
    Route::get('user-subscriptions/my-subscriptions', [UserSubscriptionController::class, 'mySubscriptions']);
    Route::post('user-subscriptions/{userSubscription}/pause', [UserSubscriptionController::class, 'pause']);
    Route::post('user-subscriptions/{userSubscription}/resume', [UserSubscriptionController::class, 'resume']);
    Route::get('user-subscriptions/{userSubscription}/usage', [UserSubscriptionController::class, 'usage']);
    Route::apiResource('user-subscriptions', UserSubscriptionController::class);

    // Rutas para Energy Sharing (Intercambios de Energía)
    Route::get('energy-sharings/my-sharings', [EnergySharingController::class, 'mySharings']);
    Route::post('energy-sharings/{energySharing}/accept', [EnergySharingController::class, 'accept']);
    Route::post('energy-sharings/{energySharing}/start', [EnergySharingController::class, 'start']);
    Route::post('energy-sharings/{energySharing}/complete', [EnergySharingController::class, 'complete']);
    Route::post('energy-sharings/{energySharing}/rate', [EnergySharingController::class, 'rate']);
    Route::apiResource('energy-sharings', EnergySharingController::class);

    // ========================================
    // ANALYTICS Y REPORTES
    // ========================================

    // Rutas para Energy Reports (Reportes de Energía)
    Route::get('energy-reports/my-reports', [EnergyReportController::class, 'myReports']);
    Route::post('energy-reports/{energyReport}/generate', [EnergyReportController::class, 'generate']);
    Route::get('energy-reports/{energyReport}/download', [EnergyReportController::class, 'download']);
    Route::post('energy-reports/{energyReport}/share', [EnergyReportController::class, 'share']);
    Route::get('energy-reports/scheduled', [EnergyReportController::class, 'scheduled']);
    Route::apiResource('energy-reports', EnergyReportController::class);

    // Rutas para Sustainability Metrics (Métricas de Sostenibilidad)
    Route::get('sustainability-metrics/summary', [SustainabilityMetricController::class, 'summary']);
    Route::apiResource('sustainability-metrics', SustainabilityMetricController::class);

    // Rutas para Performance Indicators (Indicadores de Rendimiento)
    Route::get('performance-indicators/dashboard', [PerformanceIndicatorController::class, 'dashboard']);
    Route::get('performance-indicators/alerts', [PerformanceIndicatorController::class, 'alerts']);
    Route::apiResource('performance-indicators', PerformanceIndicatorController::class);
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
    
    // Rutas públicas para Form Submissions (envío de formularios)
    Route::post('form-submissions', [FormSubmissionController::class, 'store']);
    
    // ========================================
    // ENERGY STORE PUBLIC API ENDPOINTS - TIENDA ENERGÉTICA PÚBLICA
    // ========================================
    
    // Rutas públicas para Providers (solo lectura)
    Route::get('providers', [ProviderController::class, 'index']);
    Route::get('providers/top-rated', [ProviderController::class, 'topRated']);
    Route::get('providers/renewable', [ProviderController::class, 'renewable']);
    Route::get('providers/{provider}', [ProviderController::class, 'show']);
    Route::get('providers/{provider}/statistics', [ProviderController::class, 'statistics']);
    
    // Rutas públicas para Products (solo lectura)
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/sustainable', [ProductController::class, 'sustainable']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::get('products/{product}/pricing', [ProductController::class, 'pricing']);
    Route::get('products/{product}/sustainability', [ProductController::class, 'sustainability']);

    // ========================================
    // NUEVOS ENERGY STORE PUBLIC API ENDPOINTS - ECOSISTEMA ENERGÉTICO PÚBLICO
    // ========================================

    // Rutas públicas para Carbon Credits Marketplace
    Route::get('carbon-credits/marketplace', [CarbonCreditController::class, 'marketplace']);
    Route::get('carbon-credits/analytics', [CarbonCreditController::class, 'analytics']);
    Route::get('carbon-credits/{carbonCredit}/traceability', [CarbonCreditController::class, 'traceability']);

    // Rutas públicas para Market Prices (datos en tiempo real)
    Route::get('market-prices', [MarketPriceController::class, 'index']);
    Route::get('market-prices/latest', [MarketPriceController::class, 'latest']);
    Route::get('market-prices/analytics', [MarketPriceController::class, 'analytics']);
    Route::get('market-prices/markets', [MarketPriceController::class, 'markets']);
    Route::get('market-prices/{marketPrice}', [MarketPriceController::class, 'show']);

    // Rutas públicas para Energy Storage Overview (estadísticas generales)
    Route::get('energy-storages/storage-overview', [EnergyStorageController::class, 'storageOverview']);

    // ========================================
    // RUTAS PÚBLICAS - GESTIÓN DE COMUNIDAD
    // ========================================

    // Rutas públicas para Energy Cooperatives (información pública)
    Route::get('energy-cooperatives', [EnergyCooperativeController::class, 'index']);
    Route::get('energy-cooperatives/{energyCooperative}', [EnergyCooperativeController::class, 'show']);
    Route::get('energy-cooperatives/{energyCooperative}/analytics', [EnergyCooperativeController::class, 'analytics']);

    // ========================================
    // RUTAS PÚBLICAS - ANALYTICS Y REPORTES
    // ========================================

    // Rutas públicas para Energy Reports (reportes públicos)
    Route::get('energy-reports/public', [EnergyReportController::class, 'index'])->middleware('throttle:60,1');
    Route::get('energy-reports/public/{energyReport}', [EnergyReportController::class, 'show'])->middleware('throttle:60,1');

    // Rutas públicas para Sustainability Metrics (métricas públicas)
    Route::get('sustainability-metrics/public', [SustainabilityMetricController::class, 'index'])->middleware('throttle:60,1');
    Route::get('sustainability-metrics/public/summary', [SustainabilityMetricController::class, 'summary'])->middleware('throttle:60,1');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Energy Bonds Routes
Route::prefix('v1')->name('api.v1.')->group(function () {
    
    // Energy Bonds Routes
    Route::prefix('energy-bonds')->name('energy-bonds.')->group(function () {
        // Public routes (no authentication required)
        Route::get('/public', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'publicBonds'])
            ->name('public');
        Route::get('/featured', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'featuredBonds'])
            ->name('featured');
        Route::get('/statistics', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'statistics'])
            ->name('statistics');
        
        // Protected routes (authentication required)
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'index'])
                ->name('index');
            Route::post('/', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'store'])
                ->name('store');
            Route::get('/{energyBond}', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'show'])
                ->name('show');
            Route::put('/{energyBond}', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'update'])
                ->name('update');
            Route::patch('/{energyBond}', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'update'])
                ->name('update');
            Route::delete('/{energyBond}', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'destroy'])
                ->name('destroy');
            
            // Additional actions
            Route::post('/{energyBond}/approve', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'approve'])
                ->name('approve');
            Route::post('/{energyBond}/reject', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'reject'])
                ->name('reject');
            Route::post('/{energyBond}/mark-featured', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'markFeatured'])
                ->name('mark-featured');
            Route::post('/{energyBond}/remove-featured', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'removeFeatured'])
                ->name('remove-featured');
            Route::post('/{energyBond}/make-public', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'makePublic'])
                ->name('make-public');
            Route::post('/{energyBond}/make-private', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'makePrivate'])
                ->name('make-private');
            Route::post('/{energyBond}/duplicate', [App\Http\Controllers\Api\V1\EnergyBondController::class, 'duplicate'])
                ->name('duplicate');
        });
    });
    
    // Maintenance Tasks Routes
    Route::prefix('maintenance-tasks')->name('maintenance-tasks.')->group(function () {
        // Public routes (no authentication required)
        Route::get('/overdue', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'overdueTasks'])
            ->name('overdue');
        Route::get('/today', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'todayTasks'])
            ->name('today');
        Route::get('/week', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'weekTasks'])
            ->name('week');
        Route::get('/statistics', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'statistics'])
            ->name('statistics');
        
        // Protected routes (authentication required)
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'index'])
                ->name('index');
            Route::post('/', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'store'])
                ->name('store');
            Route::get('/{maintenanceTask}', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'show'])
                ->name('show');
            Route::put('/{maintenanceTask}', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'update'])
                ->name('update');
            Route::patch('/{maintenanceTask}', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'update'])
                ->name('update');
            Route::delete('/{maintenanceTask}', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'destroy'])
                ->name('destroy');
            
            // Additional actions
            Route::post('/{maintenanceTask}/start', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'startTask'])
                ->name('start');
            Route::post('/{maintenanceTask}/complete', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'completeTask'])
                ->name('complete');
            Route::post('/{maintenanceTask}/pause', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'pauseTask'])
                ->name('pause');
            Route::post('/{maintenanceTask}/resume', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'resumeTask'])
                ->name('resume');
            Route::post('/{maintenanceTask}/cancel', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'cancelTask'])
                ->name('cancel');
            Route::post('/{maintenanceTask}/reassign', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'reassignTask'])
                ->name('reassign');
            Route::post('/{maintenanceTask}/update-progress', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'updateProgress'])
                ->name('update-progress');
            Route::post('/{maintenanceTask}/duplicate', [App\Http\Controllers\Api\V1\MaintenanceTaskController::class, 'duplicate'])
                ->name('duplicate');
        });
    });
    
    // Add more API routes here as needed...
});
