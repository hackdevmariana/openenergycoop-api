<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $icon
 * @property string $type
 * @property array<array-key, mixed>|null $criteria
 * @property int $points_reward
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int $total_unlocks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserAchievement> $userAchievements
 * @property-read int|null $user_achievements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement byType(string $type)
 * @method static \Database\Factories\AchievementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCriteria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement wherePointsReward($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereUpdatedAt($value)
 */
	class Achievement extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $affiliate_code
 * @property string $status
 * @property string $tier
 * @property numeric $commission_rate
 * @property numeric $total_earnings
 * @property numeric $pending_earnings
 * @property numeric $paid_earnings
 * @property int $total_referrals
 * @property int $active_referrals
 * @property int $converted_referrals
 * @property numeric $conversion_rate
 * @property \Illuminate\Support\Carbon $joined_date
 * @property \Illuminate\Support\Carbon|null $last_activity_date
 * @property string|null $payment_instructions
 * @property array<array-key, mixed>|null $payment_methods
 * @property array<array-key, mixed>|null $marketing_materials
 * @property array<array-key, mixed>|null $performance_metrics
 * @property int|null $referred_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Affiliate> $referrals
 * @property-read int|null $referrals_count
 * @property-read Affiliate|null $referredBy
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate byReferrer($referrerId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate byTier($tier)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate eligibleForPayout()
 * @method static \Database\Factories\AffiliateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate highPerformers()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereActiveReferrals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereAffiliateCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereCommissionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereConversionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereConvertedReferrals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereJoinedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereLastActivityDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereMarketingMaterials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate wherePaidEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate wherePaymentInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate wherePaymentMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate wherePendingEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate wherePerformanceMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereReferredBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereTier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereTotalEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereTotalReferrals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliate withoutTrashed()
 */
	class Affiliate extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property string $token
 * @property array<array-key, mixed> $scopes
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property string $status
 * @property array<array-key, mixed>|null $allowed_ips
 * @property string|null $callback_url
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property string|null $description
 * @property array<array-key, mixed>|null $rate_limits
 * @property array<array-key, mixed>|null $webhook_config
 * @property array<array-key, mixed>|null $permissions
 * @property string $version
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AuditLog> $auditLogs
 * @property-read int|null $audit_logs_count
 * @property-read mixed $days_until_expiry
 * @property-read mixed $formatted_expires_at
 * @property-read mixed $formatted_last_used_at
 * @property-read mixed $formatted_revoked_at
 * @property-read mixed $is_active
 * @property-read mixed $is_expired
 * @property-read mixed $is_revoked
 * @property-read mixed $is_suspended
 * @property-read mixed $scopes_label
 * @property-read mixed $status_color
 * @property-read mixed $status_label
 * @property-read mixed $token_preview
 * @property-read mixed $version_label
 * @property-read \App\Models\Organization $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient byIpAddress($ipAddress)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient byOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient byScope($scope)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient byVersion($version)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient expired()
 * @method static \Database\Factories\ApiClientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient notExpired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient revoked()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient suspended()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereAllowedIps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereCallbackUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereRateLimits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereRevokedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereScopes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient whereWebhookConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient withRecentActivity($days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiClient withoutTrashed()
 */
	class ApiClient extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property string|null $slogan
 * @property string|null $primary_color
 * @property string|null $secondary_color
 * @property string $locale
 * @property string|null $custom_js
 * @property string|null $favicon_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Organization $organization
 * @method static \Database\Factories\AppSettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereCustomJs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereFaviconPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting wherePrimaryColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereSecondaryColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereSlogan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereUpdatedAt($value)
 */
	class AppSetting extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string|null $subtitle
 * @property string $text
 * @property string|null $excerpt
 * @property string|null $featured_image
 * @property string $slug
 * @property int|null $author_id
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property int|null $category_id
 * @property bool $comment_enabled
 * @property bool $featured
 * @property string $status
 * @property int|null $reading_time
 * @property string|null $seo_focus_keyword
 * @property array<array-key, mixed>|null $related_articles
 * @property int $social_shares_count
 * @property int $number_of_views
 * @property int|null $organization_id
 * @property string $language
 * @property bool $is_draft
 * @property array<array-key, mixed>|null $search_keywords
 * @property string|null $internal_notes
 * @property \Illuminate\Support\Carbon|null $last_reviewed_at
 * @property string|null $accessibility_notes
 * @property string|null $reading_level
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property int|null $approved_by_user_id
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $approvedComments
 * @property-read int|null $approved_comments_count
 * @property-read \App\Models\User|null $author
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\SeoMetaData|null $seoMetaData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Taggable> $tags
 * @property-read int|null $tags_count
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article byAuthor(int $authorId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article byCategory(int $categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article byStatus(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article draft()
 * @method static \Database\Factories\ArticleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article inCurrentLanguage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article inLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article popular()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article recent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article scheduled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article shouldBePublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereAccessibilityNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereApprovedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCommentEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereFeaturedImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereLastReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereNumberOfViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereReadingLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereReadingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereRelatedArticles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereSearchKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereSeoFocusKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereSocialSharesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereUpdatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article withCurrentLanguageFallback()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article withFallback(string $language, ?string $fallback = null)
 */
	class Article extends \Eloquent implements \Spatie\MediaLibrary\HasMedia, \App\Contracts\Cacheable, \App\Contracts\Publishable, \App\Contracts\Multilingual {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $actor_type
 * @property string|null $actor_identifier
 * @property string $action
 * @property string|null $description
 * @property string|null $auditable_type
 * @property int|null $auditable_id
 * @property array<array-key, mixed>|null $old_values
 * @property array<array-key, mixed>|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $url
 * @property string|null $method
 * @property array<array-key, mixed>|null $request_data
 * @property array<array-key, mixed>|null $response_data
 * @property int|null $response_code
 * @property string|null $session_id
 * @property string|null $request_id
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $actor
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $auditable
 * @property-read mixed $action_label
 * @property-read mixed $actor_label
 * @property-read mixed $changes_summary
 * @property-read mixed $formatted_created_at
 * @property-read mixed $formatted_ip_address
 * @property-read mixed $formatted_user_agent
 * @property-read mixed $has_changes
 * @property-read mixed $is_successful
 * @property-read mixed $response_status
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byAction($action)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byActor($actorType, $actorIdentifier = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byAuditable($auditableType, $auditableId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byDateRange($startDate, $endDate = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byIpAddress($ipAddress)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byMethod($method)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byResponseCode($code)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog bySession($sessionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byUser($userId)
 * @method static \Database\Factories\AuditLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog recent($days = 7)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog thisMonth()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog thisWeek()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog thisYear()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog today()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereActorIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereActorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereAuditableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereAuditableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereRequestData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereResponseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog withMetadata($key, $value = null)
 */
	class AuditLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $rule_type
 * @property string $trigger_type
 * @property array<array-key, mixed>|null $trigger_conditions
 * @property string $action_type
 * @property array<array-key, mixed>|null $action_parameters
 * @property int|null $target_entity_id
 * @property string|null $target_entity_type
 * @property bool $is_active
 * @property int $priority
 * @property string|null $execution_frequency
 * @property \Illuminate\Support\Carbon|null $last_executed_at
 * @property \Illuminate\Support\Carbon|null $next_execution_at
 * @property int $execution_count
 * @property int|null $max_executions
 * @property int $success_count
 * @property int $failure_count
 * @property string|null $last_error_message
 * @property string|null $schedule_cron
 * @property string|null $timezone
 * @property bool $retry_on_failure
 * @property int $max_retries
 * @property int $retry_delay_minutes
 * @property array<array-key, mixed>|null $notification_emails
 * @property string|null $webhook_url
 * @property array<array-key, mixed>|null $webhook_headers
 * @property array<array-key, mixed>|null $tags
 * @property string|null $notes
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $targetEntity
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule byActionType($actionType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule byExecutionFrequency($frequency)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule byTriggerType($triggerType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule conditionBased()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule eventDriven()
 * @method static \Database\Factories\AutomationRuleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule failed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule readyToExecute()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule scheduled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule successful()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereActionParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereActionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereExecutionCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereExecutionFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereFailureCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereLastErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereLastExecutedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereMaxExecutions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereMaxRetries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereNextExecutionAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereNotificationEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereRetryDelayMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereRetryOnFailure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereRuleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereScheduleCron($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereSuccessCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereTargetEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereTargetEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereTriggerConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereTriggerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereWebhookHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule whereWebhookUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutomationRule withoutTrashed()
 */
	class AutomationRule extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property numeric $amount
 * @property string $currency
 * @property bool $is_frozen
 * @property \Illuminate\Support\Carbon|null $last_transaction_at
 * @property numeric|null $daily_limit
 * @property numeric|null $monthly_limit
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BalanceTransaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance byCurrency(string $currency)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance byType(string $type)
 * @method static \Database\Factories\BalanceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereDailyLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereIsFrozen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereLastTransactionAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereMonthlyLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance withPositiveBalance()
 */
	class Balance extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $balance_id
 * @property string $type
 * @property numeric $amount
 * @property numeric $balance_before
 * @property numeric $balance_after
 * @property string $description
 * @property string|null $reference
 * @property string|null $related_model_type
 * @property int|null $related_model_id
 * @property string|null $batch_id
 * @property numeric|null $exchange_rate
 * @property string|null $original_currency
 * @property numeric|null $original_amount
 * @property numeric $tax_amount
 * @property numeric $fee_amount
 * @property numeric $net_amount
 * @property array<array-key, mixed>|null $metadata
 * @property int|null $created_by_user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property string|null $notes
 * @property string|null $accounting_reference
 * @property bool $is_reconciled
 * @property \Illuminate\Support\Carbon|null $reconciled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Balance $balance
 * @property-read \App\Models\User|null $createdByUser
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $relatedModel
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction byBatch(string $batchId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction byStatus(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction expense()
 * @method static \Database\Factories\BalanceTransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction inDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction income()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction reconciled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction unreconciled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereAccountingReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereBalanceBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereBalanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereFeeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereIsReconciled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereOriginalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereOriginalCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereReconciledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereRelatedModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereRelatedModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BalanceTransaction whereUpdatedAt($value)
 */
	class BalanceTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $image
 * @property string|null $mobile_image
 * @property string|null $internal_link
 * @property string|null $url
 * @property int $position
 * @property bool $active
 * @property string|null $alt_text
 * @property string|null $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $exhibition_beginning
 * @property \Illuminate\Support\Carbon|null $exhibition_end
 * @property string $banner_type
 * @property array<array-key, mixed>|null $display_rules
 * @property int $click_count
 * @property int $impression_count
 * @property int|null $organization_id
 * @property bool $is_draft
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner byPosition(string $position)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner currentlyDisplaying()
 * @method static \Database\Factories\BannerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereBannerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereClickCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereDisplayRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereExhibitionBeginning($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereExhibitionEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereImpressionCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereInternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereMobileImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereUpdatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereUrl($value)
 */
	class Banner extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $donation_number
 * @property int $donor_id
 * @property int $energy_bond_id
 * @property string $donation_type
 * @property string $status
 * @property numeric $donation_amount
 * @property int $bond_units
 * @property numeric $unit_price_at_donation
 * @property numeric $total_value_at_donation
 * @property numeric $current_value
 * @property \Illuminate\Support\Carbon $donation_date
 * @property \Illuminate\Support\Carbon|null $approval_date
 * @property \Illuminate\Support\Carbon|null $completion_date
 * @property \Illuminate\Support\Carbon|null $expiry_date
 * @property string $donation_purpose
 * @property string|null $impact_description
 * @property string|null $recipient_organization
 * @property string|null $recipient_beneficiaries
 * @property string|null $project_description
 * @property string|null $project_status
 * @property numeric|null $project_budget
 * @property numeric|null $project_spent
 * @property \Illuminate\Support\Carbon|null $project_start_date
 * @property \Illuminate\Support\Carbon|null $project_end_date
 * @property string|null $project_milestones
 * @property string|null $project_outcomes
 * @property string|null $project_challenges
 * @property string|null $project_lessons_learned
 * @property bool $is_anonymous
 * @property bool $is_recurring
 * @property string|null $recurrence_frequency
 * @property \Illuminate\Support\Carbon|null $next_recurrence_date
 * @property int $recurrence_count
 * @property int|null $max_recurrences
 * @property bool $is_matched
 * @property numeric|null $matching_ratio
 * @property numeric|null $matching_amount
 * @property string|null $matching_organization
 * @property string|null $matching_terms
 * @property bool $is_tax_deductible
 * @property string|null $tax_deduction_reference
 * @property numeric|null $tax_deduction_amount
 * @property string|null $tax_deduction_notes
 * @property array<array-key, mixed>|null $donor_preferences
 * @property array<array-key, mixed>|null $communication_preferences
 * @property array<array-key, mixed>|null $reporting_preferences
 * @property array<array-key, mixed>|null $recognition_preferences
 * @property string|null $special_instructions
 * @property string|null $internal_notes
 * @property array<array-key, mixed>|null $tags
 * @property int $created_by
 * @property int|null $approved_by
 * @property int|null $processed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User $donor
 * @property-read \App\Models\EnergyBond $energyBond
 * @property-read \App\Models\User|null $processedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation anonymous()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation byAmount($minAmount = 0)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation byProjectStatus($projectStatus)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation byType($type)
 * @method static \Database\Factories\BondDonationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation matched()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation recent($days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation recurring()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation taxDeductible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereApprovalDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereBondUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereCommunicationPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereCompletionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereCurrentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereDonationAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereDonationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereDonationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereDonationPurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereDonationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereDonorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereDonorPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereEnergyBondId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereImpactDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereIsAnonymous($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereIsMatched($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereIsTaxDeductible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereMatchingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereMatchingOrganization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereMatchingRatio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereMatchingTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereMaxRecurrences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereNextRecurrenceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProcessedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProjectBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProjectChallenges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProjectDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProjectEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProjectLessonsLearned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProjectMilestones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProjectOutcomes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProjectSpent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProjectStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereProjectStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereRecipientBeneficiaries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereRecipientOrganization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereRecognitionPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereRecurrenceCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereRecurrenceFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereReportingPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereSpecialInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereTaxDeductionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereTaxDeductionNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereTaxDeductionReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereTotalValueAtDonation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereUnitPriceAtDonation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BondDonation whereUpdatedAt($value)
 */
	class BondDonation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $provider_id
 * @property int|null $user_asset_id
 * @property int|null $energy_production_id
 * @property string $credit_id
 * @property string|null $registry_id
 * @property string|null $serial_number
 * @property string|null $batch_id
 * @property string $credit_type
 * @property string|null $standard_version
 * @property string|null $methodology
 * @property string $project_name
 * @property string|null $project_description
 * @property string|null $project_id
 * @property string $project_type
 * @property string $project_location
 * @property string $project_country
 * @property string|null $project_coordinates
 * @property string $total_credits
 * @property string $available_credits
 * @property string $retired_credits
 * @property string $transferred_credits
 * @property string $status
 * @property \Illuminate\Support\Carbon $credit_period_start
 * @property \Illuminate\Support\Carbon $credit_period_end
 * @property \Illuminate\Support\Carbon $vintage_year
 * @property string|null $issuance_date
 * @property \Illuminate\Support\Carbon|null $verification_date
 * @property string|null $expiry_date
 * @property string|null $purchase_price_per_credit
 * @property string|null $current_market_price
 * @property string|null $total_investment
 * @property string $currency
 * @property string|null $verifier_name
 * @property string|null $verifier_accreditation
 * @property string|null $last_verification_date
 * @property string|null $next_verification_date
 * @property string|null $verification_documents
 * @property bool $additionality_demonstrated
 * @property string|null $additionality_justification
 * @property string|null $co_benefits
 * @property string|null $sdg_contributions
 * @property string|null $monitoring_frequency
 * @property string|null $last_monitoring_report_date
 * @property string|null $monitoring_data
 * @property array<array-key, mixed>|null $transaction_history
 * @property int|null $original_owner_id
 * @property \Illuminate\Support\Carbon|null $last_transfer_date
 * @property string|null $transfer_fees
 * @property string|null $retirement_reason
 * @property \Illuminate\Support\Carbon|null $retirement_date
 * @property int|null $retired_by
 * @property string|null $retirement_certificate
 * @property string|null $risk_rating
 * @property string|null $risk_factors
 * @property int $insurance_coverage
 * @property string|null $insurance_amount
 * @property string|null $blockchain_hash
 * @property string|null $provenance_chain
 * @property int $public_registry_listed
 * @property string|null $registry_url
 * @property string|null $actual_co2_reduced
 * @property string|null $measurement_uncertainty
 * @property string|null $environmental_monitoring
 * @property string|null $technical_specifications
 * @property string|null $project_capacity_mw
 * @property int|null $expected_project_lifetime_years
 * @property string|null $annual_emission_reductions
 * @property string|null $regulatory_approvals
 * @property int $meets_article_6_requirements
 * @property int $corresponding_adjustment_applied
 * @property string|null $regulatory_metadata
 * @property string|null $sustainability_certifications
 * @property int $gender_inclusive
 * @property int $community_engagement
 * @property string|null $social_impact_description
 * @property string|null $custom_attributes
 * @property string|null $notes
 * @property string|null $attachments
 * @property int $is_active
 * @property string|null $approved_at
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $originalOwner
 * @property-read \App\Models\Provider|null $provider
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\CarbonCreditFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereActualCo2Reduced($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereAdditionalityDemonstrated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereAdditionalityJustification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereAnnualEmissionReductions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereAvailableCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereBlockchainHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCoBenefits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCommunityEngagement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCorrespondingAdjustmentApplied($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCreditId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCreditPeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCreditPeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCreditType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCurrentMarketPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereCustomAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereEnergyProductionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereEnvironmentalMonitoring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereExpectedProjectLifetimeYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereGenderInclusive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereInsuranceAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereInsuranceCoverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereIssuanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereLastMonitoringReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereLastTransferDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereLastVerificationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereMeasurementUncertainty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereMeetsArticle6Requirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereMethodology($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereMonitoringData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereMonitoringFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereNextVerificationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereOriginalOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereProjectCapacityMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereProjectCoordinates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereProjectCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereProjectDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereProjectLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereProjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereProvenanceChain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit wherePublicRegistryListed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit wherePurchasePricePerCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRegistryUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRegulatoryApprovals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRegulatoryMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRetiredBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRetiredCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRetirementCertificate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRetirementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRetirementReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRiskFactors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereRiskRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereSdgContributions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereSocialImpactDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereStandardVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereSustainabilityCertifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereTechnicalSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereTotalCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereTotalInvestment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereTransactionHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereTransferFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereTransferredCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereUserAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereVerificationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereVerificationDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereVerifierAccreditation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereVerifierName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarbonCredit whereVintageYear($value)
 */
	class CarbonCredit extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $color
 * @property string|null $icon
 * @property int|null $parent_id
 * @property int $sort_order
 * @property bool $is_active
 * @property string $category_type
 * @property int|null $organization_id
 * @property string $language
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $activeChildren
 * @property-read int|null $active_children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $activeImages
 * @property-read int|null $active_images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Article> $articles
 * @property-read int|null $articles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $images
 * @property-read int|null $images_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Article> $publishedArticles
 * @property-read int|null $published_articles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category byType(string $type)
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category inCurrentLanguage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category inLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category rootCategories()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCategoryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withCounts()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withCurrentLanguageFallback()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withFallback(string $language, ?string $fallback = null)
 */
	class Category extends \Eloquent implements \App\Contracts\Cacheable, \App\Contracts\Multilingual {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property numeric $target_kwh
 * @property int $points_reward
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property bool $is_active
 * @property array<array-key, mixed>|null $criteria
 * @property string|null $icon
 * @property int|null $organization_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $average_progress
 * @property-read int $completed_teams_count
 * @property-read float $completion_rate
 * @property-read int $days_remaining
 * @property-read int $duration_in_days
 * @property-read \App\Models\Team|null $leader_team
 * @property-read int $participating_teams_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamChallengeProgress> $teamProgress
 * @property-read int|null $team_progress_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge byOrganization(int $organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge current()
 * @method static \Database\Factories\ChallengeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge individualChallenges()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge organizationChallenges()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge past()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge teamChallenges()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge upcoming()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereCriteria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge wherePointsReward($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereTargetKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereUpdatedAt($value)
 */
	class Challenge extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $template_type
 * @property string|null $category
 * @property string|null $subcategory
 * @property array<array-key, mixed>|null $checklist_items
 * @property array<array-key, mixed>|null $required_items
 * @property array<array-key, mixed>|null $optional_items
 * @property array<array-key, mixed>|null $conditional_items
 * @property array<array-key, mixed>|null $item_order
 * @property array<array-key, mixed>|null $scoring_system
 * @property numeric|null $pass_threshold
 * @property numeric|null $fail_threshold
 * @property bool $is_active
 * @property bool $is_standard
 * @property string $version
 * @property array<array-key, mixed>|null $tags
 * @property string|null $notes
 * @property string|null $department
 * @property string $priority
 * @property string $risk_level
 * @property array<array-key, mixed>|null $compliance_requirements
 * @property array<array-key, mixed>|null $quality_standards
 * @property array<array-key, mixed>|null $safety_requirements
 * @property bool $training_required
 * @property bool $certification_required
 * @property array<array-key, mixed>|null $documentation_required
 * @property array<array-key, mixed>|null $environmental_considerations
 * @property string|null $budget_code
 * @property string|null $cost_center
 * @property string|null $project_code
 * @property numeric|null $estimated_completion_time
 * @property numeric|null $estimated_cost
 * @property array<array-key, mixed>|null $required_skills
 * @property array<array-key, mixed>|null $required_tools
 * @property array<array-key, mixed>|null $required_parts
 * @property array<array-key, mixed>|null $work_instructions
 * @property array<array-key, mixed>|null $reference_documents
 * @property array<array-key, mixed>|null $best_practices
 * @property array<array-key, mixed>|null $lessons_learned
 * @property array<array-key, mixed>|null $continuous_improvement
 * @property int|null $audit_frequency
 * @property \Illuminate\Support\Carbon|null $last_review_date
 * @property \Illuminate\Support\Carbon|null $next_review_date
 * @property int|null $reviewed_by
 * @property array<array-key, mixed>|null $approval_workflow
 * @property array<array-key, mixed>|null $escalation_procedures
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceSchedule> $maintenanceSchedules
 * @property-read int|null $maintenance_schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceTask> $maintenanceTasks
 * @property-read int|null $maintenance_tasks_count
 * @property-read \App\Models\User|null $reviewedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate byCategory($category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate byDepartment($department)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate byRiskLevel($riskLevel)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate needsReview()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate standard()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereApprovalWorkflow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereAuditFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereBestPractices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereBudgetCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereCertificationRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereChecklistItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereComplianceRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereConditionalItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereContinuousImprovement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereCostCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereDocumentationRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereEnvironmentalConsiderations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereEscalationProcedures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereEstimatedCompletionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereEstimatedCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereFailThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereIsStandard($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereItemOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereLastReviewDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereLessonsLearned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereNextReviewDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereOptionalItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate wherePassThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereProjectCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereQualityStandards($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereReferenceDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereRequiredItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereRequiredParts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereRequiredSkills($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereRequiredTools($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereRiskLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereSafetyRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereScoringSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereSubcategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereTemplateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereTrainingRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate whereWorkInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistTemplate withoutTrashed()
 */
	class ChecklistTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $logo
 * @property string|null $url
 * @property string|null $description
 * @property int $order
 * @property bool $is_active
 * @property string $collaborator_type
 * @property int|null $organization_id
 * @property bool $is_draft
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $created_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Organization|null $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator byType(string $type)
 * @method static \Database\Factories\CollaboratorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator orderedByPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereCollaboratorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collaborator whereUrl($value)
 */
	class Collaborator extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $commentable_type
 * @property int $commentable_id
 * @property int|null $user_id
 * @property string|null $author_name
 * @property string|null $author_email
 * @property string $content
 * @property string $status
 * @property int|null $parent_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $likes_count
 * @property int $dislikes_count
 * @property bool $is_pinned
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Comment> $approvedReplies
 * @property-read int|null $approved_replies_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $commentable
 * @property-read Comment|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Comment> $replies
 * @property-read int|null $replies_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment byStatus(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment byUser(int $userId)
 * @method static \Database\Factories\CommentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment pinned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment popular()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment recent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment replies()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment rootComments()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereApprovedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereAuthorEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereAuthorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereCommentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereCommentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereDislikesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereIsPinned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereLikesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereUserId($value)
 */
	class Comment extends \Eloquent implements \App\Contracts\Cacheable {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $organization_id
 * @property int $total_users
 * @property numeric $total_kwh_produced
 * @property numeric $total_co2_avoided
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $co2_per_user
 * @property-read mixed $efficiency
 * @property-read mixed $formatted_calculated_efficiency
 * @property-read mixed $formatted_co2
 * @property-read mixed $formatted_co2_per_user
 * @property-read mixed $formatted_date
 * @property-read mixed $formatted_efficiency
 * @property-read mixed $formatted_kwh
 * @property-read mixed $formatted_production_per_user
 * @property-read mixed $formatted_ranking
 * @property-read mixed $formatted_users
 * @property-read mixed $is_active
 * @property-read mixed $is_inactive
 * @property-read mixed $production_per_user
 * @property-read \App\Models\Organization $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics byCo2Range($minCo2, $maxCo2 = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics byKwhRange($minKwh, $maxKwh = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics byOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics byUserCount($minUsers, $maxUsers = null)
 * @method static \Database\Factories\CommunityMetricsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics orderByDate($direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics orderByImpact($direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics orderByProduction($direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics orderByUsers($direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics recent($days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics thisMonth()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics thisYear()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics whereTotalCo2Avoided($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics whereTotalKwhProduced($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics whereTotalUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommunityMetrics withoutTrashed()
 */
	class CommunityMetrics extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $cif
 * @property string $contact_person
 * @property string $company_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SubscriptionRequest> $subscriptionRequests
 * @property-read int|null $subscription_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\CompanyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereContactPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedAt($value)
 */
	class Company extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $consent_type
 * @property bool $consent_given
 * @property \Illuminate\Support\Carbon $consented_at
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $version
 * @property string|null $purpose
 * @property string|null $legal_basis
 * @property array<array-key, mixed>|null $data_categories
 * @property string|null $retention_period
 * @property array<array-key, mixed>|null $third_parties
 * @property string|null $withdrawal_method
 * @property string|null $consent_document_url
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property string|null $revocation_reason
 * @property array<array-key, mixed>|null $consent_context
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $consent_type_name
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog active()
 * @method static \Database\Factories\ConsentLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog ofType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog revoked()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereConsentContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereConsentDocumentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereConsentGiven($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereConsentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereConsentedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereDataCategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereLegalBasis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereRetentionPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereRevocationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereRevokedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereThirdParties($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsentLog whereWithdrawalMethod($value)
 */
	class ConsentLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $point_number
 * @property string $name
 * @property string|null $description
 * @property string $point_type
 * @property string $status
 * @property int|null $customer_id
 * @property int|null $installation_id
 * @property string $location_address
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property numeric|null $peak_demand_kw
 * @property numeric|null $average_demand_kw
 * @property numeric|null $annual_consumption_kwh
 * @property numeric|null $monthly_consumption_kwh
 * @property numeric|null $daily_consumption_kwh
 * @property numeric|null $hourly_consumption_kwh
 * @property \Illuminate\Support\Carbon|null $connection_date
 * @property \Illuminate\Support\Carbon|null $disconnection_date
 * @property string|null $meter_number
 * @property string|null $meter_type
 * @property string|null $meter_manufacturer
 * @property string|null $meter_model
 * @property \Illuminate\Support\Carbon|null $meter_installation_date
 * @property \Illuminate\Support\Carbon|null $meter_last_calibration_date
 * @property \Illuminate\Support\Carbon|null $meter_next_calibration_date
 * @property numeric|null $voltage_level
 * @property string|null $voltage_unit
 * @property numeric|null $current_rating
 * @property string|null $current_unit
 * @property string|null $phase_type
 * @property string|null $connection_type
 * @property string|null $technical_specifications
 * @property string|null $safety_features
 * @property array<array-key, mixed>|null $load_profile
 * @property array<array-key, mixed>|null $consumption_patterns
 * @property array<array-key, mixed>|null $peak_hours
 * @property array<array-key, mixed>|null $off_peak_hours
 * @property array<array-key, mixed>|null $tags
 * @property int|null $managed_by
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyForecast> $forecasts
 * @property-read int|null $forecasts_count
 * @property-read \App\Models\EnergyInstallation|null $installation
 * @property-read \App\Models\User|null $managedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyMeter> $meters
 * @property-read int|null $meters_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyReading> $readings
 * @property-read int|null $readings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint agricultural()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint byCustomer($customerId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint byInstallation($installationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint byMeterNumber($meterNumber)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint byMeterType($meterType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint byPointType($pointType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint chargingStation()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint commercial()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint decommissioned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint disconnected()
 * @method static \Database\Factories\ConsumptionPointFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint industrial()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint maintenance()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint planned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint public()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint residential()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint streetLighting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereAnnualConsumptionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereAverageDemandKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereConnectionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereConnectionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereConsumptionPatterns($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereCurrentRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereCurrentUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereDailyConsumptionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereDisconnectionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereHourlyConsumptionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereInstallationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereLoadProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereLocationAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereManagedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereMeterInstallationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereMeterLastCalibrationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereMeterManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereMeterModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereMeterNextCalibrationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereMeterNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereMeterType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereMonthlyConsumptionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereOffPeakHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint wherePeakDemandKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint wherePeakHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint wherePhaseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint wherePointNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint wherePointType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereSafetyFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereTechnicalSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereVoltageLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumptionPoint whereVoltageUnit($value)
 */
	class ConsumptionPoint extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $address
 * @property string|null $icon_address
 * @property string|null $phone
 * @property string|null $icon_phone
 * @property string|null $email
 * @property string|null $icon_email
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string $contact_type
 * @property array<array-key, mixed>|null $business_hours
 * @property string|null $additional_info
 * @property int|null $organization_id
 * @property bool $is_draft
 * @property bool $is_primary
 * @property int|null $created_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Organization|null $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact byType($type)
 * @method static \Database\Factories\ContactFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact primary()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereAdditionalInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereBusinessHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereContactType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereIconAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereIconEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereIconPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact withLocation()
 */
	class Contact extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $cooperative_id
 * @property int $plant_id
 * @property bool $default
 * @property bool $active
 * @property int|null $organization_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EnergyCooperative $cooperative
 * @property-read mixed $is_active
 * @property-read mixed $is_default
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\Plant $plant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig byCooperative($cooperativeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig byOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig byPlant($plantId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig default()
 * @method static \Database\Factories\CooperativePlantConfigFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig whereCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig wherePlantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CooperativePlantConfig whereUpdatedAt($value)
 */
	class CooperativePlantConfig extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $organization_id
 * @property string $profile_type
 * @property string $legal_id_type
 * @property string $legal_id_number
 * @property string $legal_name
 * @property string $contract_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CustomerProfileContactInfo|null $contactInfo
 * @property-read mixed $full_legal_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LegalDocument> $legalDocuments
 * @property-read int|null $legal_documents_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile byContractType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile byProfileType($type)
 * @method static \Database\Factories\CustomerProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereContractType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereLegalIdNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereLegalIdType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereLegalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereProfileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereUserId($value)
 */
	class CustomerProfile extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $customer_profile_id
 * @property int $organization_id
 * @property string|null $billing_email
 * @property string|null $technical_email
 * @property string $address
 * @property string $postal_code
 * @property string $city
 * @property string $province
 * @property string|null $iban
 * @property string|null $cups
 * @property \Illuminate\Support\Carbon $valid_from
 * @property \Illuminate\Support\Carbon|null $valid_to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CustomerProfile|null $customerProfile
 * @property-read \App\Models\Organization|null $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo byProvince($province)
 * @method static \Database\Factories\CustomerProfileContactInfoFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo valid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereBillingEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereCups($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereCustomerProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereIban($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereTechnicalEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfileContactInfo whereValidTo($value)
 */
	class CustomerProfileContactInfo extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $name
 * @property array<array-key, mixed> $layout_json
 * @property bool $is_default
 * @property string $theme
 * @property string $color_scheme
 * @property array<array-key, mixed>|null $widget_settings
 * @property bool $is_public
 * @property string|null $description
 * @property array<array-key, mixed>|null $access_permissions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $color_scheme_label
 * @property-read mixed $formatted_created_at
 * @property-read mixed $formatted_updated_at
 * @property-read mixed $theme_label
 * @property-read mixed $visible_widget_count
 * @property-read mixed $widget_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $sharedWith
 * @property-read int|null $shared_with_count
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DashboardWidget> $widgets
 * @property-read int|null $widgets_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView byColorScheme($colorScheme)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView byTheme($theme)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView byUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView default()
 * @method static \Database\Factories\DashboardViewFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView private()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView public()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereAccessPermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereColorScheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereLayoutJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView whereWidgetSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView withWidgets()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardView withoutTrashed()
 */
	class DashboardView extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $dashboard_view_id
 * @property string $type
 * @property string|null $title
 * @property int $position
 * @property array<array-key, mixed>|null $settings_json
 * @property bool $visible
 * @property bool $collapsible
 * @property bool $collapsed
 * @property string $size
 * @property array<array-key, mixed>|null $grid_position
 * @property int|null $refresh_interval
 * @property \Illuminate\Support\Carbon|null $last_refresh
 * @property array<array-key, mixed>|null $data_source
 * @property array<array-key, mixed>|null $filters
 * @property array<array-key, mixed>|null $permissions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\DashboardView|null $dashboardView
 * @property-read mixed $default_height
 * @property-read mixed $default_width
 * @property-read mixed $filter
 * @property-read mixed $formatted_last_refresh
 * @property-read mixed $is_collapsed
 * @property-read mixed $is_collapsible
 * @property-read mixed $is_visible
 * @property-read mixed $permission
 * @property-read mixed $setting
 * @property-read mixed $size_label
 * @property-read mixed $type_label
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget byDashboardView($viewId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget bySize($size)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget byUser($userId)
 * @method static \Database\Factories\DashboardWidgetFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget hidden()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget visible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereCollapsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereCollapsible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereDashboardViewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereDataSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereGridPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereLastRefresh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereRefreshInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereSettingsJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget whereVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget withData()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DashboardWidget withoutTrashed()
 */
	class DashboardWidget extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $user_id
 * @property int|null $consumption_point_id
 * @property string|null $api_endpoint
 * @property array<array-key, mixed>|null $api_credentials
 * @property array<array-key, mixed>|null $device_config
 * @property bool $active
 * @property string|null $model
 * @property string|null $manufacturer
 * @property string|null $serial_number
 * @property string|null $firmware_version
 * @property \Illuminate\Support\Carbon|null $last_communication
 * @property array<array-key, mixed>|null $capabilities
 * @property string|null $location
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\ConsumptionPoint|null $consumptionPoint
 * @property-read mixed $display_name
 * @property-read mixed $formatted_last_communication
 * @property-read mixed $is_offline
 * @property-read mixed $is_online
 * @property-read mixed $status
 * @property-read mixed $status_color
 * @property-read mixed $type_label
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device byLocation($location)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device byManufacturer($manufacturer)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device byModel($model)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device byUser($userId)
 * @method static \Database\Factories\DeviceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device offline()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device online()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereApiCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereApiEndpoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereCapabilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereConsumptionPointId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereDeviceConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereFirmwareVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereLastCommunication($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device withCapability($capability)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device withoutTrashed()
 */
	class Device extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $discount_type
 * @property numeric $discount_value
 * @property numeric|null $minimum_purchase_amount
 * @property numeric|null $maximum_discount_amount
 * @property string $status
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property int|null $usage_limit
 * @property int $usage_count
 * @property int $per_user_limit
 * @property bool $is_first_time_only
 * @property bool $is_new_customer_only
 * @property array<array-key, mixed>|null $applicable_products
 * @property array<array-key, mixed>|null $excluded_products
 * @property array<array-key, mixed>|null $applicable_categories
 * @property array<array-key, mixed>|null $excluded_categories
 * @property array<array-key, mixed>|null $applicable_user_groups
 * @property array<array-key, mixed>|null $excluded_user_groups
 * @property bool $can_be_combined
 * @property array<array-key, mixed>|null $combination_rules
 * @property string|null $terms_conditions
 * @property string|null $usage_instructions
 * @property array<array-key, mixed>|null $tags
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode byCode($code)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode canBeCombined()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode depleted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode expired()
 * @method static \Database\Factories\DiscountCodeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode firstTimeOnly()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode newCustomerOnly()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode valid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereApplicableCategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereApplicableProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereApplicableUserGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereCanBeCombined($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereCombinationRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereDiscountValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereExcludedCategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereExcludedProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereExcludedUserGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereIsFirstTimeOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereIsNewCustomerOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereMaximumDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereMinimumPurchaseAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode wherePerUserLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereTermsConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereUsageCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereUsageInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountCode whereUsageLimit($value)
 */
	class DiscountCode extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $file_path
 * @property string|null $file_type
 * @property int|null $file_size
 * @property string|null $mime_type
 * @property string|null $checksum
 * @property bool $visible
 * @property int|null $category_id
 * @property int|null $uploaded_by
 * @property \Illuminate\Support\Carbon|null $uploaded_at
 * @property int $download_count
 * @property int $number_of_views
 * @property string $version
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $requires_auth
 * @property array<array-key, mixed>|null $allowed_roles
 * @property string|null $thumbnail_path
 * @property string $language
 * @property int|null $organization_id
 * @property bool $is_draft
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property array<array-key, mixed>|null $search_keywords
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\SeoMetaData|null $seoMetaData
 * @property-read \App\Models\User|null $updatedBy
 * @property-read \App\Models\User|null $uploadedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document byCategory(int $categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document draft()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document expired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document expiringWithin(int $days)
 * @method static \Database\Factories\DocumentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document inCurrentLanguage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document inLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document popular()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document recent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document shouldBePublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document visible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereAllowedRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereChecksum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDownloadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereNumberOfViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereRequiresAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSearchKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUploadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withCurrentLanguageFallback()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withFallback(string $language, ?string $fallback = null)
 */
	class Document extends \Eloquent implements \Spatie\MediaLibrary\HasMedia, \App\Contracts\Cacheable, \App\Contracts\Publishable, \App\Contracts\Multilingual {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $bond_number
 * @property string $name
 * @property string|null $description
 * @property string $bond_type
 * @property string $status
 * @property numeric $face_value
 * @property numeric $current_value
 * @property numeric $interest_rate
 * @property string $interest_frequency
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $maturity_date
 * @property \Illuminate\Support\Carbon $first_interest_date
 * @property \Illuminate\Support\Carbon|null $last_interest_payment_date
 * @property \Illuminate\Support\Carbon|null $next_interest_payment_date
 * @property int $total_interest_payments
 * @property int $paid_interest_payments
 * @property numeric $total_interest_paid
 * @property numeric $outstanding_principal
 * @property numeric $minimum_investment
 * @property numeric|null $maximum_investment
 * @property int $total_units_available
 * @property int $units_issued
 * @property int $units_reserved
 * @property numeric $unit_price
 * @property string $payment_schedule
 * @property bool $is_tax_free
 * @property numeric $tax_rate
 * @property bool $is_guaranteed
 * @property string|null $guarantor_name
 * @property string|null $guarantee_terms
 * @property bool $is_collateralized
 * @property string|null $collateral_description
 * @property numeric|null $collateral_value
 * @property string $risk_level
 * @property string|null $credit_rating
 * @property string|null $risk_disclosure
 * @property bool $is_public
 * @property bool $is_featured
 * @property int $priority_order
 * @property array<array-key, mixed>|null $terms_conditions
 * @property array<array-key, mixed>|null $disclosure_documents
 * @property array<array-key, mixed>|null $legal_documents
 * @property array<array-key, mixed>|null $financial_reports
 * @property array<array-key, mixed>|null $performance_metrics
 * @property array<array-key, mixed>|null $tags
 * @property string|null $notes
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $managed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BondDonation> $donations
 * @property-read int|null $donations_count
 * @property-read \App\Models\User|null $managedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond byCreditRating($creditRating)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond byRiskLevel($riskLevel)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond expiringSoon($days = 30)
 * @method static \Database\Factories\EnergyBondFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond highYield($minRate = '5')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond public()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereBondNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereBondType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereCollateralDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereCollateralValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereCreditRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereCurrentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereDisclosureDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereFaceValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereFinancialReports($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereFirstInterestDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereGuaranteeTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereGuarantorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereInterestFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereInterestRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereIsCollateralized($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereIsGuaranteed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereIsTaxFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereLastInterestPaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereLegalDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereManagedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereMaturityDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereMaximumInvestment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereMinimumInvestment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereNextInterestPaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereOutstandingPrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond wherePaidInterestPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond wherePaymentSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond wherePerformanceMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond wherePriorityOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereRiskDisclosure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereRiskLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereTermsConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereTotalInterestPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereTotalInterestPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereTotalUnitsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereUnitsIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereUnitsReserved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyBond whereUpdatedAt($value)
 */
	class EnergyBond extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $type
 * @property numeric $goal_kwh
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property string $reward_type
 * @property array<array-key, mixed>|null $reward_details
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $average_progress
 * @property-read mixed $duration
 * @property-read mixed $progress_percentage
 * @property-read mixed $status
 * @property-read mixed $status_label
 * @property-read mixed $time_until_end
 * @property-read mixed $time_until_start
 * @property-read mixed $total_participants
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $participants
 * @property-read int|null $participants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserChallengeProgress> $userProgress
 * @property-read int|null $user_progress_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge byRewardType($rewardType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge colectivo()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge current()
 * @method static \Database\Factories\EnergyChallengeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge individual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge past()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge upcoming()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereGoalKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereRewardDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereRewardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyChallenge withoutTrashed()
 */
	class EnergyChallenge extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $energy_contract_id
 * @property string|null $meter_id
 * @property \Illuminate\Support\Carbon $measurement_datetime
 * @property string $measurement_date
 * @property string $measurement_time
 * @property string $period_type
 * @property string $consumption_kwh
 * @property string|null $peak_power_kw
 * @property string|null $average_power_kw
 * @property string|null $power_factor
 * @property string|null $peak_hours_consumption
 * @property string|null $standard_hours_consumption
 * @property string|null $valley_hours_consumption
 * @property string|null $tariff_type
 * @property string|null $unit_price_eur_kwh
 * @property string|null $total_cost_eur
 * @property string $renewable_percentage
 * @property string $grid_consumption_kwh
 * @property string $self_consumption_kwh
 * @property string|null $voltage_v
 * @property string|null $frequency_hz
 * @property string|null $thd_voltage_percentage
 * @property string|null $thd_current_percentage
 * @property string|null $efficiency_percentage
 * @property string|null $estimated_co2_emissions_kg
 * @property string|null $carbon_intensity_kg_co2_kwh
 * @property string|null $vs_previous_period_percentage
 * @property string|null $vs_similar_users_percentage
 * @property string|null $efficiency_score
 * @property string|null $temperature_celsius
 * @property string|null $humidity_percentage
 * @property string|null $weather_condition
 * @property string|null $device_info
 * @property string $data_quality
 * @property int $is_estimated
 * @property string|null $estimation_method
 * @property int $consumption_alert_triggered
 * @property string|null $alert_threshold_kwh
 * @property string|null $alert_message
 * @property string|null $processed_at
 * @property string|null $processing_metadata
 * @property int $is_validated
 * @property string|null $validation_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EnergyContract|null $energyContract
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\EnergyConsumptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereAlertMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereAlertThresholdKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereAveragePowerKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereCarbonIntensityKgCo2Kwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereConsumptionAlertTriggered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereConsumptionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereDataQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereDeviceInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereEfficiencyPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereEfficiencyScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereEnergyContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereEstimatedCo2EmissionsKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereEstimationMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereFrequencyHz($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereGridConsumptionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereHumidityPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereIsEstimated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereIsValidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereMeasurementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereMeasurementDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereMeasurementTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereMeterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption wherePeakHoursConsumption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption wherePeakPowerKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption wherePowerFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereProcessingMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereRenewablePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereSelfConsumptionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereStandardHoursConsumption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereTariffType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereTemperatureCelsius($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereThdCurrentPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereThdVoltagePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereTotalCostEur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereUnitPriceEurKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereValidationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereValleyHoursConsumption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereVoltageV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereVsPreviousPeriodPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereVsSimilarUsersPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyConsumption whereWeatherCondition($value)
 */
	class EnergyConsumption extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $provider_id
 * @property int $product_id
 * @property string $contract_number
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property string $status
 * @property string $total_value
 * @property string|null $monthly_payment
 * @property string $currency
 * @property string|null $deposit_amount
 * @property bool $deposit_paid
 * @property string $contracted_power
 * @property string|null $estimated_annual_consumption
 * @property string $guaranteed_supply_percentage
 * @property string $green_energy_percentage
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property \Illuminate\Support\Carbon|null $signed_date
 * @property \Illuminate\Support\Carbon|null $activation_date
 * @property string|null $terms_conditions
 * @property array<array-key, mixed>|null $special_clauses
 * @property bool $auto_renewal
 * @property int|null $renewal_period_months
 * @property string|null $early_termination_fee
 * @property string $billing_frequency
 * @property string|null $next_billing_date
 * @property string|null $last_billing_date
 * @property string|null $performance_metrics
 * @property string|null $current_satisfaction_score
 * @property int $total_claims
 * @property int $resolved_claims
 * @property string|null $estimated_co2_reduction
 * @property array<array-key, mixed>|null $sustainability_certifications
 * @property bool $carbon_neutral
 * @property array<array-key, mixed>|null $custom_fields
 * @property string|null $attachments
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $terminated_at
 * @property int|null $terminated_by
 * @property string|null $termination_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Provider $provider
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\EnergyContractFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereActivationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereAutoRenewal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereBillingFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereCarbonNeutral($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereContractNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereContractedPower($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereCurrentSatisfactionScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereCustomFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereDepositAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereDepositPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereEarlyTerminationFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereEstimatedAnnualConsumption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereEstimatedCo2Reduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereGreenEnergyPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereGuaranteedSupplyPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereLastBillingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereMonthlyPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereNextBillingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract wherePerformanceMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereRenewalPeriodMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereResolvedClaims($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereSignedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereSpecialClauses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereSustainabilityCertifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereTerminatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereTerminatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereTerminationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereTermsConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereTotalClaims($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereTotalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyContract whereUserId($value)
 */
	class EnergyContract extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string|null $mission_statement
 * @property string|null $vision_statement
 * @property string|null $legal_name
 * @property string|null $tax_id
 * @property string|null $registration_number
 * @property string|null $legal_form
 * @property string $status
 * @property array<array-key, mixed>|null $contact_info
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state_province
 * @property string|null $postal_code
 * @property string $country
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property int|null $founder_id
 * @property int|null $administrator_id
 * @property \Illuminate\Support\Carbon|null $founded_date
 * @property \Illuminate\Support\Carbon|null $registration_date
 * @property \Illuminate\Support\Carbon|null $activation_date
 * @property int|null $max_members
 * @property int $current_members
 * @property numeric|null $membership_fee
 * @property string|null $membership_fee_frequency
 * @property bool $open_enrollment
 * @property string|null $enrollment_requirements
 * @property array<array-key, mixed>|null $energy_types
 * @property numeric|null $total_capacity_kw
 * @property numeric|null $available_capacity_kw
 * @property bool $allows_energy_sharing
 * @property bool $allows_trading
 * @property numeric|null $sharing_fee_percentage
 * @property string $currency
 * @property array<array-key, mixed>|null $payment_methods
 * @property bool $requires_deposit
 * @property numeric|null $deposit_amount
 * @property numeric $total_energy_shared_kwh
 * @property numeric $total_cost_savings_eur
 * @property numeric $total_co2_reduction_kg
 * @property int $total_projects
 * @property numeric|null $average_member_satisfaction
 * @property array<array-key, mixed>|null $settings
 * @property array<array-key, mixed>|null $notifications_config
 * @property string $timezone
 * @property string $language
 * @property array<array-key, mixed>|null $certifications
 * @property array<array-key, mixed>|null $sustainability_goals
 * @property string|null $achievements
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $notes
 * @property bool $is_featured
 * @property int $visibility_level
 * @property \Illuminate\Support\Carbon|null $last_activity_at
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property int|null $verified_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $administrator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyReport> $energyReports
 * @property-read int|null $energy_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergySharing> $energySharings
 * @property-read int|null $energy_sharings_count
 * @property-read \App\Models\User|null $founder
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PerformanceIndicator> $performanceIndicators
 * @property-read int|null $performance_indicators_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $refunds
 * @property-read int|null $refunds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SustainabilityMetric> $sustainabilityMetrics
 * @property-read int|null $sustainability_metrics_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserSubscription> $userSubscriptions
 * @property-read int|null $user_subscriptions_count
 * @property-read \App\Models\User|null $verifiedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WalletTransaction> $walletTransactions
 * @property-read int|null $wallet_transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative byCity($city)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative byCountry($country)
 * @method static \Database\Factories\EnergyCooperativeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative openEnrollment()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative verified()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereAchievements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereActivationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereAdministratorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereAllowsEnergySharing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereAllowsTrading($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereAvailableCapacityKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereAverageMemberSatisfaction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereCertifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereContactInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereCurrentMembers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereDepositAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereEnergyTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereEnrollmentRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereFoundedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereFounderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereLastActivityAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereLegalForm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereLegalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereMaxMembers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereMembershipFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereMembershipFeeFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereMissionStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereNotificationsConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereOpenEnrollment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative wherePaymentMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereRegistrationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereRequiresDeposit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereSharingFeePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereStateProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereSustainabilityGoals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereTotalCapacityKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereTotalCo2ReductionKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereTotalCostSavingsEur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereTotalEnergySharedKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereTotalProjects($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereVerifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereVisibilityLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyCooperative whereVisionStatement($value)
 */
	class EnergyCooperative extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $forecast_number
 * @property string $name
 * @property string|null $description
 * @property string $forecast_type
 * @property string $forecast_horizon
 * @property string $forecast_method
 * @property string $forecast_status
 * @property string $accuracy_level
 * @property numeric|null $accuracy_score
 * @property numeric|null $confidence_interval_lower
 * @property numeric|null $confidence_interval_upper
 * @property numeric|null $confidence_level
 * @property int|null $source_id
 * @property string|null $source_type
 * @property int|null $target_id
 * @property string|null $target_type
 * @property \Illuminate\Support\Carbon $forecast_start_time
 * @property \Illuminate\Support\Carbon $forecast_end_time
 * @property \Illuminate\Support\Carbon $generation_time
 * @property \Illuminate\Support\Carbon $valid_from
 * @property \Illuminate\Support\Carbon|null $valid_until
 * @property \Illuminate\Support\Carbon|null $expiry_time
 * @property string|null $time_zone
 * @property string|null $time_resolution
 * @property int|null $forecast_periods
 * @property numeric|null $total_forecasted_value
 * @property string|null $forecast_unit
 * @property numeric|null $baseline_value
 * @property numeric|null $trend_value
 * @property numeric|null $seasonal_value
 * @property numeric|null $cyclical_value
 * @property numeric|null $irregular_value
 * @property array<array-key, mixed>|null $forecast_data
 * @property array<array-key, mixed>|null $baseline_data
 * @property array<array-key, mixed>|null $trend_data
 * @property array<array-key, mixed>|null $seasonal_data
 * @property array<array-key, mixed>|null $cyclical_data
 * @property array<array-key, mixed>|null $irregular_data
 * @property array<array-key, mixed>|null $weather_data
 * @property array<array-key, mixed>|null $input_variables
 * @property array<array-key, mixed>|null $model_parameters
 * @property array<array-key, mixed>|null $validation_metrics
 * @property array<array-key, mixed>|null $performance_history
 * @property array<array-key, mixed>|null $tags
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $validated_by
 * @property \Illuminate\Support\Carbon|null $validated_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\EnergySource|null $source
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $target
 * @property-read \App\Models\User|null $validatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast archived()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byAccuracyLevel($accuracyLevel)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byAccuracyScore($minScore)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byConfidenceLevel($minConfidence)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byExpiryTime($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byForecastHorizon($forecastHorizon)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byForecastMethod($forecastMethod)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byForecastStatus($forecastStatus)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byForecastType($forecastType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byGenerationTime($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast bySource($sourceId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byTarget($targetId, $targetType = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byValidFrom($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast byValidUntil($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast consumption()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast daily()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast demand()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast draft()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast expertJudgment()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast expired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast expiredStatus()
 * @method static \Database\Factories\EnergyForecastFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast generation()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast highAccuracy()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast hourly()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast hybrid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast load()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast longTerm()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast lowAccuracy()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast machineLearning()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast mediumAccuracy()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast monthly()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast notExpired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast pendingValidation()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast physicalModel()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast price()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast quarterly()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast renewable()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast statistical()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast storage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast superseded()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast transmission()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast validated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast validatedStatus()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast weather()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast weekly()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereAccuracyLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereAccuracyScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereBaselineData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereBaselineValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereConfidenceIntervalLower($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereConfidenceIntervalUpper($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereConfidenceLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereCyclicalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereCyclicalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereExpiryTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereForecastData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereForecastEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereForecastHorizon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereForecastMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereForecastNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereForecastPeriods($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereForecastStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereForecastStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereForecastType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereForecastUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereGenerationTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereInputVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereIrregularData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereIrregularValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereModelParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast wherePerformanceHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereSeasonalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereSeasonalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereTimeResolution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereTimeZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereTotalForecastedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereTrendData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereTrendValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereValidatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereValidatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereValidationMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast whereWeatherData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyForecast yearly()
 */
	class EnergyForecast extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $installation_number
 * @property string $name
 * @property string|null $description
 * @property string $installation_type
 * @property string $status
 * @property string $priority
 * @property int $energy_source_id
 * @property int|null $customer_id
 * @property int|null $project_id
 * @property numeric $installed_capacity_kw
 * @property numeric $operational_capacity_kw
 * @property numeric $efficiency_rating
 * @property numeric $annual_production_kwh
 * @property numeric $monthly_production_kwh
 * @property numeric $daily_production_kwh
 * @property string $location_address
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property \Illuminate\Support\Carbon $installation_date
 * @property \Illuminate\Support\Carbon|null $commissioning_date
 * @property \Illuminate\Support\Carbon|null $warranty_expiry_date
 * @property numeric $installation_cost
 * @property numeric|null $operational_cost_per_kwh
 * @property numeric|null $maintenance_cost_per_kwh
 * @property string|null $technical_specifications
 * @property string|null $warranty_terms
 * @property string|null $maintenance_requirements
 * @property string|null $safety_features
 * @property array<array-key, mixed>|null $equipment_details
 * @property array<array-key, mixed>|null $maintenance_schedule
 * @property array<array-key, mixed>|null $performance_metrics
 * @property array<array-key, mixed>|null $warranty_documents
 * @property array<array-key, mixed>|null $installation_photos
 * @property array<array-key, mixed>|null $tags
 * @property int|null $installed_by
 * @property int|null $managed_by
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $customer
 * @property-read \App\Models\EnergySource $energySource
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyForecast> $forecasts
 * @property-read int|null $forecasts_count
 * @property-read \App\Models\User|null $installedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceTask> $maintenanceTasks
 * @property-read int|null $maintenance_tasks_count
 * @property-read \App\Models\User|null $managedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyMeter> $meters
 * @property-read int|null $meters_count
 * @property-read \App\Models\ProductionProject|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyReading> $readings
 * @property-read int|null $readings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation byCustomer($customerId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation byEnergySource($energySourceId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation byProject($projectId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation commercial()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation community()
 * @method static \Database\Factories\EnergyInstallationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation gridTied()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation industrial()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation maintenance()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation microgrid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation offGrid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation operational()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation residential()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation utilityScale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereAnnualProductionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereCommissioningDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereDailyProductionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereEfficiencyRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereEnergySourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereEquipmentDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereInstallationCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereInstallationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereInstallationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereInstallationPhotos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereInstallationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereInstalledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereInstalledCapacityKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereLocationAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereMaintenanceCostPerKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereMaintenanceRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereMaintenanceSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereManagedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereMonthlyProductionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereOperationalCapacityKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereOperationalCostPerKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation wherePerformanceMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereSafetyFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereTechnicalSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereWarrantyDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereWarrantyExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyInstallation whereWarrantyTerms($value)
 */
	class EnergyInstallation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $meter_number
 * @property string $name
 * @property string|null $description
 * @property string $meter_type
 * @property string $status
 * @property string $meter_category
 * @property string|null $manufacturer
 * @property string|null $model
 * @property string|null $serial_number
 * @property string|null $firmware_version
 * @property string|null $hardware_version
 * @property int|null $installation_id
 * @property int|null $consumption_point_id
 * @property int|null $customer_id
 * @property string|null $location_address
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property \Illuminate\Support\Carbon $installation_date
 * @property \Illuminate\Support\Carbon|null $commissioning_date
 * @property \Illuminate\Support\Carbon|null $last_calibration_date
 * @property \Illuminate\Support\Carbon|null $next_calibration_date
 * @property \Illuminate\Support\Carbon|null $warranty_expiry_date
 * @property numeric|null $voltage_rating
 * @property string|null $voltage_unit
 * @property numeric|null $current_rating
 * @property string|null $current_unit
 * @property string|null $phase_type
 * @property string|null $connection_type
 * @property numeric|null $accuracy_class
 * @property numeric|null $measurement_range_min
 * @property numeric|null $measurement_range_max
 * @property string|null $measurement_unit
 * @property numeric|null $pulse_constant
 * @property string|null $pulse_unit
 * @property bool $is_smart_meter
 * @property bool $has_remote_reading
 * @property bool $has_two_way_communication
 * @property string|null $communication_protocol
 * @property string|null $communication_frequency
 * @property string|null $data_logging_interval
 * @property int|null $data_retention_days
 * @property string|null $technical_specifications
 * @property string|null $calibration_requirements
 * @property string|null $maintenance_requirements
 * @property string|null $safety_features
 * @property array<array-key, mixed>|null $meter_features
 * @property array<array-key, mixed>|null $communication_settings
 * @property array<array-key, mixed>|null $alarm_settings
 * @property array<array-key, mixed>|null $data_formats
 * @property array<array-key, mixed>|null $tags
 * @property int|null $installed_by
 * @property int|null $managed_by
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\ConsumptionPoint|null $consumptionPoint
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyForecast> $forecasts
 * @property-read int|null $forecasts_count
 * @property-read \App\Models\EnergyInstallation|null $installation
 * @property-read \App\Models\User|null $installedBy
 * @property-read \App\Models\User|null $managedBy
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $meterable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyReading> $readings
 * @property-read int|null $readings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter byConsumptionPoint($consumptionPointId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter byCustomer($customerId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter byInstallation($installationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter byManufacturer($manufacturer)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter byMeterCategory($meterCategory)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter byMeterNumber($meterNumber)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter byMeterType($meterType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter byModel($model)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter bySerialNumber($serialNumber)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter calibrating()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter compressedAir()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter electricity()
 * @method static \Database\Factories\EnergyMeterFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter faulty()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter gas()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter heat()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter maintenance()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter needsCalibration()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter remoteReading()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter smartMeters()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter steam()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter twoWayCommunication()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter water()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereAccuracyClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereAlarmSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereCalibrationRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereCommissioningDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereCommunicationFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereCommunicationProtocol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereCommunicationSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereConnectionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereConsumptionPointId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereCurrentRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereCurrentUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereDataFormats($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereDataLoggingInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereDataRetentionDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereFirmwareVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereHardwareVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereHasRemoteReading($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereHasTwoWayCommunication($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereInstallationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereInstallationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereInstalledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereIsSmartMeter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereLastCalibrationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereLocationAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereMaintenanceRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereManagedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereMeasurementRangeMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereMeasurementRangeMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereMeasurementUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereMeterCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereMeterFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereMeterNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereMeterType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereNextCalibrationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter wherePhaseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter wherePulseConstant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter wherePulseUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereSafetyFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereTechnicalSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereVoltageRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereVoltageUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter whereWarrantyExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyMeter withoutTrashed()
 */
	class EnergyMeter extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $pool_number
 * @property string $name
 * @property string|null $description
 * @property string $pool_type
 * @property string $status
 * @property string $energy_category
 * @property numeric $total_capacity_mw
 * @property numeric $available_capacity_mw
 * @property numeric $reserved_capacity_mw
 * @property numeric $utilized_capacity_mw
 * @property numeric $efficiency_rating
 * @property numeric $availability_factor
 * @property numeric $capacity_factor
 * @property numeric $annual_production_mwh
 * @property numeric $monthly_production_mwh
 * @property numeric $daily_production_mwh
 * @property numeric $hourly_production_mwh
 * @property string|null $location_address
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $region
 * @property string|null $country
 * @property \Illuminate\Support\Carbon|null $commissioning_date
 * @property \Illuminate\Support\Carbon|null $decommissioning_date
 * @property int|null $expected_lifespan_years
 * @property numeric|null $construction_cost
 * @property numeric|null $operational_cost_per_mwh
 * @property numeric|null $maintenance_cost_per_mwh
 * @property string|null $technical_specifications
 * @property string|null $environmental_impact
 * @property string|null $regulatory_compliance
 * @property string|null $safety_features
 * @property array<array-key, mixed>|null $pool_members
 * @property array<array-key, mixed>|null $pool_operators
 * @property array<array-key, mixed>|null $pool_governance
 * @property array<array-key, mixed>|null $trading_rules
 * @property array<array-key, mixed>|null $settlement_procedures
 * @property array<array-key, mixed>|null $risk_management
 * @property array<array-key, mixed>|null $performance_metrics
 * @property array<array-key, mixed>|null $environmental_data
 * @property array<array-key, mixed>|null $regulatory_documents
 * @property array<array-key, mixed>|null $tags
 * @property int|null $managed_by
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyForecast> $forecasts
 * @property-read int|null $forecasts_count
 * @property-read \App\Models\User|null $managedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyTradingOrder> $tradingOrders
 * @property-read int|null $trading_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyTransfer> $transfers
 * @property-read int|null $transfers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool ancillary()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool balancing()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool byCountry($country)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool byEnergyCategory($energyCategory)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool byManagedBy($managedBy)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool byPoolType($poolType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool byRegion($region)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool capacity()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool closed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool demand()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool demandResponse()
 * @method static \Database\Factories\EnergyPoolFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool highAvailability($minAvailability = 90)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool highCapacityFactor($minCapacityFactor = 70)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool highEfficiency($minEfficiency = 80)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool hybrid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool maintenance()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool nonRenewable()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool planned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool renewable()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool reserve()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool storage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool suspended()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool trading()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool virtual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereAnnualProductionMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereAvailabilityFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereAvailableCapacityMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereCapacityFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereCommissioningDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereConstructionCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereDailyProductionMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereDecommissioningDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereEfficiencyRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereEnergyCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereEnvironmentalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereEnvironmentalImpact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereExpectedLifespanYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereHourlyProductionMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereLocationAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereMaintenanceCostPerMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereManagedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereMonthlyProductionMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereOperationalCostPerMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool wherePerformanceMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool wherePoolGovernance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool wherePoolMembers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool wherePoolNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool wherePoolOperators($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool wherePoolType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereRegulatoryCompliance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereRegulatoryDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereReservedCapacityMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereRiskManagement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereSafetyFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereSettlementProcedures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereTechnicalSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereTotalCapacityMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereTradingRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyPool whereUtilizedCapacityMw($value)
 */
	class EnergyPool extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $provider_id
 * @property int|null $user_asset_id
 * @property int|null $energy_storage_id
 * @property string|null $system_id
 * @property string|null $inverter_id
 * @property \Illuminate\Support\Carbon $production_datetime
 * @property string $production_date
 * @property string $production_time
 * @property string $period_type
 * @property string $energy_source
 * @property string $production_kwh
 * @property string|null $peak_power_kw
 * @property string|null $average_power_kw
 * @property string|null $instantaneous_power_kw
 * @property string $self_consumption_kwh
 * @property string $grid_injection_kwh
 * @property string $storage_charge_kwh
 * @property string $curtailed_kwh
 * @property string|null $system_efficiency
 * @property string|null $inverter_efficiency
 * @property string|null $performance_ratio
 * @property string|null $capacity_factor
 * @property string|null $irradiance_w_m2
 * @property string|null $wind_speed_ms
 * @property string|null $wind_direction_degrees
 * @property string|null $ambient_temperature
 * @property string|null $module_temperature
 * @property string|null $humidity_percentage
 * @property string|null $atmospheric_pressure
 * @property string|null $feed_in_tariff_eur_kwh
 * @property string|null $market_price_eur_kwh
 * @property string|null $revenue_eur
 * @property string|null $savings_eur
 * @property string|null $voltage_v
 * @property string|null $frequency_hz
 * @property string|null $power_factor
 * @property string|null $thd_percentage
 * @property string|null $forecasted_production_kwh
 * @property string|null $forecast_accuracy_percentage
 * @property string|null $forecast_model_used
 * @property string|null $co2_avoided_kg
 * @property string|null $carbon_intensity_avoided
 * @property string $renewable_percentage
 * @property string $operational_status
 * @property string|null $status_notes
 * @property int $underperformance_alert
 * @property string|null $underperformance_threshold
 * @property string|null $system_alerts
 * @property string|null $error_codes
 * @property int $cleaning_required
 * @property string|null $last_cleaning_date
 * @property string|null $soiling_losses_percentage
 * @property string|null $shading_losses_percentage
 * @property string|null $inverter_data
 * @property string|null $inverter_temperature
 * @property string|null $inverter_status
 * @property string $data_quality
 * @property int $is_validated
 * @property string|null $validation_notes
 * @property string|null $measurement_metadata
 * @property string|null $processed_at
 * @property string|null $data_source
 * @property string|null $processing_flags
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\UserAsset|null $userAsset
 * @method static \Database\Factories\EnergyProductionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereAmbientTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereAtmosphericPressure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereAveragePowerKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereCapacityFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereCarbonIntensityAvoided($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereCleaningRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereCo2AvoidedKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereCurtailedKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereDataQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereDataSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereEnergySource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereEnergyStorageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereErrorCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereFeedInTariffEurKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereForecastAccuracyPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereForecastModelUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereForecastedProductionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereFrequencyHz($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereGridInjectionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereHumidityPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereInstantaneousPowerKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereInverterData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereInverterEfficiency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereInverterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereInverterStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereInverterTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereIrradianceWM2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereIsValidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereLastCleaningDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereMarketPriceEurKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereMeasurementMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereModuleTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereOperationalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction wherePeakPowerKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction wherePerformanceRatio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction wherePowerFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereProcessingFlags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereProductionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereProductionDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereProductionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereProductionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereRenewablePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereRevenueEur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereSavingsEur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereSelfConsumptionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereShadingLossesPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereSoilingLossesPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereStatusNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereStorageChargeKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereSystemAlerts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereSystemEfficiency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereThdPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereUnderperformanceAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereUnderperformanceThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereUserAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereValidationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereVoltageV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereWindDirectionDegrees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyProduction whereWindSpeedMs($value)
 */
	class EnergyProduction extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $reading_number
 * @property int $meter_id
 * @property int|null $installation_id
 * @property int|null $consumption_point_id
 * @property int|null $customer_id
 * @property string $reading_type
 * @property string $reading_source
 * @property string $reading_status
 * @property \Illuminate\Support\Carbon $reading_timestamp
 * @property string|null $reading_period
 * @property numeric $reading_value
 * @property string $reading_unit
 * @property numeric|null $previous_reading_value
 * @property numeric|null $consumption_value
 * @property string|null $consumption_unit
 * @property numeric|null $demand_value
 * @property string|null $demand_unit
 * @property numeric|null $power_factor
 * @property numeric|null $voltage_value
 * @property string|null $voltage_unit
 * @property numeric|null $current_value
 * @property string|null $current_unit
 * @property numeric|null $frequency_value
 * @property string|null $frequency_unit
 * @property numeric|null $temperature
 * @property string|null $temperature_unit
 * @property numeric|null $humidity
 * @property string|null $humidity_unit
 * @property numeric|null $quality_score
 * @property string|null $quality_notes
 * @property string|null $validation_notes
 * @property string|null $correction_notes
 * @property array<array-key, mixed>|null $raw_data
 * @property array<array-key, mixed>|null $processed_data
 * @property array<array-key, mixed>|null $alarms
 * @property array<array-key, mixed>|null $events
 * @property array<array-key, mixed>|null $tags
 * @property int|null $read_by
 * @property int|null $validated_by
 * @property \Illuminate\Support\Carbon|null $validated_at
 * @property int|null $corrected_by
 * @property \Illuminate\Support\Carbon|null $corrected_at
 * @property int $created_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\ConsumptionPoint|null $consumptionPoint
 * @property-read \App\Models\User|null $correctedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $customer
 * @property-read \App\Models\EnergyInstallation|null $installation
 * @property-read \App\Models\EnergyMeter $meter
 * @property-read \App\Models\User|null $readBy
 * @property-read \App\Models\User|null $validatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading automatic()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byConsumptionPoint($consumptionPointId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byCustomer($customerId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byHour($hour)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byInstallation($installationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byMeter($meterId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byReadingSource($readingSource)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byReadingStatus($readingStatus)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byReadingType($readingType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading calculated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading corrected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading cumulative()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading current()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading demand()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading energy()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading estimated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading estimatedSource()
 * @method static \Database\Factories\EnergyReadingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading frequency()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading hasCorrection()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading highQuality($minScore = 80)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading imported()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading instantaneous()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading interval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading invalid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading lowQuality($maxScore = 50)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading manual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading missing()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading powerFactor()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading remote()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading suspicious()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading thisMonth()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading thisWeek()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading thisYear()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading today()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading unvalidated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading valid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading validated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading voltage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereAlarms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereConsumptionPointId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereConsumptionUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereConsumptionValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereCorrectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereCorrectedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereCorrectionNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereCurrentUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereCurrentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereDemandUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereDemandValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereEvents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereFrequencyUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereFrequencyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereHumidity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereHumidityUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereInstallationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereMeterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading wherePowerFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading wherePreviousReadingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereProcessedData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereQualityNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereQualityScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereRawData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereReadBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereReadingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereReadingPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereReadingSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereReadingStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereReadingTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereReadingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereReadingUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereReadingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereTemperatureUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereValidatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereValidatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereValidationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereVoltageUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading whereVoltageValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReading yesterday()
 */
	class EnergyReading extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $report_code
 * @property string|null $description
 * @property string $report_type
 * @property string $report_category
 * @property string $scope
 * @property array<array-key, mixed>|null $scope_filters
 * @property \Illuminate\Support\Carbon $period_start
 * @property \Illuminate\Support\Carbon $period_end
 * @property string $period_type
 * @property string $generation_frequency
 * @property string|null $generation_time
 * @property array<array-key, mixed>|null $generation_config
 * @property bool $auto_generate
 * @property bool $send_notifications
 * @property array<array-key, mixed>|null $notification_recipients
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $generated_at
 * @property \Illuminate\Support\Carbon|null $scheduled_for
 * @property int $generation_attempts
 * @property string|null $generation_error
 * @property int|null $file_size_bytes
 * @property array<array-key, mixed>|null $data_summary
 * @property array<array-key, mixed>|null $metrics
 * @property array<array-key, mixed>|null $charts_config
 * @property array<array-key, mixed>|null $tables_data
 * @property string|null $insights
 * @property string|null $recommendations
 * @property string|null $pdf_path
 * @property string|null $excel_path
 * @property string|null $csv_path
 * @property string|null $json_path
 * @property array<array-key, mixed>|null $export_formats
 * @property array<array-key, mixed>|null $dashboard_config
 * @property bool $is_public
 * @property string|null $public_share_token
 * @property \Illuminate\Support\Carbon|null $public_expires_at
 * @property array<array-key, mixed>|null $access_permissions
 * @property int|null $total_records_processed
 * @property numeric|null $processing_time_seconds
 * @property int|null $data_quality_score
 * @property array<array-key, mixed>|null $data_sources
 * @property bool $include_comparison
 * @property \Illuminate\Support\Carbon|null $comparison_period_start
 * @property \Illuminate\Support\Carbon|null $comparison_period_end
 * @property array<array-key, mixed>|null $comparison_metrics
 * @property int|null $user_id
 * @property int|null $energy_cooperative_id
 * @property int $created_by_id
 * @property int|null $template_id
 * @property bool $cache_enabled
 * @property int $cache_duration_minutes
 * @property \Illuminate\Support\Carbon|null $cache_expires_at
 * @property string|null $cache_key
 * @property int $view_count
 * @property int $download_count
 * @property \Illuminate\Support\Carbon|null $last_viewed_at
 * @property \Illuminate\Support\Carbon|null $last_downloaded_at
 * @property array<array-key, mixed>|null $viewer_stats
 * @property array<array-key, mixed>|null $tags
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $notes
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\EnergyCooperative|null $energyCooperative
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PerformanceIndicator> $performanceIndicators
 * @property-read int|null $performance_indicators_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SustainabilityMetric> $sustainabilityMetrics
 * @property-read int|null $sustainability_metrics_count
 * @property-read EnergyReport|null $template
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport autoGenerate()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport byCategory(string $category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport byPeriod(\Carbon\Carbon $start, \Carbon\Carbon $end)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport byScope(string $scope)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport byStatus(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport byType(string $type)
 * @method static \Database\Factories\EnergyReportFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport public()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport scheduledForGeneration()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereAccessPermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereAutoGenerate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereCacheDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereCacheEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereCacheExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereCacheKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereChartsConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereComparisonMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereComparisonPeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereComparisonPeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereCsvPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereDashboardConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereDataQualityScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereDataSources($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereDataSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereDownloadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereEnergyCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereExcelPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereExportFormats($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereFileSizeBytes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereGeneratedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereGenerationAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereGenerationConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereGenerationError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereGenerationFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereGenerationTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereIncludeComparison($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereInsights($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereJsonPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereLastDownloadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereLastViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereNotificationRecipients($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport wherePdfPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereProcessingTimeSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport wherePublicExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport wherePublicShareToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereRecommendations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereReportCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereReportCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereReportType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereScheduledFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereScopeFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereSendNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereTablesData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereTotalRecordsProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereViewCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyReport whereViewerStats($value)
 */
	class EnergyReport extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $provider_user_id
 * @property int $consumer_user_id
 * @property int|null $energy_cooperative_id
 * @property string $sharing_code
 * @property string $title
 * @property string|null $description
 * @property string $sharing_type
 * @property string $status
 * @property numeric $energy_amount_kwh
 * @property numeric $energy_delivered_kwh
 * @property numeric $energy_remaining_kwh
 * @property string|null $energy_source
 * @property bool $is_renewable
 * @property numeric|null $renewable_percentage
 * @property \Illuminate\Support\Carbon $sharing_start_datetime
 * @property \Illuminate\Support\Carbon $sharing_end_datetime
 * @property \Illuminate\Support\Carbon $proposal_expiry_datetime
 * @property int $duration_hours
 * @property array<array-key, mixed>|null $time_slots
 * @property bool $flexible_timing
 * @property numeric $price_per_kwh
 * @property numeric $total_amount
 * @property numeric $platform_fee
 * @property numeric $cooperative_fee
 * @property numeric $net_amount
 * @property string $currency
 * @property string $payment_method
 * @property numeric|null $quality_score
 * @property numeric|null $reliability_score
 * @property numeric|null $delivery_efficiency
 * @property int $interruptions_count
 * @property numeric|null $average_voltage
 * @property numeric|null $frequency_stability
 * @property numeric|null $max_distance_km
 * @property numeric|null $actual_distance_km
 * @property array<array-key, mixed>|null $grid_connection_details
 * @property bool $requires_grid_approval
 * @property string|null $grid_operator
 * @property string|null $connection_type
 * @property array<array-key, mixed>|null $provider_preferences
 * @property array<array-key, mixed>|null $consumer_preferences
 * @property array<array-key, mixed>|null $technical_requirements
 * @property string|null $special_conditions
 * @property bool $allows_partial_delivery
 * @property numeric|null $min_delivery_kwh
 * @property numeric $co2_reduction_kg
 * @property numeric|null $environmental_impact_score
 * @property array<array-key, mixed>|null $sustainability_metrics
 * @property bool $certified_green_energy
 * @property string|null $certification_number
 * @property array<array-key, mixed>|null $monitoring_data
 * @property \Illuminate\Support\Carbon|null $last_monitoring_update
 * @property int $monitoring_frequency_minutes
 * @property bool $real_time_tracking
 * @property array<array-key, mixed>|null $alerts_configuration
 * @property string|null $dispute_reason
 * @property string|null $dispute_resolution
 * @property int|null $mediator_id
 * @property \Illuminate\Support\Carbon|null $dispute_opened_at
 * @property \Illuminate\Support\Carbon|null $dispute_resolved_at
 * @property numeric|null $provider_rating
 * @property numeric|null $consumer_rating
 * @property string|null $provider_feedback
 * @property string|null $consumer_feedback
 * @property bool|null $would_repeat
 * @property \Illuminate\Support\Carbon|null $payment_due_date
 * @property \Illuminate\Support\Carbon|null $payment_completed_at
 * @property string $payment_status
 * @property string|null $payment_transaction_id
 * @property array<array-key, mixed>|null $payment_details
 * @property array<array-key, mixed>|null $metadata
 * @property array<array-key, mixed>|null $integration_data
 * @property string|null $external_reference
 * @property array<array-key, mixed>|null $tags
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $proposed_at
 * @property \Illuminate\Support\Carbon|null $accepted_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $consumerUser
 * @property-read \App\Models\EnergyCooperative|null $energyCooperative
 * @property-read \App\Models\User|null $mediator
 * @property-read \App\Models\User $providerUser
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing byConsumer($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing byProvider($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing byUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing certifiedGreen()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing disputed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing expiringSoon($hours = 24)
 * @method static \Database\Factories\EnergySharingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing inProgress()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing renewable()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereActualDistanceKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereAlertsConfiguration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereAllowsPartialDelivery($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereAverageVoltage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereCertificationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereCertifiedGreenEnergy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereCo2ReductionKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereConnectionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereConsumerFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereConsumerPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereConsumerRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereConsumerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereCooperativeFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereDeliveryEfficiency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereDisputeOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereDisputeReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereDisputeResolution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereDisputeResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereDurationHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereEnergyAmountKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereEnergyCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereEnergyDeliveredKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereEnergyRemainingKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereEnergySource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereEnvironmentalImpactScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereExternalReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereFlexibleTiming($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereFrequencyStability($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereGridConnectionDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereGridOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereIntegrationData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereInterruptionsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereIsRenewable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereLastMonitoringUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereMaxDistanceKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereMediatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereMinDeliveryKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereMonitoringData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereMonitoringFrequencyMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing wherePaymentCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing wherePaymentDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing wherePaymentDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing wherePaymentTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing wherePlatformFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing wherePricePerKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereProposalExpiryDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereProposedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereProviderFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereProviderPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereProviderRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereProviderUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereQualityScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereRealTimeTracking($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereReliabilityScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereRenewablePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereRequiresGridApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereSharingCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereSharingEndDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereSharingStartDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereSharingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereSpecialConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereSustainabilityMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereTechnicalRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereTimeSlots($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySharing whereWouldRepeat($value)
 */
	class EnergySharing extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $source_type
 * @property string $status
 * @property string $energy_category
 * @property numeric $installed_capacity_mw
 * @property numeric $operational_capacity_mw
 * @property numeric $efficiency_rating
 * @property numeric $availability_factor
 * @property numeric $capacity_factor
 * @property numeric $annual_production_mwh
 * @property numeric $monthly_production_mwh
 * @property numeric $daily_production_mwh
 * @property numeric $hourly_production_mwh
 * @property string|null $location_address
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $region
 * @property string|null $country
 * @property \Illuminate\Support\Carbon|null $commissioning_date
 * @property \Illuminate\Support\Carbon|null $decommissioning_date
 * @property int|null $expected_lifespan_years
 * @property numeric|null $construction_cost
 * @property numeric|null $operational_cost_per_mwh
 * @property numeric|null $maintenance_cost_per_mwh
 * @property string|null $technical_specifications
 * @property string|null $environmental_impact
 * @property string|null $regulatory_compliance
 * @property string|null $safety_features
 * @property array<array-key, mixed>|null $equipment_details
 * @property array<array-key, mixed>|null $maintenance_schedule
 * @property array<array-key, mixed>|null $performance_metrics
 * @property array<array-key, mixed>|null $environmental_data
 * @property array<array-key, mixed>|null $regulatory_documents
 * @property array<array-key, mixed>|null $tags
 * @property int|null $managed_by
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyForecast> $forecasts
 * @property-read int|null $forecasts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyInstallation> $installations
 * @property-read int|null $installations_count
 * @property-read \App\Models\User|null $managedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyMeter> $meters
 * @property-read int|null $meters_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionProject> $productionProjects
 * @property-read int|null $production_projects_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyReading> $readings
 * @property-read int|null $readings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource byCategory($category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource byCountry($country)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource byRegion($region)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource byType($type)
 * @method static \Database\Factories\EnergySourceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource highCapacity($minCapacity = 100)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource highEfficiency($minEfficiency = 80)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource hybrid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource maintenance()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource nonRenewable()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource operational()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource renewable()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource underConstruction()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereAnnualProductionMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereAvailabilityFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereCapacityFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereCommissioningDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereConstructionCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereDailyProductionMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereDecommissioningDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereEfficiencyRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereEnergyCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereEnvironmentalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereEnvironmentalImpact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereEquipmentDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereExpectedLifespanYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereHourlyProductionMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereInstalledCapacityMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereLocationAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereMaintenanceCostPerMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereMaintenanceSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereManagedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereMonthlyProductionMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereOperationalCapacityMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereOperationalCostPerMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource wherePerformanceMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereRegulatoryCompliance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereRegulatoryDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereSafetyFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereTechnicalSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergySource whereUpdatedAt($value)
 */
	class EnergySource extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $provider_id
 * @property int|null $user_asset_id
 * @property string $system_id
 * @property string $name
 * @property string|null $description
 * @property string $storage_type
 * @property string|null $technology_details
 * @property string|null $manufacturer
 * @property string|null $model
 * @property string|null $installation_year
 * @property string $capacity_kwh
 * @property string $usable_capacity_kwh
 * @property string $max_charge_power_kw
 * @property string $max_discharge_power_kw
 * @property string $current_charge_kwh
 * @property string $charge_level_percentage
 * @property string $status
 * @property string|null $round_trip_efficiency
 * @property string|null $charge_efficiency
 * @property string|null $discharge_efficiency
 * @property int $cycle_count
 * @property string|null $depth_of_discharge_avg
 * @property int|null $expected_lifecycle_years
 * @property int|null $expected_cycles
 * @property string $capacity_degradation_percentage
 * @property string $current_health_percentage
 * @property string|null $installation_cost
 * @property string|null $maintenance_cost_annual
 * @property string|null $replacement_cost
 * @property string $currency
 * @property string $min_charge_level
 * @property string $max_charge_level
 * @property int $auto_management_enabled
 * @property string|null $charge_schedule
 * @property string|null $discharge_schedule
 * @property int $grid_tied
 * @property int $islanding_capable
 * @property string|null $feed_in_tariff_eur_kwh
 * @property string|null $time_of_use_optimization
 * @property string|null $operating_temp_min
 * @property string|null $operating_temp_max
 * @property string|null $current_temperature
 * @property string|null $humidity_percentage
 * @property string|null $location_description
 * @property string|null $safety_systems
 * @property int $fire_suppression
 * @property int $theft_protection
 * @property string|null $protective_devices
 * @property int $remote_monitoring
 * @property int $remote_control
 * @property string|null $monitoring_system
 * @property string|null $communication_protocols
 * @property string|null $co2_footprint_manufacturing_kg
 * @property string|null $co2_savings_annual_kg
 * @property int $recyclable_materials
 * @property string|null $recycling_percentage
 * @property string|null $last_maintenance_date
 * @property \Illuminate\Support\Carbon|null $next_maintenance_date
 * @property int $maintenance_interval_months
 * @property string|null $maintenance_notes
 * @property \Illuminate\Support\Carbon|null $warranty_end_date
 * @property string|null $warranty_provider
 * @property string|null $insurance_value
 * @property string|null $insurance_expiry_date
 * @property string|null $technical_specifications
 * @property string|null $certifications
 * @property string|null $custom_fields
 * @property string|null $notes
 * @property bool $is_active
 * @property string|null $commissioned_at
 * @property string|null $decommissioned_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Provider $provider
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\EnergyStorageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereAutoManagementEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCapacityDegradationPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCapacityKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCertifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereChargeEfficiency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereChargeLevelPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereChargeSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCo2FootprintManufacturingKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCo2SavingsAnnualKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCommissionedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCommunicationProtocols($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCurrentChargeKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCurrentHealthPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCurrentTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCustomFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereCycleCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereDecommissionedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereDepthOfDischargeAvg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereDischargeEfficiency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereDischargeSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereExpectedCycles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereExpectedLifecycleYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereFeedInTariffEurKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereFireSuppression($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereGridTied($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereHumidityPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereInstallationCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereInstallationYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereInsuranceExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereInsuranceValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereIslandingCapable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereLastMaintenanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereLocationDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereMaintenanceCostAnnual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereMaintenanceIntervalMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereMaintenanceNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereMaxChargeLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereMaxChargePowerKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereMaxDischargePowerKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereMinChargeLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereMonitoringSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereNextMaintenanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereOperatingTempMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereOperatingTempMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereProtectiveDevices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereRecyclableMaterials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereRecyclingPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereRemoteControl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereRemoteMonitoring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereReplacementCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereRoundTripEfficiency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereSafetySystems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereStorageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereTechnicalSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereTechnologyDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereTheftProtection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereTimeOfUseOptimization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereUsableCapacityKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereUserAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereWarrantyEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyStorage whereWarrantyProvider($value)
 */
	class EnergyStorage extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $order_number
 * @property string $order_type
 * @property string $order_status
 * @property string $order_side
 * @property int $trader_id
 * @property int|null $pool_id
 * @property int|null $counterparty_id
 * @property numeric $quantity_mwh
 * @property numeric $filled_quantity_mwh
 * @property numeric $remaining_quantity_mwh
 * @property numeric $price_per_mwh
 * @property numeric $total_value
 * @property numeric $filled_value
 * @property numeric $remaining_value
 * @property string $price_type
 * @property string|null $price_index
 * @property numeric|null $price_adjustment
 * @property \Illuminate\Support\Carbon $valid_from
 * @property \Illuminate\Support\Carbon|null $valid_until
 * @property \Illuminate\Support\Carbon|null $execution_time
 * @property \Illuminate\Support\Carbon|null $expiry_time
 * @property string $execution_type
 * @property string $priority
 * @property bool $is_negotiable
 * @property string|null $negotiation_terms
 * @property string|null $special_conditions
 * @property string|null $delivery_requirements
 * @property string|null $payment_terms
 * @property array<array-key, mixed>|null $order_conditions
 * @property array<array-key, mixed>|null $order_restrictions
 * @property array<array-key, mixed>|null $order_metadata
 * @property array<array-key, mixed>|null $tags
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $executed_by
 * @property \Illuminate\Support\Carbon|null $executed_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User|null $counterparty
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $executedBy
 * @property-read \App\Models\EnergyPool|null $pool
 * @property-read \App\Models\User $trader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder activeStatus()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder allOrNothing()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder ask()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder bid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder buy()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byCounterparty($counterpartyId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byExecutionTime($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byExecutionType($executionType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byExpiryTime($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byOrderSide($orderSide)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byOrderStatus($orderStatus)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byOrderType($orderType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byPool($poolId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byPriceRange($minPrice, $maxPrice)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byPriceType($priceType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byQuantityRange($minQuantity, $maxQuantity)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byTrader($traderId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byValidFrom($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder byValidUntil($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder executed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder expired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder expiredStatus()
 * @method static \Database\Factories\EnergyTradingOrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder fillOrKill()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder filled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder fixedPrice()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder floatingPrice()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder formulaPrice()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder goodTillCancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder goodTillDate()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder immediateExecution()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder indexedPrice()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder limit()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder market()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder negotiable()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder notExecuted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder notExpired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder partiallyFilled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder sell()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder stop()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder stopLimit()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereCounterpartyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereDeliveryRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereExecutedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereExecutedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereExecutionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereExecutionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereExpiryTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereFilledQuantityMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereFilledValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereIsNegotiable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereNegotiationTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereOrderConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereOrderMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereOrderRestrictions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereOrderSide($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereOrderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder wherePoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder wherePriceAdjustment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder wherePriceIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder wherePricePerMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder wherePriceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereQuantityMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereRemainingQuantityMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereRemainingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereSpecialConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereTotalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereTraderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTradingOrder whereValidUntil($value)
 */
	class EnergyTradingOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $transfer_number
 * @property string $name
 * @property string|null $description
 * @property string $transfer_type
 * @property string $status
 * @property string $priority
 * @property int|null $source_id
 * @property string|null $source_type
 * @property int|null $destination_id
 * @property string|null $destination_type
 * @property int|null $source_meter_id
 * @property int|null $destination_meter_id
 * @property numeric $transfer_amount_kwh
 * @property numeric $transfer_amount_mwh
 * @property numeric|null $transfer_rate_kw
 * @property numeric|null $transfer_rate_mw
 * @property string|null $transfer_unit
 * @property \Illuminate\Support\Carbon $scheduled_start_time
 * @property \Illuminate\Support\Carbon $scheduled_end_time
 * @property \Illuminate\Support\Carbon|null $actual_start_time
 * @property \Illuminate\Support\Carbon|null $actual_end_time
 * @property \Illuminate\Support\Carbon|null $completion_time
 * @property numeric|null $duration_hours
 * @property numeric|null $efficiency_percentage
 * @property numeric|null $loss_percentage
 * @property numeric|null $loss_amount_kwh
 * @property numeric $net_transfer_amount_kwh
 * @property numeric $net_transfer_amount_mwh
 * @property numeric|null $cost_per_kwh
 * @property numeric|null $total_cost
 * @property string $currency
 * @property numeric $exchange_rate
 * @property string|null $transfer_method
 * @property string|null $transfer_medium
 * @property string|null $transfer_protocol
 * @property bool $is_automated
 * @property bool $requires_approval
 * @property bool $is_approved
 * @property bool $is_verified
 * @property string|null $transfer_conditions
 * @property string|null $safety_requirements
 * @property string|null $quality_standards
 * @property array<array-key, mixed>|null $transfer_parameters
 * @property array<array-key, mixed>|null $monitoring_data
 * @property array<array-key, mixed>|null $alarm_settings
 * @property array<array-key, mixed>|null $event_logs
 * @property array<array-key, mixed>|null $performance_metrics
 * @property array<array-key, mixed>|null $tags
 * @property int|null $scheduled_by
 * @property int|null $initiated_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $verified_by
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property int|null $completed_by
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int $created_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User|null $completedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $destination
 * @property-read \App\Models\EnergyMeter|null $destinationMeter
 * @property-read \App\Models\User|null $initiatedBy
 * @property-read \App\Models\User|null $scheduledBy
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $source
 * @property-read \App\Models\EnergyMeter|null $sourceMeter
 * @property-read \App\Models\User|null $verifiedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer automated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byActualEndTime($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byActualStartTime($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byAmountRange($minAmount, $maxAmount)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byCompletionTime($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byCostRange($minCost, $maxCost)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byCurrency($currency)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byDestination($destinationId, $destinationType = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byDestinationMeter($meterId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byEfficiencyRange($minEfficiency, $maxEfficiency)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byLossRange($minLoss, $maxLoss)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byScheduledEndTime($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byScheduledStartTime($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer bySource($sourceId, $sourceType = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer bySourceMeter($meterId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer byTransferType($transferType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer consumption()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer contractual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer dueSoon($hours = 24)
 * @method static \Database\Factories\EnergyTransferFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer failed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer generation()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer gridExport()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer gridImport()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer inProgress()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer lowPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer manual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer normalPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer notApproved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer notVerified()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer onHold()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer peerToPeer()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer physical()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer requiresApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer reversed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer scheduled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer storage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer verified()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer virtual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereActualEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereActualStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereAlarmSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereCompletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereCompletionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereCostPerKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereDestinationMeterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereDestinationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereDurationHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereEfficiencyPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereEventLogs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereInitiatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereIsAutomated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereLossAmountKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereLossPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereMonitoringData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereNetTransferAmountKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereNetTransferAmountMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer wherePerformanceMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereQualityStandards($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereRequiresApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereSafetyRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereScheduledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereScheduledEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereScheduledStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereSourceMeterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferAmountKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferAmountMwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferProtocol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferRateKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferRateMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereTransferUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnergyTransfer whereVerifiedBy($value)
 */
	class EnergyTransfer extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property \Illuminate\Support\Carbon $date
 * @property string $location
 * @property bool $public
 * @property string $language
 * @property int $organization_id
 * @property bool $is_draft
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventAttendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventAttendance> $attendedUsers
 * @property-read int|null $attended_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventAttendance> $cancelledUsers
 * @property-read int|null $cancelled_users_count
 * @property-read array $attendance_stats
 * @property-read string $language_label
 * @property-read string $status
 * @property-read string $status_badge_class
 * @property-read string $status_color
 * @property-read string $status_icon
 * @property-read string $time_ago
 * @property-read string $time_until
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventAttendance> $noShowUsers
 * @property-read int|null $no_show_users_count
 * @property-read \App\Models\Organization $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventAttendance> $registeredUsers
 * @property-read int|null $registered_users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event byDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event byLanguage($language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event byLocation($location)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event byOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event drafts()
 * @method static \Database\Factories\EventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event past()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event private()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event public()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event thisMonth()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event thisWeek()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event today()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event upcoming()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event withoutTrashed()
 */
	class Event extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon $registered_at
 * @property \Illuminate\Support\Carbon|null $checked_in_at
 * @property string|null $cancellation_reason
 * @property string|null $notes
 * @property string|null $checkin_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Event $event
 * @property-read string $status_badge_class
 * @property-read string $status_color
 * @property-read string $status_icon
 * @property-read string $status_label
 * @property-read string $time_since_checkin
 * @property-read string $time_since_registration
 * @property-read string $time_until_event
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance attended()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance byCheckinDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance byEvent($eventId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance byOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance byRegistrationDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance byUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance cancelled()
 * @method static \Database\Factories\EventAttendanceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance noShow()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance recent($days = 7)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance registered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereCheckinToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereRegisteredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendance withoutTrashed()
 */
	class EventAttendance extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $topic_id
 * @property string $question
 * @property string $answer
 * @property int $position
 * @property int $views_count
 * @property int $helpful_count
 * @property int $not_helpful_count
 * @property bool $is_featured
 * @property array<array-key, mixed>|null $tags
 * @property int|null $organization_id
 * @property string $language
 * @property bool $is_draft
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read mixed $helpful_rate
 * @property-read mixed $readable_answer
 * @property-read mixed $short_answer
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\FaqTopic|null $topic
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq byTopic($topicId)
 * @method static \Database\Factories\FaqFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq inLanguage($language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq orderedByPosition()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereHelpfulCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereNotHelpfulCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereTopicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereUpdatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereViewsCount($value)
 */
	class Faq extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $color
 * @property int $sort_order
 * @property bool $is_active
 * @property int|null $organization_id
 * @property string $language
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Faq> $faqs
 * @property-read int|null $faqs_count
 * @property-read \App\Models\Organization|null $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic active()
 * @method static \Database\Factories\FaqTopicFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic inLanguage($language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqTopic whereUpdatedAt($value)
 */
	class FaqTopic extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $form_name
 * @property array<array-key, mixed> $fields
 * @property string $status
 * @property string|null $source_url
 * @property string|null $referrer
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property int|null $processed_by_user_id
 * @property string|null $processing_notes
 * @property int|null $organization_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\User|null $processedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission archived()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission byFormType(string $formType)
 * @method static \Database\Factories\FormSubmissionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission fromIp(string $ip)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission processed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission processedBy(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission recent(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission spam()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission unprocessed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereFormName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereProcessedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereProcessingNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereReferrer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereSourceUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormSubmission whereUserAgent($value)
 */
	class FormSubmission extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $image
 * @property string|null $mobile_image
 * @property string|null $text
 * @property string|null $subtext
 * @property string|null $text_button
 * @property string|null $internal_link
 * @property string|null $cta_link_external
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $exhibition_beginning
 * @property \Illuminate\Support\Carbon|null $exhibition_end
 * @property bool $active
 * @property string|null $video_url
 * @property string|null $video_background
 * @property string $text_align
 * @property int $overlay_opacity
 * @property string|null $animation_type
 * @property string $cta_style
 * @property int $priority
 * @property string $language
 * @property int|null $organization_id
 * @property bool $is_draft
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PageComponent> $pageComponents
 * @property-read int|null $page_components_count
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero byPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero draft()
 * @method static \Database\Factories\HeroFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero forSlideshow()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero inCurrentLanguage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero inExhibitionPeriod()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero inLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero shouldBePublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereAnimationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereCtaLinkExternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereCtaStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereExhibitionBeginning($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereExhibitionEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereInternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereMobileImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereOverlayOpacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereSubtext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereTextAlign($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereTextButton($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereUpdatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereVideoBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero whereVideoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero withCurrentLanguageFallback()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hero withFallback(string $language, ?string $fallback = null)
 */
	class Hero extends \Eloquent implements \Spatie\MediaLibrary\HasMedia, \App\Contracts\Cacheable, \App\Contracts\Publishable, \App\Contracts\Multilingual {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $alt_text
 * @property string $filename
 * @property string $path
 * @property string|null $url
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property int|null $width
 * @property int|null $height
 * @property array<array-key, mixed>|null $metadata
 * @property int|null $category_id
 * @property array<array-key, mixed>|null $tags
 * @property int|null $organization_id
 * @property string $language
 * @property bool $is_public
 * @property bool $is_featured
 * @property string $status
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property array<array-key, mixed>|null $responsive_urls
 * @property int $download_count
 * @property int $view_count
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property int|null $uploaded_by_user_id
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read float|null $aspect_ratio
 * @property-read string|null $dimensions
 * @property-read string $formatted_file_size
 * @property-read bool|null $is_landscape
 * @property-read bool|null $is_portrait
 * @property-read bool|null $is_square
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\User|null $uploadedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image byCategory($categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image byMimeType(string $mimeType)
 * @method static \Database\Factories\ImageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image images()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image inLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image public()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image recentlyUsed(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereDownloadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereResponsiveUrls($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereSeoDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereSeoTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereUploadedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereViewCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereWidth($value)
 */
	class Image extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property numeric $total_kwh_produced
 * @property numeric $total_co2_avoided_kg
 * @property int|null $plant_group_id
 * @property \Illuminate\Support\Carbon $generated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $efficiency
 * @property-read mixed $formatted_co2
 * @property-read mixed $formatted_date
 * @property-read mixed $formatted_efficiency
 * @property-read mixed $formatted_kwh
 * @property-read mixed $is_global
 * @property-read mixed $is_individual
 * @property-read \App\Models\PlantGroup|null $plantGroup
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics byCo2Range($minCo2, $maxCo2 = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics byDateRange($startDate, $endDate = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics byKwhRange($minKwh, $maxKwh = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics byPlantGroup($plantGroupId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics byUser($userId)
 * @method static \Database\Factories\ImpactMetricsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics global()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics individual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics orderByDate($direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics orderByImpact($direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics orderByProduction($direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics recent($days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics thisMonth()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics thisYear()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics whereGeneratedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics wherePlantGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics whereTotalCo2AvoidedKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics whereTotalKwhProduced($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImpactMetrics withoutTrashed()
 */
	class ImpactMetrics extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $token
 * @property string|null $email
 * @property int $organization_role_id
 * @property int $organization_id
 * @property int $invited_by
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $invitation_url
 * @property-read \App\Models\User $invitedByUser
 * @property-read \App\Models\Organization $organization
 * @property-read \App\Models\OrganizationRole $organizationRole
 * @method static \Database\Factories\InvitationTokenFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken notExpired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken valid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereInvitedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereOrganizationRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvitationToken whereUsedAt($value)
 */
	class InvitationToken extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $invoice_number
 * @property string $invoice_code
 * @property string $type
 * @property string $status
 * @property int $user_id
 * @property int|null $energy_cooperative_id
 * @property int|null $parent_invoice_id
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property \Illuminate\Support\Carbon|null $service_period_start
 * @property \Illuminate\Support\Carbon|null $service_period_end
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $viewed_at
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property numeric $subtotal
 * @property numeric $tax_amount
 * @property numeric $discount_amount
 * @property numeric $total_amount
 * @property numeric $paid_amount
 * @property numeric $pending_amount
 * @property string $currency
 * @property numeric $tax_rate
 * @property string|null $tax_number
 * @property string|null $tax_type
 * @property string $customer_name
 * @property string $customer_email
 * @property string|null $billing_address
 * @property string|null $customer_tax_id
 * @property string|null $company_name
 * @property string|null $company_address
 * @property string|null $company_tax_id
 * @property string|null $company_email
 * @property array<array-key, mixed> $line_items
 * @property string|null $description
 * @property string|null $notes
 * @property string|null $terms_and_conditions
 * @property numeric|null $energy_consumption_kwh
 * @property numeric|null $energy_production_kwh
 * @property numeric|null $energy_price_per_kwh
 * @property string|null $meter_reading_start
 * @property string|null $meter_reading_end
 * @property array<array-key, mixed>|null $payment_methods
 * @property string|null $payment_terms
 * @property int $grace_period_days
 * @property bool $is_recurring
 * @property string|null $recurring_frequency
 * @property \Illuminate\Support\Carbon|null $next_billing_date
 * @property int $recurring_count
 * @property int|null $max_recurring_count
 * @property string|null $pdf_path
 * @property string|null $pdf_url
 * @property array<array-key, mixed>|null $attachments
 * @property int|null $created_by_id
 * @property string|null $customer_ip
 * @property int $view_count
 * @property \Illuminate\Support\Carbon|null $last_viewed_at
 * @property array<array-key, mixed>|null $activity_log
 * @property array<array-key, mixed>|null $metadata
 * @property string $language
 * @property string|null $template
 * @property bool $is_test
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Invoice> $childInvoices
 * @property-read int|null $child_invoices_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\EnergyCooperative|null $energyCooperative
 * @property-read Invoice|null $parentInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $refunds
 * @property-read int|null $refunds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice forUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice paid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice production()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice recurring()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice test()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereActivityLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCompanyAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCompanyEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCompanyTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereEnergyConsumptionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereEnergyCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereEnergyPricePerKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereEnergyProductionKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereGracePeriodDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIsTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereLastViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereLineItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMaxRecurringCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMeterReadingEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMeterReadingStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNextBillingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereParentInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaymentMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePdfPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePdfUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePendingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereRecurringCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereRecurringFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereServicePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereServicePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTermsAndConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereViewCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereViewedAt($value)
 */
	class Invoice extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $customer_profile_id
 * @property int $organization_id
 * @property string $type
 * @property string $version
 * @property \Illuminate\Support\Carbon $uploaded_at
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property int|null $verifier_user_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CustomerProfile|null $customerProfile
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\User|null $verifier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument byVersion($version)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument expired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument expiringWithin(int $days)
 * @method static \Database\Factories\LegalDocumentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument latestVersion()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument pendingVerification()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument verified()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereCustomerProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereUploadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereVerifierUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalDocument whereVersion($value)
 */
	class LegalDocument extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $schedule_type
 * @property string|null $cron_expression
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property bool $is_active
 * @property int|null $equipment_id
 * @property string|null $equipment_type
 * @property int|null $location_id
 * @property string|null $location_type
 * @property int|null $assigned_team_id
 * @property int|null $vendor_id
 * @property int|null $task_template_id
 * @property int|null $checklist_template_id
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $schedule_config
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\ChecklistTemplate|null $checklistTemplate
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $equipment
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceTask> $maintenanceTasks
 * @property-read int|null $maintenance_tasks_count
 * @property-read \App\Models\TaskTemplate|null $taskTemplate
 * @property-read \App\Models\Vendor|null $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule autoGenerateTasks()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule byCategory($category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule byDepartment($department)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule byEquipment($equipmentId, $equipmentType = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule byFrequencyType($frequencyType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule byLocation($locationId, $locationType = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule bySubcategory($subcategory)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule calendarBased()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule conditionBased()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule critical()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule dueSoon($days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule eventBased()
 * @method static \Database\Factories\MaintenanceScheduleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule manual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule predictive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule preventive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule timeBased()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule urgent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule usageBased()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereAssignedTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereChecklistTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereCronExpression($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereEquipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereEquipmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereLocationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereScheduleConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereScheduleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereTaskTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceSchedule withoutTrashed()
 */
	class MaintenanceSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $task_type
 * @property string $priority
 * @property string $status
 * @property int|null $assigned_to
 * @property int|null $assigned_by
 * @property \Illuminate\Support\Carbon $due_date
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property numeric|null $estimated_hours
 * @property numeric|null $actual_hours
 * @property int|null $equipment_id
 * @property string|null $equipment_type
 * @property int|null $location_id
 * @property string|null $location_type
 * @property int|null $maintenance_schedule_id
 * @property array<array-key, mixed>|null $checklist_items
 * @property array<array-key, mixed>|null $required_tools
 * @property array<array-key, mixed>|null $required_parts
 * @property string|null $safety_notes
 * @property string|null $technical_notes
 * @property numeric|null $cost_estimate
 * @property numeric|null $actual_cost
 * @property int|null $vendor_id
 * @property bool $warranty_work
 * @property bool $recurring
 * @property string|null $recurrence_pattern
 * @property \Illuminate\Support\Carbon|null $next_recurrence_date
 * @property array<array-key, mixed>|null $attachments
 * @property array<array-key, mixed>|null $tags
 * @property string|null $notes
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $work_order_number
 * @property string|null $department
 * @property string|null $category
 * @property string|null $subcategory
 * @property string $risk_level
 * @property string|null $completion_notes
 * @property int|null $quality_score
 * @property string|null $customer_feedback
 * @property bool $follow_up_required
 * @property \Illuminate\Support\Carbon|null $follow_up_date
 * @property bool $preventive_maintenance
 * @property bool $corrective_maintenance
 * @property bool $emergency_maintenance
 * @property bool $planned_maintenance
 * @property bool $unplanned_maintenance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User|null $assignedBy
 * @property-read \App\Models\User|null $assignedTo
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $equipment
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $location
 * @property-read \App\Models\MaintenanceSchedule|null $maintenanceSchedule
 * @property-read \App\Models\Vendor|null $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask byAssignedTo($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask byCategory($category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask byDepartment($department)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask byRiskLevel($riskLevel)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask bySubcategory($subcategory)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask byTaskType($taskType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask corrective()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask critical()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask dueThisWeek()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask dueToday()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask emergency()
 * @method static \Database\Factories\MaintenanceTaskFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask followUpRequired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask highRisk()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask inProgress()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask onHold()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask planned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask preventive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask recurring()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask unplanned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask urgent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask warrantyWork()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereActualCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereActualHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereAssignedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereChecklistItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereCompletionNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereCorrectiveMaintenance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereCostEstimate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereCustomerFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereEmergencyMaintenance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereEquipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereEquipmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereEstimatedHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereFollowUpDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereFollowUpRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereLocationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereMaintenanceScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereNextRecurrenceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask wherePlannedMaintenance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask wherePreventiveMaintenance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereQualityScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereRecurrencePattern($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereRequiredParts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereRequiredTools($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereRiskLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereSafetyNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereSubcategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereTaskType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereTechnicalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereUnplannedMaintenance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereWarrantyWork($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceTask whereWorkOrderNumber($value)
 */
	class MaintenanceTask extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $market_name
 * @property string|null $market_code
 * @property string $country
 * @property string|null $region
 * @property string|null $zone
 * @property string $commodity_type
 * @property string $product_name
 * @property string|null $product_description
 * @property \Illuminate\Support\Carbon $price_datetime
 * @property \Illuminate\Support\Carbon $price_date
 * @property string $price_time
 * @property string $period_type
 * @property \Illuminate\Support\Carbon $delivery_start_date
 * @property \Illuminate\Support\Carbon $delivery_end_date
 * @property string $delivery_period
 * @property numeric $price
 * @property string $currency
 * @property string $unit
 * @property string|null $opening_price
 * @property string|null $closing_price
 * @property numeric|null $high_price
 * @property numeric|null $low_price
 * @property string|null $weighted_average_price
 * @property numeric|null $volume
 * @property string|null $volume_unit
 * @property int|null $number_of_transactions
 * @property string|null $bid_price
 * @property string|null $ask_price
 * @property string|null $spread
 * @property string|null $price_change_absolute
 * @property numeric|null $price_change_percentage
 * @property numeric|null $volatility
 * @property string|null $vs_previous_day
 * @property string|null $vs_previous_week
 * @property string|null $vs_previous_month
 * @property string|null $vs_previous_year
 * @property string|null $demand_mw
 * @property string|null $supply_mw
 * @property string|null $renewable_generation_mw
 * @property string|null $conventional_generation_mw
 * @property string|null $imports_mw
 * @property string|null $exports_mw
 * @property string|null $system_margin_mw
 * @property string|null $reserve_margin_percentage
 * @property string|null $system_condition
 * @property string|null $temperature_celsius
 * @property string|null $wind_generation_factor
 * @property string|null $solar_generation_factor
 * @property string|null $hydro_reservoir_level
 * @property string|null $natural_gas_price
 * @property string|null $coal_price
 * @property string|null $oil_price
 * @property string|null $co2_price
 * @property string $data_source
 * @property string|null $data_provider
 * @property string $data_quality
 * @property string|null $feed_id
 * @property string|null $api_metadata
 * @property string|null $data_retrieved_at
 * @property string|null $sma_7
 * @property string|null $sma_30
 * @property string|null $ema_7
 * @property string|null $ema_30
 * @property string|null $rsi
 * @property int $price_spike_detected
 * @property string|null $spike_threshold
 * @property int $unusual_volume_detected
 * @property string|null $market_alerts
 * @property string|null $forecast_next_hour
 * @property string|null $forecast_next_day
 * @property string|null $forecast_confidence
 * @property string|null $forecast_model
 * @property int $regulated_price
 * @property string|null $regulatory_period
 * @property string|null $regulatory_adjustments
 * @property string $market_status
 * @property string|null $additional_data
 * @property string|null $notes
 * @property int $is_holiday
 * @property string|null $day_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\MarketPriceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereAdditionalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereApiMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereAskPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereBidPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereClosingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereCo2Price($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereCoalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereCommodityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereConventionalGenerationMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereDataProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereDataQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereDataRetrievedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereDataSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereDayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereDeliveryEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereDeliveryPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereDeliveryStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereDemandMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereEma30($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereEma7($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereExportsMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereForecastConfidence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereForecastModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereForecastNextDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereForecastNextHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereHighPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereHydroReservoirLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereImportsMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereIsHoliday($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereLowPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereMarketAlerts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereMarketCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereMarketName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereMarketStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereNaturalGasPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereNumberOfTransactions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereOilPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereOpeningPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice wherePriceChangeAbsolute($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice wherePriceChangePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice wherePriceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice wherePriceDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice wherePriceSpikeDetected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice wherePriceTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereProductDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereRegulatedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereRegulatoryAdjustments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereRegulatoryPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereRenewableGenerationMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereReserveMarginPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereRsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereSma30($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereSma7($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereSolarGenerationFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereSpikeThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereSpread($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereSupplyMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereSystemCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereSystemMarginMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereTemperatureCelsius($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereUnusualVolumeDetected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereVolatility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereVolumeUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereVsPreviousDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereVsPreviousMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereVsPreviousWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereVsPreviousYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereWeightedAveragePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereWindGenerationFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MarketPrice whereZone($value)
 */
	class MarketPrice extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $icon
 * @property string $text
 * @property string|null $internal_link
 * @property string|null $external_link
 * @property bool $target_blank
 * @property int|null $parent_id
 * @property int $order
 * @property string|null $permission
 * @property string $menu_group
 * @property string|null $css_classes
 * @property array<array-key, mixed>|null $visibility_rules
 * @property string|null $badge_text
 * @property string|null $badge_color
 * @property string $language
 * @property int|null $organization_id
 * @property bool $is_draft
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Menu> $children
 * @property-read int|null $children_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Organization|null $organization
 * @property-read Menu|null $parent
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu byGroup(string $group)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu draft()
 * @method static \Database\Factories\MenuFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu inCurrentLanguage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu inLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu rootItems()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu shouldBePublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereBadgeColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereBadgeText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereCssClasses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereInternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereMenuGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereTargetBlank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereUpdatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereVisibilityRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu withCurrentLanguageFallback()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu withFallback(string $language, ?string $fallback = null)
 */
	class Menu extends \Eloquent implements \App\Contracts\Cacheable, \App\Contracts\Publishable, \App\Contracts\Multilingual {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name
 * @property string $email
 * @property string|null $phone
 * @property string $subject
 * @property string $message
 * @property string $status
 * @property string $priority
 * @property string $message_type
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $replied_at
 * @property int|null $replied_by_user_id
 * @property string|null $internal_notes
 * @property int|null $assigned_to_user_id
 * @property int|null $organization_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $assignedTo
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\User|null $repliedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message archived()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message assignedTo(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message byEmail(string $email)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message byPriority(string $priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message byType(string $type)
 * @method static \Database\Factories\MessageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message orderByPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message read()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message replied()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message spam()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message unread()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereAssignedToUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMessageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereRepliedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereRepliedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUserAgent($value)
 */
	class Message extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $milestone_type
 * @property string $status
 * @property string $priority
 * @property \Illuminate\Support\Carbon $target_date
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $completion_date
 * @property numeric|null $target_value
 * @property numeric|null $current_value
 * @property numeric $progress_percentage
 * @property string|null $success_criteria
 * @property string|null $dependencies
 * @property string|null $risks
 * @property string|null $mitigation_strategies
 * @property int|null $parent_milestone_id
 * @property int|null $assigned_to
 * @property int $created_by
 * @property array<array-key, mixed>|null $tags
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $assignedTo
 * @property-read \App\Models\User $createdBy
 * @property-read Milestone|null $parentMilestone
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Milestone> $subMilestones
 * @property-read int|null $sub_milestones_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byAssignedTo($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byCompletionDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byCreatedBy($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byParentMilestone($parentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byProgressRange($minProgress, $maxProgress)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byStartDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byTargetDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone byValueRange($minValue, $maxValue)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone community()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone dueSoon($days = 7)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone environmental()
 * @method static \Database\Factories\MilestoneFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone financial()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone inProgress()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone lowPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone mediumPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone notStarted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone onHold()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone operational()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone overdueStatus()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone project()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone regulatory()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone rootMilestones()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCompletionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCurrentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereDependencies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereMilestoneType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereMitigationStrategies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereParentMilestoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereProgressPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereRisks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereSuccessCriteria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereTargetDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereTargetValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereUpdatedAt($value)
 */
	class Milestone extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $text
 * @property int $province_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $full_name
 * @property-read bool $is_operating
 * @property-read \App\Models\Province $province
 * @property-read \App\Models\Region|null $region
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WeatherSnapshot> $weatherSnapshots
 * @property-read int|null $weather_snapshots_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality bySlug(string $slug)
 * @method static \Database\Factories\MunicipalityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality inProvince($provinceId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality inRegion($regionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality operating()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality whereProvinceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Municipality withWeatherData()
 */
	class Municipality extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name
 * @property string $email
 * @property string $status
 * @property string|null $subscription_source
 * @property array<array-key, mixed>|null $preferences
 * @property array<array-key, mixed>|null $tags
 * @property string $language
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $unsubscribed_at
 * @property string|null $confirmation_token
 * @property string|null $unsubscribe_token
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $emails_sent
 * @property int $emails_opened
 * @property int $links_clicked
 * @property \Illuminate\Support\Carbon|null $last_email_sent_at
 * @property \Illuminate\Support\Carbon|null $last_email_opened_at
 * @property int|null $organization_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization|null $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription bounced()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription byLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription bySource(string $source)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription complained()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription engaged()
 * @method static \Database\Factories\NewsletterSubscriptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription recentSubscribers(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription unsubscribed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereConfirmationToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereEmailsOpened($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereEmailsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereLastEmailOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereLastEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereLinksClicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription wherePreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereSubscriptionSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereUnsubscribeToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereUnsubscribedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscription withTags(array $tags)
 */
	class NewsletterSubscription extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string|null $delivered_time_ago
 * @property-read string $message_summary
 * @property-read string|null $read_time_ago
 * @property-read string $time_ago
 * @property-read string $title_formatted
 * @property-read string $type_badge_class
 * @property-read string $type_color
 * @property-read string $type_icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification byUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification delivered()
 * @method static \Database\Factories\NotificationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification notDelivered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification read()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification recent(int $days = 7)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification unread()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification withoutTrashed()
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $channel
 * @property string $notification_type
 * @property bool $enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $channel_color
 * @property-read string $channel_icon
 * @property-read string $channel_label
 * @property-read string $notification_type_color
 * @property-read string $notification_type_icon
 * @property-read string $notification_type_label
 * @property-read string $status_badge_class
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting byChannel(string $channel)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting byNotificationType(string $notificationType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting byUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting disabled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting enabled()
 * @method static \Database\Factories\NotificationSettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereNotificationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereUserId($value)
 */
	class NotificationSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $domain
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property array<array-key, mixed>|null $css_files
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AppSetting|null $appSettings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Article> $articles
 * @property-read int|null $articles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Event> $events
 * @property-read int|null $events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrganizationFeature> $features
 * @property-read int|null $features_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page> $pages
 * @property-read int|null $pages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\OrganizationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereCssFiles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereUpdatedAt($value)
 */
	class Organization extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $organization_id
 * @property string $feature_key
 * @property bool $enabled_dashboard
 * @property bool $enabled_web
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization $organization
 * @method static \Database\Factories\OrganizationFeatureFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature whereEnabledDashboard($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature whereEnabledWeb($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature whereFeatureKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationFeature whereUpdatedAt($value)
 */
	class OrganizationFeature extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array<array-key, mixed>|null $permissions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserOrganizationRole> $userRoles
 * @property-read int|null $user_roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\OrganizationRoleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationRole whereUpdatedAt($value)
 */
	class OrganizationRole extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $route
 * @property string $language
 * @property int|null $organization_id
 * @property bool $is_draft
 * @property string $template
 * @property array<array-key, mixed>|null $meta_data
 * @property int $cache_duration
 * @property bool $requires_auth
 * @property array<array-key, mixed>|null $allowed_roles
 * @property int|null $parent_id
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property array<array-key, mixed>|null $search_keywords
 * @property string|null $internal_notes
 * @property \Illuminate\Support\Carbon|null $last_reviewed_at
 * @property string|null $accessibility_notes
 * @property string|null $reading_level
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property int|null $approved_by_user_id
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Page> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PageComponent> $components
 * @property-read int|null $components_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read Page|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Page> $publishedChildren
 * @property-read int|null $published_children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PageComponent> $publishedComponents
 * @property-read int|null $published_components_count
 * @property-read \App\Models\SeoMetaData|null $seoMetaData
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page byParent(?int $parentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page byTemplate(string $template)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page draft()
 * @method static \Database\Factories\PageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page inCurrentLanguage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page inLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page requiresAuth(bool $requiresAuth = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page rootPages()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page shouldBePublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereAccessibilityNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereAllowedRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereApprovedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCacheDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereLastReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereReadingLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereRequiresAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSearchKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withCurrentLanguageFallback()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withFallback(string $language, ?string $fallback = null)
 */
	class Page extends \Eloquent implements \Spatie\MediaLibrary\HasMedia, \App\Contracts\Cacheable, \App\Contracts\Publishable, \App\Contracts\Multilingual {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $page_id
 * @property string $componentable_type
 * @property int $componentable_id
 * @property int $position
 * @property int|null $parent_id
 * @property string $language
 * @property int|null $organization_id
 * @property bool $is_draft
 * @property string $version
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property string|null $preview_token
 * @property array<array-key, mixed>|null $settings
 * @property bool $cache_enabled
 * @property array<array-key, mixed>|null $visibility_rules
 * @property string|null $ab_test_group
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PageComponent> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $componentable
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\Page $page
 * @property-read PageComponent|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent byComponentType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent byPosition(int $position)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent draft()
 * @method static \Database\Factories\PageComponentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent inAbTestGroup(string $group)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent inCurrentLanguage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent inLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent shouldBePublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent visible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereAbTestGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereCacheEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereComponentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereComponentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent wherePreviewToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent whereVisibilityRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent withCurrentLanguageFallback()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageComponent withFallback(string $language, ?string $fallback = null)
 */
	class PageComponent extends \Eloquent implements \App\Contracts\Cacheable, \App\Contracts\Publishable, \App\Contracts\Multilingual {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $payment_code
 * @property string|null $external_id
 * @property int $user_id
 * @property int|null $invoice_id
 * @property string $type
 * @property string $status
 * @property string $method
 * @property numeric $amount
 * @property numeric $fee
 * @property numeric $net_amount
 * @property string $currency
 * @property numeric|null $exchange_rate
 * @property numeric|null $original_amount
 * @property string|null $original_currency
 * @property string|null $gateway
 * @property string|null $gateway_transaction_id
 * @property array<array-key, mixed>|null $gateway_response
 * @property array<array-key, mixed>|null $gateway_metadata
 * @property string|null $card_last_four
 * @property string|null $card_brand
 * @property string|null $payment_method_id
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $authorized_at
 * @property \Illuminate\Support\Carbon|null $captured_at
 * @property string|null $description
 * @property string|null $reference
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $failure_reason
 * @property string|null $notes
 * @property int|null $energy_cooperative_id
 * @property string|null $energy_contract_id
 * @property numeric|null $energy_amount_kwh
 * @property int|null $created_by_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property bool $is_test
 * @property bool $is_recurring
 * @property int|null $parent_payment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $childPayments
 * @property-read int|null $child_payments_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\EnergyCooperative|null $energyCooperative
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read Payment|null $parentPayment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $refunds
 * @property-read int|null $refunds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment byGateway($gateway)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment byMethod($method)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment failed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment forUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment production()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment test()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAuthorizedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCapturedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCardBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCardLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereEnergyAmountKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereEnergyContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereEnergyCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereFailedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereFailureReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereGatewayMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereGatewayResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereGatewayTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereIsTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereOriginalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereOriginalCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereParentPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUserId($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $indicator_name
 * @property string $indicator_code
 * @property string|null $description
 * @property string $indicator_type
 * @property string $category
 * @property string $criticality
 * @property int $priority
 * @property string $frequency
 * @property bool $is_active
 * @property string $scope
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property string|null $entity_name
 * @property \Illuminate\Support\Carbon $measurement_timestamp
 * @property \Illuminate\Support\Carbon $measurement_date
 * @property \Illuminate\Support\Carbon $period_start
 * @property \Illuminate\Support\Carbon $period_end
 * @property string $period_type
 * @property numeric $current_value
 * @property string|null $unit
 * @property numeric|null $target_value
 * @property numeric|null $baseline_value
 * @property numeric|null $previous_value
 * @property string|null $best_value
 * @property string|null $worst_value
 * @property string|null $change_absolute
 * @property numeric|null $change_percentage
 * @property numeric|null $target_achievement_percentage
 * @property string|null $trend_direction
 * @property string|null $trend_strength
 * @property string|null $performance_status
 * @property string|null $calculation_formula
 * @property string|null $calculation_parameters
 * @property string|null $data_sources
 * @property string $calculation_method
 * @property numeric|null $confidence_level
 * @property numeric|null $industry_benchmark
 * @property string|null $competitor_benchmark
 * @property string|null $internal_benchmark
 * @property string|null $benchmark_comparison
 * @property string|null $influencing_factors
 * @property string|null $context_notes
 * @property string|null $external_conditions
 * @property string|null $seasonality_factor
 * @property int $weather_dependent
 * @property bool $alerts_enabled
 * @property string|null $alert_threshold_min
 * @property string|null $alert_threshold_max
 * @property string|null $warning_threshold_min
 * @property string|null $warning_threshold_max
 * @property string $current_alert_level
 * @property string|null $last_alert_sent_at
 * @property string|null $improvement_actions
 * @property string|null $corrective_actions
 * @property string|null $improvement_potential
 * @property string|null $next_review_date
 * @property string|null $action_priority
 * @property string $business_impact
 * @property string|null $financial_impact_eur
 * @property string|null $business_value_description
 * @property string|null $stakeholders
 * @property numeric|null $efficiency_percentage
 * @property numeric|null $utilization_percentage
 * @property string|null $availability_percentage
 * @property int|null $downtime_minutes
 * @property string|null $cost_per_unit
 * @property string|null $revenue_impact_eur
 * @property string|null $roi_percentage
 * @property string|null $payback_months
 * @property numeric|null $quality_score
 * @property string|null $satisfaction_score
 * @property int|null $defects_count
 * @property string|null $error_rate_percentage
 * @property string|null $system_load_percentage
 * @property string|null $response_time_ms
 * @property string|null $throughput_per_hour
 * @property int|null $concurrent_users
 * @property int|null $energy_cooperative_id
 * @property int|null $user_id
 * @property int|null $energy_report_id
 * @property int $created_by_id
 * @property int|null $validated_by_id
 * @property bool $is_validated
 * @property string|null $validated_at
 * @property string|null $validation_notes
 * @property string|null $audit_log
 * @property int $revision_number
 * @property bool $show_in_dashboard
 * @property string|null $dashboard_config
 * @property string|null $chart_type
 * @property int|null $dashboard_order
 * @property bool $auto_calculate
 * @property string|null $calculation_time
 * @property string|null $automation_rules
 * @property string|null $last_calculated_at
 * @property int $calculation_attempts
 * @property string|null $last_calculation_error
 * @property string|null $tags
 * @property string|null $metadata
 * @property string|null $notes
 * @property int $is_public
 * @property string|null $public_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\EnergyCooperative|null $energyCooperative
 * @property-read \App\Models\EnergyReport|null $energyReport
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator byCategory(string $category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator byCriticality(string $criticality)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator byType(string $type)
 * @method static \Database\Factories\PerformanceIndicatorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator forDashboard()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereActionPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereAlertThresholdMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereAlertThresholdMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereAlertsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereAuditLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereAutoCalculate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereAutomationRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereAvailabilityPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereBaselineValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereBenchmarkComparison($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereBestValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereBusinessImpact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereBusinessValueDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCalculationAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCalculationFormula($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCalculationMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCalculationParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCalculationTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereChangeAbsolute($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereChangePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereChartType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCompetitorBenchmark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereConcurrentUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereConfidenceLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereContextNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCorrectiveActions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCostPerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCriticality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCurrentAlertLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereCurrentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereDashboardConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereDashboardOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereDataSources($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereDefectsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereDowntimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereEfficiencyPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereEnergyCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereEnergyReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereEntityName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereErrorRatePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereExternalConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereFinancialImpactEur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereImprovementActions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereImprovementPotential($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereIndicatorCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereIndicatorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereIndicatorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereIndustryBenchmark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereInfluencingFactors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereInternalBenchmark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereIsValidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereLastAlertSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereLastCalculatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereLastCalculationError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereMeasurementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereMeasurementTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereNextReviewDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator wherePaybackMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator wherePerformanceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator wherePreviousValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator wherePublicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereQualityScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereResponseTimeMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereRevenueImpactEur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereRevisionNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereRoiPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereSatisfactionScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereSeasonalityFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereShowInDashboard($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereStakeholders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereSystemLoadPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereTargetAchievementPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereTargetValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereThroughputPerHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereTrendDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereTrendStrength($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereUtilizationPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereValidatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereValidatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereValidationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereWarningThresholdMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereWarningThresholdMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereWeatherDependent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator whereWorstValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerformanceIndicator withAlerts()
 */
	class PerformanceIndicator extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property numeric $co2_equivalent_per_unit_kg
 * @property string|null $image
 * @property string|null $description
 * @property string $unit_label
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CooperativePlantConfig> $cooperativeConfigs
 * @property-read int|null $cooperative_configs_count
 * @property-read mixed $display_name
 * @property-read mixed $formatted_co2
 * @property-read mixed $image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlantGroup> $plantGroups
 * @property-read int|null $plant_groups_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant byCo2Range($minCo2, $maxCo2 = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant byName($name)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant byUnitLabel($unitLabel)
 * @method static \Database\Factories\PlantFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant whereCo2EquivalentPerUnitKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant whereUnitLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plant withoutTrashed()
 */
	class Plant extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property int $plant_id
 * @property int $number_of_plants
 * @property numeric $co2_avoided_total
 * @property string|null $custom_label
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $display_name
 * @property-read mixed $formatted_co2_avoided
 * @property-read mixed $formatted_plant_count
 * @property-read mixed $is_collective
 * @property-read mixed $is_individual
 * @property-read \App\Models\Plant $plant
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup byCo2Range($minCo2, $maxCo2 = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup byPlant($plantId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup byUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup collective()
 * @method static \Database\Factories\PlantGroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup individual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup whereCo2AvoidedTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup whereCustomLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup whereNumberOfPlants($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup wherePlantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlantGroup withoutTrashed()
 */
	class PlantGroup extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $offer_number
 * @property string $title
 * @property string|null $description
 * @property string $offer_type
 * @property string $status
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property \Illuminate\Support\Carbon|null $early_bird_end_date
 * @property \Illuminate\Support\Carbon|null $founder_end_date
 * @property int $total_units_available
 * @property int $units_reserved
 * @property int $units_sold
 * @property numeric $early_bird_price
 * @property numeric|null $founder_price
 * @property numeric $regular_price
 * @property numeric $final_price
 * @property numeric|null $savings_percentage
 * @property numeric|null $savings_amount
 * @property int $max_units_per_customer
 * @property bool $is_featured
 * @property bool $is_public
 * @property string|null $terms_conditions
 * @property string|null $delivery_timeline
 * @property string|null $risk_disclosure
 * @property array<array-key, mixed>|null $included_features
 * @property array<array-key, mixed>|null $excluded_features
 * @property array<array-key, mixed>|null $bonus_items
 * @property array<array-key, mixed>|null $early_access_benefits
 * @property array<array-key, mixed>|null $founder_benefits
 * @property array<array-key, mixed>|null $marketing_materials
 * @property array<array-key, mixed>|null $tags
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer earlyBird()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer expired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer founder()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer public()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereBonusItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereDeliveryTimeline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereEarlyAccessBenefits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereEarlyBirdEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereEarlyBirdPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereExcludedFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereFinalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereFounderBenefits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereFounderEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereFounderPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereIncludedFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereMarketingMaterials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereMaxUnitsPerCustomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereOfferNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereOfferType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereRegularPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereRiskDisclosure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereSavingsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereSavingsPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereTermsConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereTotalUnitsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereUnitsReserved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereUnitsSold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreSaleOffer withoutTrashed()
 */
	class PreSaleOffer extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $provider_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $type
 * @property numeric|null $base_purchase_price
 * @property numeric|null $base_sale_price
 * @property string $commission_type
 * @property numeric|null $commission_value
 * @property string $surcharge_type
 * @property numeric|null $surcharge_value
 * @property string $unit
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property array<array-key, mixed>|null $metadata
 * @property numeric|null $renewable_percentage
 * @property numeric|null $carbon_footprint
 * @property string|null $geographical_zone
 * @property string|null $image_path
 * @property array<array-key, mixed>|null $features
 * @property int|null $stock_quantity
 * @property numeric|null $weight
 * @property array<array-key, mixed>|null $dimensions
 * @property string|null $warranty_info
 * @property int|null $estimated_lifespan_years
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property array<array-key, mixed>|null $keywords
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Provider $provider
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $taggables
 * @property-read int|null $taggables_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserAsset> $userAssets
 * @property-read int|null $user_assets_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product byType(string $type)
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product inZone(string $zone)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product priceRange(?float $min = null, ?float $max = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product renewable(float $minPercentage = '50')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBasePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBaseSalePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCarbonFootprint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCommissionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCommissionValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDimensions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereEstimatedLifespanYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGeographicalZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRenewablePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStockQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSurchargeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSurchargeValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWarrantyInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWeight($value)
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $project_number
 * @property string $name
 * @property string|null $description
 * @property string $project_type
 * @property string $status
 * @property string $priority
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $expected_completion_date
 * @property \Illuminate\Support\Carbon|null $actual_completion_date
 * @property numeric $budget
 * @property numeric $spent_amount
 * @property numeric $remaining_budget
 * @property numeric $planned_capacity_mw
 * @property numeric|null $actual_capacity_mw
 * @property numeric|null $efficiency_rating
 * @property string|null $location_address
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $technical_specifications
 * @property string|null $environmental_impact
 * @property string|null $regulatory_compliance
 * @property string|null $safety_measures
 * @property array<array-key, mixed>|null $project_team
 * @property array<array-key, mixed>|null $stakeholders
 * @property array<array-key, mixed>|null $contractors
 * @property array<array-key, mixed>|null $suppliers
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Milestone> $milestones
 * @property array<array-key, mixed>|null $risks
 * @property array<array-key, mixed>|null $mitigation_strategies
 * @property array<array-key, mixed>|null $quality_standards
 * @property array<array-key, mixed>|null $documentation
 * @property array<array-key, mixed>|null $tags
 * @property int|null $project_manager
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyInstallation> $installations
 * @property-read int|null $installations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyMeter> $meters
 * @property-read int|null $meters_count
 * @property-read int|null $milestones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PreSaleOffer> $preSaleOffers
 * @property-read int|null $pre_sale_offers_count
 * @property-read \App\Models\User|null $projectManager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyReading> $readings
 * @property-read int|null $readings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject byProjectManager($projectManagerId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject completed()
 * @method static \Database\Factories\ProductionProjectFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject inProgress()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject maintenance()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject onHold()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject planning()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereActualCapacityMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereActualCompletionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereContractors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereDocumentation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereEfficiencyRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereEnvironmentalImpact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereExpectedCompletionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereLocationAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereMilestones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereMitigationStrategies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject wherePlannedCapacityMw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereProjectManager($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereProjectNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereProjectTeam($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereProjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereQualityStandards($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereRegulatoryCompliance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereRemainingBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereRisks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereSafetyMeasures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereSpentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereStakeholders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereSuppliers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereTechnicalSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionProject whereUpdatedAt($value)
 */
	class ProductionProject extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionReservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionReservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionReservation query()
 */
	class ProductionReservation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property array<array-key, mixed>|null $contact_info
 * @property string $type
 * @property bool $is_active
 * @property string|null $website
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $logo_path
 * @property numeric|null $rating
 * @property int $total_reviews
 * @property array<array-key, mixed>|null $certifications
 * @property array<array-key, mixed>|null $operating_regions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider byType(string $type)
 * @method static \Database\Factories\ProviderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider highRated(float $minRating = '4')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider inRegion(string $region)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereCertifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereContactInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereOperatingRegions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereTotalReviews($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Provider whereWebsite($value)
 */
	class Provider extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $region_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Municipality> $municipalities
 * @property-read int|null $municipalities_count
 * @property-read \App\Models\Region $region
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WeatherSnapshot> $weatherSnapshots
 * @property-read int|null $weather_snapshots_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province bySlug(string $slug)
 * @method static \Database\Factories\ProvinceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province inRegion($regionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province withMunicipalityCount()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Province withWeatherData()
 */
	class Province extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $refund_code
 * @property string|null $external_refund_id
 * @property string|null $reference
 * @property int $user_id
 * @property int $payment_id
 * @property int|null $invoice_id
 * @property int|null $transaction_id
 * @property int|null $energy_cooperative_id
 * @property string $type
 * @property string $reason
 * @property string $status
 * @property numeric $refund_amount
 * @property numeric $original_amount
 * @property numeric $processing_fee
 * @property numeric $net_refund_amount
 * @property string $currency
 * @property numeric|null $exchange_rate
 * @property numeric|null $original_currency_amount
 * @property string|null $original_currency
 * @property string $refund_method
 * @property string|null $refund_destination
 * @property array<array-key, mixed>|null $refund_details
 * @property string|null $gateway
 * @property string|null $gateway_refund_id
 * @property array<array-key, mixed>|null $gateway_response
 * @property string|null $gateway_status
 * @property \Illuminate\Support\Carbon $requested_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property string $description
 * @property string|null $customer_reason
 * @property string|null $internal_notes
 * @property array<array-key, mixed>|null $supporting_documents
 * @property numeric|null $energy_amount_kwh
 * @property numeric|null $energy_price_per_kwh
 * @property \Illuminate\Support\Carbon|null $energy_service_date
 * @property string|null $energy_contract_id
 * @property int|null $requested_by_id
 * @property int|null $approved_by_id
 * @property int|null $processed_by_id
 * @property bool $requires_approval
 * @property bool $auto_approved
 * @property numeric|null $auto_approval_threshold
 * @property bool $is_chargeback
 * @property string|null $chargeback_id
 * @property \Illuminate\Support\Carbon|null $chargeback_date
 * @property string|null $dispute_details
 * @property string|null $request_ip
 * @property string|null $user_agent
 * @property bool $is_test
 * @property array<array-key, mixed>|null $audit_trail
 * @property bool $customer_notified
 * @property \Illuminate\Support\Carbon|null $customer_notified_at
 * @property array<array-key, mixed>|null $notification_history
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $failure_reason
 * @property int $retry_count
 * @property \Illuminate\Support\Carbon|null $next_retry_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\EnergyCooperative|null $energyCooperative
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \App\Models\Payment $payment
 * @property-read \App\Models\User|null $processedBy
 * @property-read \App\Models\User|null $requestedBy
 * @property-read \App\Models\Transaction|null $transaction
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund autoApproved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund byReason($reason)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund chargebacks()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund forUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund production()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund requiringApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund test()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereApprovedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereAuditTrail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereAutoApprovalThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereAutoApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereChargebackDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereChargebackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCustomerNotified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCustomerNotifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCustomerReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereDisputeDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereEnergyAmountKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereEnergyContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereEnergyCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereEnergyPricePerKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereEnergyServiceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereExternalRefundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereFailedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereFailureReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereGatewayRefundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereGatewayResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereGatewayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereIsChargeback($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereIsTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereNetRefundAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereNextRetryAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereNotificationHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereOriginalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereOriginalCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereOriginalCurrencyAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereProcessedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereProcessingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRefundAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRefundCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRefundDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRefundDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRefundMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRequestIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRequestedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRequiresApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRetryCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereSupportingDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereUserId($value)
 */
	class Refund extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Municipality> $municipalities
 * @property-read int|null $municipalities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Province> $provinces
 * @property-read int|null $provinces_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region bySlug(string $slug)
 * @method static \Database\Factories\RegionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region withMunicipalityCount()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region withProvinceCount()
 */
	class Region extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $order_number
 * @property int $customer_id
 * @property int|null $affiliate_id
 * @property string $order_type
 * @property string $status
 * @property string $payment_status
 * @property string $shipping_status
 * @property numeric $subtotal
 * @property numeric $tax_amount
 * @property numeric $shipping_amount
 * @property numeric $discount_amount
 * @property numeric $total_amount
 * @property numeric $paid_amount
 * @property numeric $refunded_amount
 * @property numeric $outstanding_amount
 * @property string $currency
 * @property numeric $exchange_rate
 * @property string|null $payment_method
 * @property string|null $payment_reference
 * @property \Illuminate\Support\Carbon|null $payment_date
 * @property string|null $shipping_method
 * @property string|null $tracking_number
 * @property \Illuminate\Support\Carbon|null $shipped_date
 * @property \Illuminate\Support\Carbon|null $delivered_date
 * @property \Illuminate\Support\Carbon|null $expected_delivery_date
 * @property string|null $shipping_address
 * @property string|null $billing_address
 * @property string|null $special_instructions
 * @property string|null $internal_notes
 * @property array<array-key, mixed>|null $order_items
 * @property array<array-key, mixed>|null $applied_discounts
 * @property array<array-key, mixed>|null $shipping_details
 * @property array<array-key, mixed>|null $customer_notes
 * @property array<array-key, mixed>|null $tags
 * @property int $created_by
 * @property int|null $processed_by
 * @property int|null $shipped_by
 * @property int|null $delivered_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Affiliate|null $affiliate
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User $customer
 * @property-read \App\Models\User|null $deliveredBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\User|null $processedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $refunds
 * @property-read int|null $refunds_count
 * @property-read \App\Models\User|null $shippedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder byAffiliate($affiliateId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder byCustomer($customerId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder byOrderType($orderType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder byPaymentStatus($paymentStatus)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder byShippingStatus($shippingStatus)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder confirmed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder delivered()
 * @method static \Database\Factories\SaleOrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder onHold()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder paid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder partialPayment()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder pendingPayment()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder processing()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder shipped()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereAffiliateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereAppliedDiscounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereCustomerNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereDeliveredBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereDeliveredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereExpectedDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereOrderItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereOrderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereOutstandingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder wherePaymentReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereProcessedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereRefundedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereShippedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereShippedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereShippingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereShippingDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereShippingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereShippingStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereSpecialInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleOrder whereUpdatedAt($value)
 */
	class SaleOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $seoable_type
 * @property int $seoable_id
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $canonical_url
 * @property string $robots
 * @property string|null $og_title
 * @property string|null $og_description
 * @property string|null $og_image_path
 * @property string $og_type
 * @property string|null $twitter_title
 * @property string|null $twitter_description
 * @property string|null $twitter_image_path
 * @property string $twitter_card
 * @property array<array-key, mixed>|null $structured_data
 * @property string|null $focus_keyword
 * @property array<array-key, mixed>|null $additional_meta
 * @property string $language
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $seoable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData byKeyword(string $keyword)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData byRobots(string $robots)
 * @method static \Database\Factories\SeoMetaDataFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData inCurrentLanguage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData inLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereAdditionalMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereCanonicalUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereFocusKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereOgDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereOgImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereOgTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereOgType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereRobots($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereSeoableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereSeoableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereStructuredData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereTwitterCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereTwitterDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereTwitterImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereTwitterTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData withCurrentLanguageFallback()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetaData withFallback(string $language, ?string $fallback = null)
 */
	class SeoMetaData extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $platform
 * @property string $url
 * @property string|null $icon
 * @property string|null $css_class
 * @property string|null $color
 * @property int $order
 * @property bool $is_active
 * @property int|null $followers_count
 * @property int|null $organization_id
 * @property bool $is_draft
 * @property int|null $created_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Organization|null $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink byPlatform($platform)
 * @method static \Database\Factories\SocialLinkFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink popular()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereCssClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereFollowersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLink whereUrl($value)
 */
	class SocialLink extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $cooperative_id
 * @property string $status
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization $cooperative
 * @property-read string $status_label
 * @property-read string $type_label
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest byCooperative(int $cooperativeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest byStatus(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest byUser(int $userId)
 * @method static \Database\Factories\SubscriptionRequestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest whereCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionRequest whereUserId($value)
 */
	class SubscriptionRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property bool $anonymous_allowed
 * @property bool $visible_results
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SurveyResponse> $anonymousResponses
 * @property-read int|null $anonymous_responses_count
 * @property-read string $duration
 * @property-read array $response_stats
 * @property-read string $status
 * @property-read string $status_badge_class
 * @property-read string $status_label
 * @property-read string $time_until_end
 * @property-read string $time_until_start
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $respondents
 * @property-read int|null $respondents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SurveyResponse> $responses
 * @property-read int|null $responses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SurveyResponse> $userResponses
 * @property-read int|null $user_responses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey activeToday()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey anonymousAllowed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey anonymousNotAllowed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey byDateRange(string $from, string $to)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey ended()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey expiringSoon(int $days = 7)
 * @method static \Database\Factories\SurveyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey notEnded()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey notStarted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey past()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey resultsHidden()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey resultsVisible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey started()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey upcoming()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey whereAnonymousAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey whereVisibleResults($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Survey withoutTrashed()
 */
	class Survey extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $survey_id
 * @property int|null $user_id
 * @property array<array-key, mixed> $response_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string|null $respondent_avatar
 * @property-read string|null $respondent_email
 * @property-read string $respondent_name
 * @property-read int $response_field_count
 * @property-read array $response_keys
 * @property-read string $response_type
 * @property-read string $response_type_badge_class
 * @property-read string $response_type_label
 * @property-read string $time_since_response
 * @property-read string $time_since_response_detailed
 * @property-read \App\Models\Survey $survey
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse anonymous()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse byDateRange(string $from, string $to)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse bySurvey(int $surveyId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse byUser(int $userId)
 * @method static \Database\Factories\SurveyResponseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse identified()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse recent(int $days = 7)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse searchInResponse(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse thisMonth()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse thisWeek()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse today()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse withResponseData(string $key, $value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse withResponseDataContains(string $key, string $value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyResponse withoutTrashed()
 */
	class SurveyResponse extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $metric_name
 * @property string $metric_code
 * @property string|null $description
 * @property string $metric_type
 * @property string $metric_category
 * @property string $entity_type
 * @property int|null $entity_id
 * @property string|null $entity_name
 * @property \Illuminate\Support\Carbon $measurement_date
 * @property \Illuminate\Support\Carbon $period_start
 * @property \Illuminate\Support\Carbon $period_end
 * @property string $period_type
 * @property numeric $value
 * @property string $unit
 * @property numeric|null $baseline_value
 * @property numeric|null $target_value
 * @property string|null $previous_period_value
 * @property string|null $change_absolute
 * @property numeric|null $change_percentage
 * @property string|null $trend
 * @property string|null $trend_score
 * @property string|null $calculation_method
 * @property string|null $data_sources
 * @property string|null $calculation_details
 * @property string|null $data_quality_score
 * @property string|null $assumptions
 * @property numeric|null $co2_emissions_kg
 * @property string|null $co2_avoided_kg
 * @property string|null $carbon_offset_kg
 * @property string|null $carbon_intensity
 * @property string|null $renewable_energy_kwh
 * @property string|null $total_energy_kwh
 * @property numeric|null $renewable_percentage
 * @property string|null $fossil_fuel_displacement_kwh
 * @property numeric|null $cost_savings_eur
 * @property string|null $investment_recovery_eur
 * @property string|null $economic_impact_eur
 * @property int|null $jobs_created
 * @property int|null $jobs_sustained
 * @property int|null $communities_impacted
 * @property int|null $people_benefited
 * @property string|null $social_value_eur
 * @property int|null $education_hours
 * @property int|null $awareness_campaigns
 * @property bool $is_certified
 * @property string|null $certification_body
 * @property string|null $certification_number
 * @property string|null $certification_date
 * @property string|null $certification_expires_at
 * @property string $verification_status
 * @property string|null $industry_benchmark
 * @property string|null $regional_benchmark
 * @property string|null $best_practice_benchmark
 * @property string|null $performance_rating
 * @property bool $contributes_to_sdg
 * @property string|null $sdg_targets
 * @property int $paris_agreement_aligned
 * @property string|null $sustainability_goals
 * @property int $include_in_reports
 * @property bool $is_public
 * @property string|null $public_description
 * @property string|null $visualization_config
 * @property int $report_priority
 * @property int|null $energy_cooperative_id
 * @property int|null $user_id
 * @property int|null $energy_report_id
 * @property int|null $calculated_by_id
 * @property int|null $verified_by_id
 * @property string|null $calculated_at
 * @property string|null $verified_at
 * @property string|null $audit_trail
 * @property string|null $notes
 * @property string|null $metadata
 * @property bool $alert_enabled
 * @property string|null $alert_threshold_min
 * @property string|null $alert_threshold_max
 * @property string $alert_status
 * @property string|null $last_alert_sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EnergyCooperative|null $energyCooperative
 * @property-read \App\Models\EnergyReport|null $energyReport
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric byCategory(string $category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric certified()
 * @method static \Database\Factories\SustainabilityMetricFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric improving()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereAlertEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereAlertStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereAlertThresholdMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereAlertThresholdMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereAssumptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereAuditTrail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereAwarenessCampaigns($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereBaselineValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereBestPracticeBenchmark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCalculatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCalculatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCalculationDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCalculationMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCarbonIntensity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCarbonOffsetKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCertificationBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCertificationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCertificationExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCertificationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereChangeAbsolute($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereChangePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCo2AvoidedKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCo2EmissionsKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCommunitiesImpacted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereContributesToSdg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCostSavingsEur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereDataQualityScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereDataSources($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereEconomicImpactEur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereEducationHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereEnergyCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereEnergyReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereEntityName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereFossilFuelDisplacementKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereIncludeInReports($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereIndustryBenchmark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereInvestmentRecoveryEur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereIsCertified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereJobsCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereJobsSustained($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereLastAlertSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereMeasurementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereMetricCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereMetricCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereMetricName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereMetricType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereParisAgreementAligned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric wherePeopleBenefited($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric wherePerformanceRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric wherePreviousPeriodValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric wherePublicDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereRegionalBenchmark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereRenewableEnergyKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereRenewablePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereReportPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereSdgTargets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereSocialValueEur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereSustainabilityGoals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereTargetValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereTotalEnergyKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereTrend($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereTrendScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereVerificationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereVerifiedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SustainabilityMetric whereVisualizationConfig($value)
 */
	class SustainabilityMetric extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $color
 * @property string|null $icon
 * @property string|null $description
 * @property string $tag_type
 * @property string $type
 * @property int $usage_count
 * @property bool $is_featured
 * @property bool $is_active
 * @property int $sort_order
 * @property array<array-key, mixed>|null $metadata
 * @property int|null $organization_id
 * @property string $language
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Provider> $providers
 * @property-read int|null $providers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Taggable> $taggables
 * @property-read int|null $taggables_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag byType(string $type)
 * @method static \Database\Factories\TagFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag popular(int $minUsage = 10)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereTagType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereUsageCount($value)
 */
	class Tag extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $tag_id
 * @property string $taggable_type
 * @property int $taggable_id
 * @property string $tagged_at
 * @property int|null $tagged_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tag $tag
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $taggable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable byTag(int $tagId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable byWeight(float $minWeight = '1')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable whereTaggableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable whereTaggableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable whereTaggedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable whereTaggedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Taggable whereUpdatedAt($value)
 */
	class Taggable extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $template_type
 * @property string|null $category
 * @property string|null $subcategory
 * @property numeric|null $estimated_duration_hours
 * @property numeric|null $estimated_cost
 * @property array<array-key, mixed>|null $required_skills
 * @property array<array-key, mixed>|null $required_tools
 * @property array<array-key, mixed>|null $required_parts
 * @property array<array-key, mixed>|null $safety_requirements
 * @property array<array-key, mixed>|null $technical_requirements
 * @property array<array-key, mixed>|null $quality_standards
 * @property array<array-key, mixed>|null $checklist_items
 * @property array<array-key, mixed>|null $work_instructions
 * @property bool $is_active
 * @property bool $is_standard
 * @property string $version
 * @property array<array-key, mixed>|null $tags
 * @property string|null $notes
 * @property string|null $department
 * @property string $priority
 * @property string $risk_level
 * @property array<array-key, mixed>|null $compliance_requirements
 * @property array<array-key, mixed>|null $documentation_required
 * @property bool $training_required
 * @property bool $certification_required
 * @property array<array-key, mixed>|null $environmental_considerations
 * @property string|null $budget_code
 * @property string|null $cost_center
 * @property string|null $project_code
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceSchedule> $maintenanceSchedules
 * @property-read int|null $maintenance_schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceTask> $maintenanceTasks
 * @property-read int|null $maintenance_tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate byCategory($category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate byDepartment($department)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate byRiskLevel($riskLevel)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate byType($type)
 * @method static \Database\Factories\TaskTemplateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate standard()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereBudgetCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereCertificationRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereChecklistItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereComplianceRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereCostCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereDocumentationRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereEnvironmentalConsiderations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereEstimatedCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereEstimatedDurationHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereIsStandard($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereProjectCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereQualityStandards($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereRequiredParts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereRequiredSkills($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereRequiredTools($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereRiskLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereSafetyRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereSubcategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereTechnicalRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereTemplateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereTrainingRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate whereWorkInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskTemplate withoutTrashed()
 */
	class TaskTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $calculation_number
 * @property string $name
 * @property string|null $description
 * @property string $tax_type
 * @property string $calculation_type
 * @property string $status
 * @property string $priority
 * @property int|null $entity_id
 * @property string|null $entity_type
 * @property int|null $transaction_id
 * @property string|null $transaction_type
 * @property \Illuminate\Support\Carbon $tax_period_start
 * @property \Illuminate\Support\Carbon $tax_period_end
 * @property \Illuminate\Support\Carbon $calculation_date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $payment_date
 * @property numeric $taxable_amount
 * @property numeric $tax_rate
 * @property numeric $tax_amount
 * @property numeric|null $tax_base_amount
 * @property numeric $exemption_amount
 * @property numeric $deduction_amount
 * @property numeric $credit_amount
 * @property numeric $net_tax_amount
 * @property numeric $penalty_amount
 * @property numeric $interest_amount
 * @property numeric $total_amount_due
 * @property numeric $amount_paid
 * @property numeric $amount_remaining
 * @property string $currency
 * @property numeric $exchange_rate
 * @property string|null $tax_jurisdiction
 * @property string|null $tax_authority
 * @property string|null $tax_registration_number
 * @property string|null $tax_filing_frequency
 * @property string|null $tax_filing_method
 * @property bool $is_estimated
 * @property bool $is_final
 * @property bool $is_amended
 * @property string|null $amendment_reason
 * @property string|null $calculation_notes
 * @property string|null $review_notes
 * @property string|null $approval_notes
 * @property array<array-key, mixed>|null $calculation_details
 * @property array<array-key, mixed>|null $tax_breakdown
 * @property array<array-key, mixed>|null $supporting_documents
 * @property array<array-key, mixed>|null $audit_trail
 * @property array<array-key, mixed>|null $tags
 * @property int|null $calculated_by
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $applied_by
 * @property \Illuminate\Support\Carbon|null $applied_at
 * @property int $created_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $appliedBy
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User|null $calculatedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $entity
 * @property-read \App\Models\User|null $reviewedBy
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $transaction
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation amended()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation applied()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation automatic()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation batch()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byAmountRange($minAmount, $maxAmount)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byCalculationDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byCalculationType($calculationType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byCurrency($currency)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byDueDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byEntity($entityId, $entityType = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byPaymentDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byTaxAuthority($authority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byTaxJurisdiction($jurisdiction)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byTaxPeriod($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byTaxRateRange($minRate, $maxRate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byTaxType($taxType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation byTransaction($transactionId, $transactionType = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation calculated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation carbonTax()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation customsDuty()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation draft()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation dueSoon($days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation energyTax()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation environmentalTax()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation error()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation estimated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation eventTriggered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation exciseTax()
 * @method static \Database\Factories\TaxCalculationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation final()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation incomeTax()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation lowPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation manual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation normalPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation paid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation propertyTax()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation realTime()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation reviewed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation salesTax()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation scheduled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation unpaid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation valueAddedTax()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereAmendmentReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereAmountPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereAmountRemaining($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereAppliedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereAppliedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereApprovalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereAuditTrail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereCalculatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereCalculationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereCalculationDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereCalculationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereCalculationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereCalculationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereCreditAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereDeductionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereExemptionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereInterestAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereIsAmended($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereIsEstimated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereIsFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereNetTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation wherePenaltyAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereReviewNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereSupportingDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxAuthority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxBaseAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxBreakdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxFilingFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxFilingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxJurisdiction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxPeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxPeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxableAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTotalAmountDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTransactionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereUpdatedAt($value)
 */
	class TaxCalculation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $created_by_user_id
 * @property int|null $organization_id
 * @property bool $is_open
 * @property int|null $max_members
 * @property string|null $logo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamMembership> $activeMemberships
 * @property-read int|null $active_memberships_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $admins
 * @property-read int|null $admins_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $allMembers
 * @property-read int|null $all_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamChallengeProgress> $challengeProgress
 * @property-read int|null $challenge_progress_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Challenge> $challenges
 * @property-read int|null $challenges_count
 * @property-read \App\Models\User $createdBy
 * @property-read int|null $available_slots
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamMembership> $memberships
 * @property-read int|null $memberships_count
 * @property-read \App\Models\Organization|null $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team byOrganization(int $organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team closed()
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team open()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereIsOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereMaxMembers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withSpace()
 */
	class Team extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $team_id
 * @property int $challenge_id
 * @property numeric $progress_kwh
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Challenge $challenge
 * @property-read int|null $days_since_completion
 * @property-read float $progress_percentage
 * @property-read float $remaining_kwh
 * @property-read int $team_rank
 * @property-read \App\Models\Team $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress byChallenge(int $challengeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress byTeam(int $teamId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress completed()
 * @method static \Database\Factories\TeamChallengeProgressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress inProgress()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress whereChallengeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress whereProgressKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamChallengeProgress whereUpdatedAt($value)
 */
	class TeamChallengeProgress extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $team_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $joined_at
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $left_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \DateInterval|null $duration
 * @property-read int|null $duration_in_days
 * @property-read bool $is_active
 * @property-read \App\Models\Team $team
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership admins()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership byRole(string $role)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership byTeam(int $teamId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership byUser(int $userId)
 * @method static \Database\Factories\TeamMembershipFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership members()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership moderators()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership whereLeftAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembership whereUserId($value)
 */
	class TeamMembership extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $slug
 * @property string|null $title
 * @property string|null $subtitle
 * @property string $text
 * @property string $version
 * @property string $language
 * @property int|null $organization_id
 * @property bool $is_draft
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $author_id
 * @property int|null $parent_id
 * @property string|null $excerpt
 * @property int|null $reading_time
 * @property string|null $seo_focus_keyword
 * @property int $number_of_views
 * @property array<array-key, mixed>|null $search_keywords
 * @property string|null $internal_notes
 * @property \Illuminate\Support\Carbon|null $last_reviewed_at
 * @property string|null $accessibility_notes
 * @property string|null $reading_level
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property int|null $approved_by_user_id
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User|null $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TextContent> $children
 * @property-read int|null $children_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PageComponent> $pageComponents
 * @property-read int|null $page_components_count
 * @property-read TextContent|null $parent
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent draft()
 * @method static \Database\Factories\TextContentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent forCurrentOrganization()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent forOrganization($organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent inCurrentLanguage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent inLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent shouldBePublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereAccessibilityNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereApprovedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereLastReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereNumberOfViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereReadingLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereReadingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereSearchKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereSeoFocusKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereUpdatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent withCurrentLanguageFallback()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TextContent withFallback(string $language, ?string $fallback = null)
 */
	class TextContent extends \Eloquent implements \App\Contracts\Cacheable, \App\Contracts\Publishable, \App\Contracts\Multilingual {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $transaction_code
 * @property string|null $reference
 * @property string|null $batch_id
 * @property int $user_id
 * @property int|null $payment_id
 * @property int|null $invoice_id
 * @property int|null $energy_cooperative_id
 * @property string $type
 * @property string $category
 * @property string $status
 * @property numeric $amount
 * @property numeric $fee
 * @property numeric $net_amount
 * @property string $currency
 * @property numeric|null $exchange_rate
 * @property numeric|null $original_amount
 * @property string|null $original_currency
 * @property string|null $from_account_type
 * @property string|null $from_account_id
 * @property string|null $to_account_type
 * @property string|null $to_account_id
 * @property numeric|null $balance_before
 * @property numeric|null $balance_after
 * @property numeric|null $energy_amount_kwh
 * @property numeric|null $energy_price_per_kwh
 * @property string|null $energy_contract_id
 * @property \Illuminate\Support\Carbon|null $energy_delivery_date
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $settled_at
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property string|null $processor
 * @property string|null $processor_transaction_id
 * @property array<array-key, mixed>|null $processor_response
 * @property string|null $authorization_code
 * @property string $description
 * @property string|null $notes
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $failure_reason
 * @property int|null $created_by_id
 * @property int|null $approved_by_id
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property bool $is_internal
 * @property bool $is_test
 * @property bool $is_recurring
 * @property bool $requires_approval
 * @property bool $is_reversible
 * @property int|null $parent_transaction_id
 * @property int|null $reversal_transaction_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Transaction> $childTransactions
 * @property-read int|null $child_transactions_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\EnergyCooperative|null $energyCooperative
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read Transaction|null $parentTransaction
 * @property-read \App\Models\Payment|null $payment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $refunds
 * @property-read int|null $refunds_count
 * @property-read Transaction|null $reversalTransaction
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WalletTransaction> $walletTransactions
 * @property-read int|null $wallet_transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction byBatch($batchId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction byCategory($category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction external()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction failed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction forUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction internal()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction production()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction requiringApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction test()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereApprovedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereAuthorizationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereBalanceBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereEnergyAmountKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereEnergyContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereEnergyCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereEnergyDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereEnergyPricePerKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereFailedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereFailureReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereFromAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereFromAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereIsInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereIsReversible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereIsTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereOriginalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereOriginalCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereParentTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereProcessor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereProcessorResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereProcessorTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereRequiresApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereReversalTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereSettledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereToAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereToAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTransactionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUserId($value)
 */
	class Transaction extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserDevice> $activeDevices
 * @property-read int|null $active_devices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $approvedRefunds
 * @property-read int|null $approved_refunds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $approvedTransactions
 * @property-read int|null $approved_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WalletTransaction> $approvedWalletTransactions
 * @property-read int|null $approved_wallet_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventAttendance> $attendedEvents
 * @property-read int|null $attended_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventAttendance> $cancelledEvents
 * @property-read int|null $cancelled_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConsentLog> $consentLogs
 * @property-read int|null $consent_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $createdInvoices
 * @property-read int|null $created_invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $createdPayments
 * @property-read int|null $created_payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyReport> $createdReports
 * @property-read int|null $created_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $createdTransactions
 * @property-read int|null $created_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WalletTransaction> $createdWalletTransactions
 * @property-read int|null $created_wallet_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserDevice> $devices
 * @property-read int|null $devices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnergyReport> $energyReports
 * @property-read int|null $energy_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventAttendance> $eventAttendances
 * @property-read int|null $event_attendances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventAttendance> $noShowEvents
 * @property-read int|null $no_show_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NotificationSetting> $notificationSettings
 * @property-read int|null $notification_settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PerformanceIndicator> $performanceIndicators
 * @property-read int|null $performance_indicators_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $processedRefunds
 * @property-read int|null $processed_refunds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $recentNotifications
 * @property-read int|null $recent_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $refunds
 * @property-read int|null $refunds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventAttendance> $registeredEvents
 * @property-read int|null $registered_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $requestedRefunds
 * @property-read int|null $requested_refunds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Survey> $respondedSurveys
 * @property-read int|null $responded_surveys_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserSettings> $settings
 * @property-read int|null $settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SurveyResponse> $surveyResponses
 * @property-read int|null $survey_responses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SustainabilityMetric> $sustainabilityMetrics
 * @property-read int|null $sustainability_metrics_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $unreadNotifications
 * @property-read int|null $unread_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserSubscription> $userSubscriptions
 * @property-read int|null $user_subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WalletTransaction> $walletTransactions
 * @property-read int|null $wallet_transactions_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $achievement_id
 * @property \Illuminate\Support\Carbon $earned_at
 * @property string|null $custom_message
 * @property bool $reward_granted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Achievement $achievement
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement byAchievement(int $achievementId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement byUser(int $userId)
 * @method static \Database\Factories\UserAchievementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement orderedByEarnedDate(string $direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement pendingReward()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement rewardGranted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereAchievementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereCustomMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereEarnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereRewardGranted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereUserId($value)
 */
	class UserAchievement extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property numeric $quantity
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string $source_type
 * @property string $status
 * @property numeric|null $current_value
 * @property numeric|null $purchase_price
 * @property numeric|null $daily_yield
 * @property numeric $total_yield_generated
 * @property numeric|null $efficiency_rating
 * @property numeric|null $maintenance_cost
 * @property \Illuminate\Support\Carbon|null $last_maintenance_date
 * @property \Illuminate\Support\Carbon|null $next_maintenance_date
 * @property bool $auto_reinvest
 * @property numeric|null $reinvest_threshold
 * @property numeric|null $reinvest_percentage
 * @property bool $is_transferable
 * @property bool $is_delegatable
 * @property int|null $delegated_to_user_id
 * @property array<array-key, mixed>|null $metadata
 * @property numeric|null $estimated_annual_return
 * @property numeric|null $actual_annual_return
 * @property array<array-key, mixed>|null $performance_history
 * @property bool $notifications_enabled
 * @property array<array-key, mixed>|null $alert_preferences
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BalanceTransaction> $balanceTransactions
 * @property-read int|null $balance_transactions_count
 * @property-read \App\Models\User|null $delegatedToUser
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset autoReinvest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset bySource(string $source)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset expiring(int $days = 30)
 * @method static \Database\Factories\UserAssetFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset needsMaintenance()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereActualAnnualReturn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereAlertPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereAutoReinvest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereCurrentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereDailyYield($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereDelegatedToUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereEfficiencyRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereEstimatedAnnualReturn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereIsDelegatable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereIsTransferable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereLastMaintenanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereMaintenanceCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereNextMaintenanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereNotificationsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset wherePerformanceHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereReinvestPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereReinvestThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereTotalYieldGenerated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAsset whereUserId($value)
 */
	class UserAsset extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $challenge_id
 * @property numeric $progress_kwh
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EnergyChallenge $challenge
 * @property-read mixed $days_remaining
 * @property-read mixed $estimated_completion_date
 * @property-read mixed $progress_percentage
 * @property-read mixed $remaining_kwh
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress byChallenge($challengeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress byUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress completed()
 * @method static \Database\Factories\UserChallengeProgressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress inProgress()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress recent($days = 7)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress whereChallengeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress whereProgressKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserChallengeProgress whereUserId($value)
 */
	class UserChallengeProgress extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $device_name
 * @property string $device_type
 * @property string|null $platform
 * @property \Illuminate\Support\Carbon|null $last_seen_at
 * @property string|null $push_token
 * @property string|null $user_agent
 * @property string|null $ip_address
 * @property bool $is_current
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $device_info
 * @property-read string $device_type_name
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice current()
 * @method static \Database\Factories\UserDeviceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice ofType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice recentlyActive(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice revoked()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereIsCurrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereLastSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice wherePushToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereRevokedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDevice withPushToken()
 */
	class UserDevice extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $organization_id
 * @property int $organization_role_id
 * @property \Illuminate\Support\Carbon $assigned_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization $organization
 * @property-read \App\Models\OrganizationRole $organizationRole
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\UserOrganizationRoleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOrganizationRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOrganizationRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOrganizationRole query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOrganizationRole whereAssignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOrganizationRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOrganizationRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOrganizationRole whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOrganizationRole whereOrganizationRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOrganizationRole whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOrganizationRole whereUserId($value)
 */
	class UserOrganizationRole extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $avatar
 * @property string|null $bio
 * @property string|null $municipality_id
 * @property \Illuminate\Support\Carbon|null $join_date
 * @property string|null $role_in_cooperative
 * @property bool $profile_completed
 * @property bool $newsletter_opt_in
 * @property bool $show_in_rankings
 * @property numeric $co2_avoided_total
 * @property numeric $kwh_produced_total
 * @property int $points_total
 * @property array<array-key, mixed>|null $badges_earned
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property int $organization_id
 * @property string|null $team_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $age
 * @property-read int $municipality_rank
 * @property-read int $organization_rank
 * @property-read \App\Models\Organization $organization
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile byMunicipality(string $municipalityId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile byOrganization(int $organizationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile completed()
 * @method static \Database\Factories\UserProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile inRankings()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile orderedByPoints()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereBadgesEarned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereCo2AvoidedTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereJoinDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereKwhProducedTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereMunicipalityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereNewsletterOptIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile wherePointsTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereProfileCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereRoleInCooperative($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereShowInRankings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereUserId($value)
 */
	class UserProfile extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $language
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $timezone
 * @property string $theme
 * @property bool $notifications_enabled
 * @property bool $email_notifications
 * @property bool $push_notifications
 * @property bool $sms_notifications
 * @property bool $marketing_emails
 * @property bool $newsletter_subscription
 * @property string $privacy_level
 * @property string $profile_visibility
 * @property bool $show_achievements
 * @property bool $show_statistics
 * @property bool $show_activity
 * @property string $date_format
 * @property string $time_format
 * @property string $currency
 * @property string $measurement_unit
 * @property string $energy_unit
 * @property array<array-key, mixed>|null $custom_settings
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\UserSettingsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereCustomSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereEmailNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereEnergyUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereMarketingEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereMeasurementUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereNewsletterSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereNotificationsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings wherePrivacyLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereProfileVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings wherePushNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereShowAchievements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereShowActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereShowStatistics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereSmsNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereTimeFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereUserId($value)
 */
	class UserSettings extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $energy_cooperative_id
 * @property int|null $provider_id
 * @property string $subscription_type
 * @property string $plan_name
 * @property string|null $plan_description
 * @property string $service_category
 * @property array<array-key, mixed>|null $included_services
 * @property string $status
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property \Illuminate\Support\Carbon|null $trial_end_date
 * @property \Illuminate\Support\Carbon|null $next_billing_date
 * @property \Illuminate\Support\Carbon|null $cancellation_date
 * @property \Illuminate\Support\Carbon|null $last_renewed_at
 * @property string $billing_frequency
 * @property numeric $price
 * @property string $currency
 * @property numeric|null $discount_percentage
 * @property numeric|null $discount_amount
 * @property string|null $promo_code
 * @property numeric|null $energy_allowance_kwh
 * @property numeric|null $overage_rate_per_kwh
 * @property array<array-key, mixed>|null $peak_hours_config
 * @property bool $includes_renewable_energy
 * @property numeric|null $renewable_percentage
 * @property array<array-key, mixed>|null $preferences
 * @property array<array-key, mixed>|null $notification_settings
 * @property bool $auto_renewal
 * @property int $renewal_reminder_days
 * @property numeric $current_period_usage_kwh
 * @property numeric $total_usage_kwh
 * @property numeric $current_period_cost
 * @property numeric $total_cost_paid
 * @property int $billing_cycles_completed
 * @property int $loyalty_points
 * @property array<array-key, mixed>|null $benefits_earned
 * @property numeric $referral_credits
 * @property int $referrals_count
 * @property string|null $payment_method
 * @property array<array-key, mixed>|null $payment_details
 * @property \Illuminate\Support\Carbon|null $last_payment_date
 * @property numeric|null $last_payment_amount
 * @property string $payment_status
 * @property string|null $cancellation_reason
 * @property string|null $cancellation_feedback
 * @property bool $eligible_for_reactivation
 * @property \Illuminate\Support\Carbon|null $reactivation_deadline
 * @property int $support_tickets_count
 * @property numeric|null $satisfaction_rating
 * @property string|null $special_notes
 * @property array<array-key, mixed>|null $metadata
 * @property array<array-key, mixed>|null $integration_settings
 * @property string|null $external_subscription_id
 * @property array<array-key, mixed>|null $tags
 * @property \Illuminate\Support\Carbon|null $activated_at
 * @property \Illuminate\Support\Carbon|null $paused_at
 * @property \Illuminate\Support\Carbon|null $suspended_at
 * @property int|null $created_by
 * @property int|null $managed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\EnergyCooperative|null $energyCooperative
 * @property-read \App\Models\User|null $managedBy
 * @property-read \App\Models\Provider|null $provider
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription autoRenewal()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription byBillingFrequency($frequency)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription dueForRenewal($days = 7)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription expired()
 * @method static \Database\Factories\UserSubscriptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription renewableEnergy()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereActivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereAutoRenewal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereBenefitsEarned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereBillingCyclesCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereBillingFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereCancellationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereCancellationFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereCurrentPeriodCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereCurrentPeriodUsageKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereDiscountPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereEligibleForReactivation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereEnergyAllowanceKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereEnergyCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereExternalSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereIncludedServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereIncludesRenewableEnergy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereIntegrationSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereLastPaymentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereLastPaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereLastRenewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereLoyaltyPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereManagedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereNextBillingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereNotificationSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereOverageRatePerKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription wherePausedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription wherePaymentDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription wherePeakHoursConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription wherePlanDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription wherePlanName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription wherePreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription wherePromoCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereReactivationDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereReferralCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereReferralsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereRenewablePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereRenewalReminderDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereSatisfactionRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereServiceCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereSpecialNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereSubscriptionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereSupportTicketsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereSuspendedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereTotalCostPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereTotalUsageKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereTrialEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubscription whereUserId($value)
 */
	class UserSubscription extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $legal_name
 * @property string|null $tax_id
 * @property string|null $registration_number
 * @property string $vendor_type
 * @property string|null $industry
 * @property string|null $description
 * @property string|null $contact_person
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $country
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $payment_terms
 * @property numeric|null $credit_limit
 * @property numeric $current_balance
 * @property string $currency
 * @property numeric $tax_rate
 * @property numeric $discount_rate
 * @property numeric|null $rating
 * @property bool $is_active
 * @property bool $is_verified
 * @property bool $is_preferred
 * @property bool $is_blacklisted
 * @property \Illuminate\Support\Carbon|null $contract_start_date
 * @property \Illuminate\Support\Carbon|null $contract_end_date
 * @property array<array-key, mixed>|null $contract_terms
 * @property array<array-key, mixed>|null $insurance_coverage
 * @property array<array-key, mixed>|null $certifications
 * @property array<array-key, mixed>|null $licenses
 * @property array<array-key, mixed>|null $performance_metrics
 * @property array<array-key, mixed>|null $quality_standards
 * @property array<array-key, mixed>|null $delivery_terms
 * @property array<array-key, mixed>|null $warranty_terms
 * @property array<array-key, mixed>|null $return_policy
 * @property array<array-key, mixed>|null $tags
 * @property string|null $logo
 * @property array<array-key, mixed>|null $documents
 * @property array<array-key, mixed>|null $bank_account
 * @property array<array-key, mixed>|null $payment_methods
 * @property array<array-key, mixed>|null $contact_history
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string $status
 * @property string $risk_level
 * @property string $compliance_status
 * @property int|null $audit_frequency
 * @property \Illuminate\Support\Carbon|null $last_audit_date
 * @property \Illuminate\Support\Carbon|null $next_audit_date
 * @property array<array-key, mixed>|null $financial_stability
 * @property array<array-key, mixed>|null $market_reputation
 * @property array<array-key, mixed>|null $competitor_analysis
 * @property array<array-key, mixed>|null $strategic_importance
 * @property array<array-key, mixed>|null $dependencies
 * @property array<array-key, mixed>|null $alternatives
 * @property array<array-key, mixed>|null $cost_benefit_analysis
 * @property array<array-key, mixed>|null $performance_reviews
 * @property array<array-key, mixed>|null $improvement_plans
 * @property array<array-key, mixed>|null $escalation_procedures
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceSchedule> $maintenanceSchedules
 * @property-read int|null $maintenance_schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceTask> $maintenanceTasks
 * @property-read int|null $maintenance_tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor blacklisted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor byComplianceStatus($complianceStatus)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor byContractStatus($status = 'active')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor byCreditLimit($minLimit, $maxLimit = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor byIndustry($industry)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor byLocation($country = null, $state = null, $city = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor byPaymentTerms($terms)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor byRating($minRating)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor byRiskLevel($riskLevel)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor compliant()
 * @method static \Database\Factories\VendorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor highRating()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor highRisk()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor needsAudit()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor nonCompliant()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor preferred()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor verified()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereAlternatives($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereAuditFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereBankAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCertifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCompetitorAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereComplianceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereContactHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereContactPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereContractEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereContractStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereContractTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCostBenefitAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCreditLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereDeliveryTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereDependencies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereDiscountRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereEscalationProcedures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereFinancialStability($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereImprovementPlans($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereIndustry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereInsuranceCoverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereIsBlacklisted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereIsPreferred($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereLastAuditDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereLegalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereLicenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereMarketReputation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereNextAuditDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor wherePaymentMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor wherePerformanceMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor wherePerformanceReviews($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereQualityStandards($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereReturnPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereRiskLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereStrategicImportance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereVendorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereWarrantyTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor withoutTrashed()
 */
	class Vendor extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $transaction_code
 * @property string|null $reference
 * @property int $user_id
 * @property int|null $related_user_id
 * @property int|null $transaction_id
 * @property int|null $energy_cooperative_id
 * @property string $type
 * @property string|null $subtype
 * @property string $status
 * @property string $token_type
 * @property numeric $amount
 * @property numeric|null $rate
 * @property string $currency
 * @property numeric|null $equivalent_value
 * @property numeric|null $balance_before
 * @property numeric|null $balance_after
 * @property numeric|null $energy_amount_kwh
 * @property numeric|null $energy_price_per_kwh
 * @property string|null $energy_source
 * @property bool|null $is_renewable
 * @property \Illuminate\Support\Carbon|null $energy_generation_date
 * @property \Illuminate\Support\Carbon|null $energy_consumption_date
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $locked_until
 * @property \Illuminate\Support\Carbon|null $available_at
 * @property string $description
 * @property string|null $notes
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $source_wallet_id
 * @property string|null $source_transaction_code
 * @property numeric|null $source_amount
 * @property string|null $source_token_type
 * @property bool $has_expiration
 * @property int|null $expiration_days
 * @property bool $is_locked
 * @property string|null $lock_reason
 * @property bool $requires_approval
 * @property int|null $approved_by_id
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $approval_notes
 * @property int|null $created_by_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property bool $is_internal
 * @property bool $is_test
 * @property bool $is_reversible
 * @property int|null $reversal_transaction_id
 * @property \Illuminate\Support\Carbon|null $reversed_at
 * @property string|null $reversal_reason
 * @property string|null $batch_id
 * @property int|null $batch_sequence
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\EnergyCooperative|null $energyCooperative
 * @property-read \App\Models\User|null $relatedUser
 * @property-read WalletTransaction|null $reversalTransaction
 * @property-read \App\Models\Transaction|null $transaction
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction byBatch($batchId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction byTokenType($tokenType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction credits()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction debits()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction energyRelated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction expired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction expiringSoon($days = 7)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction forUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction locked()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction production()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction test()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereApprovalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereApprovedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereAvailableAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereBalanceBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereBatchSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereEnergyAmountKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereEnergyConsumptionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereEnergyCooperativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereEnergyGenerationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereEnergyPricePerKwh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereEnergySource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereEquivalentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereExpirationDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereHasExpiration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereIsInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereIsRenewable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereIsReversible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereIsTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereLockReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereLockedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereRelatedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereRequiresApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereReversalReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereReversalTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereReversedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereSourceAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereSourceTokenType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereSourceTransactionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereSourceWalletId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereSubtype($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereTokenType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereTransactionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletTransaction whereUserId($value)
 */
	class WalletTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $municipality_id
 * @property numeric|null $temperature
 * @property numeric|null $cloud_coverage
 * @property numeric|null $solar_radiation
 * @property \Illuminate\Support\Carbon $timestamp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $age
 * @property-read string $cloud_coverage_display
 * @property-read bool $is_recent
 * @property-read string $solar_radiation_display
 * @property-read string $temperature_display
 * @property-read \App\Models\Municipality $municipality
 * @method static \Database\Factories\WeatherSnapshotFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot forMunicipality($municipalityId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot inDateRange(\Carbon\Carbon $from, \Carbon\Carbon $to)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot inProvince($provinceId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot inRegion($regionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot latest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot optimalSolarConditions()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot recentReadings(int $hours = 24)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot thisMonth()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot thisWeek()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot today()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot whereCloudCoverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot whereMunicipalityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot whereSolarRadiation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot whereTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeatherSnapshot withGoodSolarConditions(float $minRadiation = '500')
 */
	class WeatherSnapshot extends \Eloquent {}
}

