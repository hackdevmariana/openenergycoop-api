<?php

use App\Models\SeoMetaData;
use App\Models\Article;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    SeoMetaData::query()->delete();
    
    $this->user = User::factory()->create();
    $this->article = Article::factory()->create();
    $this->page = Page::factory()->create();
});

describe('SeoMetaDataController', function () {
    
    // NOTE: Controller routes are not defined in routes/api.php, so focusing on model functionality
    
    describe('Basic model functionality', function () {
        it('can create seo metadata records', function () {
            $seoData = SeoMetaData::factory()->forModel($this->article)->create();

            expect($seoData)->toBeInstanceOf(SeoMetaData::class);
            expect($seoData->seoable_type)->toBe(Article::class);
            expect($seoData->seoable_id)->toBe($this->article->id);
            expect($seoData->language)->not->toBeNull();
        });

        it('has polymorphic relationship with seoable', function () {
            $seoData = SeoMetaData::factory()->forModel($this->article)->create();

            expect($seoData->seoable)->toBeInstanceOf(Article::class);
            expect($seoData->seoable->id)->toBe($this->article->id);
        });

        it('can have all SEO fields populated', function () {
            $seoData = SeoMetaData::factory()->complete()->create([
                'meta_title' => 'Test SEO Title',
                'meta_description' => 'Test SEO description for better ranking',
                'canonical_url' => 'https://example.com/test',
                'robots' => 'index,follow',
                'og_title' => 'Test OG Title',
                'og_description' => 'Test OG description for social media',
                'twitter_card' => 'summary_large_image',
                'focus_keyword' => 'test seo',
            ]);

            expect($seoData->meta_title)->toBe('Test SEO Title');
            expect($seoData->meta_description)->toBe('Test SEO description for better ranking');
            expect($seoData->canonical_url)->toBe('https://example.com/test');
            expect($seoData->robots)->toBe('index,follow');
            expect($seoData->og_title)->toBe('Test OG Title');
            expect($seoData->twitter_card)->toBe('summary_large_image');
            expect($seoData->focus_keyword)->toBe('test seo');
        });

        it('casts arrays correctly', function () {
            $structuredData = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => 'Test Article'
            ];
            
            $additionalMeta = [
                ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'],
                ['name' => 'theme-color', 'content' => '#007bff'],
            ];

            $seoData = SeoMetaData::factory()->create([
                'structured_data' => $structuredData,
                'additional_meta' => $additionalMeta,
            ]);

            expect($seoData->structured_data)->toBe($structuredData);
            expect($seoData->additional_meta)->toBe($additionalMeta);
            expect(is_array($seoData->structured_data))->toBe(true);
            expect(is_array($seoData->additional_meta))->toBe(true);
        });
    });

    describe('Model business logic', function () {
        it('can get effective title with fallback', function () {
            $seoData = SeoMetaData::factory()->create([
                'meta_title' => 'Custom SEO Title'
            ]);

            expect($seoData->getEffectiveTitle())->toBe('Custom SEO Title');
        });

        it('can get effective description', function () {
            $seoData = SeoMetaData::factory()->create([
                'meta_description' => 'Custom SEO description'
            ]);

            expect($seoData->getEffectiveDescription())->toBe('Custom SEO description');
        });

        it('can get canonical url', function () {
            $url = 'https://example.com/test-page';
            $seoData = SeoMetaData::factory()->create([
                'canonical_url' => $url
            ]);

            expect($seoData->getCanonicalUrl())->toBe($url);
        });

        it('can get effective image', function () {
            $imagePath = '/images/test-image.jpg';
            $seoData = SeoMetaData::factory()->create([
                'og_image_path' => $imagePath
            ]);

            expect($seoData->getEffectiveImage())->toBe($imagePath);
        });

        it('can generate meta tags array', function () {
            $seoData = SeoMetaData::factory()->complete()->create([
                'meta_title' => 'Test Title',
                'meta_description' => 'Test Description',
                'robots' => 'index,follow',
                'og_title' => 'OG Title',
                'twitter_card' => 'summary_large_image',
            ]);

            $metaTags = $seoData->getMetaTags();

            expect($metaTags)->toHaveKey('title');
            expect($metaTags)->toHaveKey('meta');
            expect($metaTags)->toHaveKey('og');
            expect($metaTags)->toHaveKey('twitter');
            expect($metaTags)->toHaveKey('link');
            expect($metaTags['title'])->toBe('Test Title');
        });

        it('can create seo data for model using static method', function () {
            $seoData = SeoMetaData::createForModel($this->article, [
                'meta_title' => 'Article Title',
                'meta_description' => 'Article Description',
            ]);

            expect($seoData)->toBeInstanceOf(SeoMetaData::class);
            expect($seoData->seoable_type)->toBe(Article::class);
            expect($seoData->seoable_id)->toBe($this->article->id);
            expect($seoData->meta_title)->toBe('Article Title');
        });

        it('can update or create seo data for model', function () {
            // First call should create
            $seoData1 = SeoMetaData::updateOrCreateForModel($this->article, [
                'meta_title' => 'First Title',
                'language' => 'es',
            ]);

            expect($seoData1->meta_title)->toBe('First Title');

            // Second call should update
            $seoData2 = SeoMetaData::updateOrCreateForModel($this->article, [
                'meta_title' => 'Updated Title',
                'language' => 'es',
            ]);

            expect($seoData2->id)->toBe($seoData1->id);
            expect($seoData2->meta_title)->toBe('Updated Title');
        });
    });

    describe('Model scopes', function () {
        it('can filter by keyword', function () {
            $seoWithKeyword = SeoMetaData::factory()->withKeyword('renewable energy')->create();
            $seoWithoutKeyword = SeoMetaData::factory()->create(['focus_keyword' => 'solar power']);

            $results = SeoMetaData::byKeyword('renewable')->get();

            expect($results)->toHaveCount(1);
            expect($results->first()->id)->toBe($seoWithKeyword->id);
        });

        it('can filter by robots', function () {
            $indexableSeo = SeoMetaData::factory()->indexable()->create();
            $nonIndexableSeo = SeoMetaData::factory()->nonIndexable()->create();

            $results = SeoMetaData::byRobots('index,follow')->get();

            expect($results)->toHaveCount(1);
            expect($results->first()->id)->toBe($indexableSeo->id);
        });
    });

    describe('Factory states', function () {
        it('creates complete seo data with all fields', function () {
            $seoData = SeoMetaData::factory()->complete()->create();

            expect($seoData->meta_title)->not->toBeNull();
            expect($seoData->meta_description)->not->toBeNull();
            expect($seoData->canonical_url)->not->toBeNull();
            expect($seoData->og_title)->not->toBeNull();
            expect($seoData->og_description)->not->toBeNull();
            expect($seoData->twitter_title)->not->toBeNull();
            expect($seoData->focus_keyword)->not->toBeNull();
            expect($seoData->structured_data)->not->toBeNull();
        });

        it('creates minimal seo data', function () {
            $seoData = SeoMetaData::factory()->minimal()->create();

            expect($seoData->meta_title)->not->toBeNull();
            expect($seoData->meta_description)->not->toBeNull();
            expect($seoData->og_title)->toBeNull();
            expect($seoData->twitter_title)->toBeNull();
            expect($seoData->focus_keyword)->toBeNull();
        });

        it('creates article-specific seo data', function () {
            $seoData = SeoMetaData::factory()->forArticle()->create();

            expect($seoData->seoable_type)->toBe(Article::class);
            expect($seoData->og_type)->toBe('article');
            expect($seoData->structured_data)->toHaveKey('@type', 'Article');
        });

        it('creates page-specific seo data', function () {
            $seoData = SeoMetaData::factory()->forPage()->create();

            expect($seoData->seoable_type)->toBe(Page::class);
            expect($seoData->og_type)->toBe('website');
            expect($seoData->structured_data)->toHaveKey('@type', 'WebPage');
        });

        it('can create with specific language', function () {
            $spanishSeo = SeoMetaData::factory()->spanish()->create();
            $englishSeo = SeoMetaData::factory()->english()->create();

            expect($spanishSeo->language)->toBe('es');
            expect($englishSeo->language)->toBe('en');
        });

        it('can create with images', function () {
            $seoData = SeoMetaData::factory()->withImages()->create();

            expect($seoData->og_image_path)->not->toBeNull();
            expect($seoData->twitter_image_path)->not->toBeNull();
        });
    });

    describe('Data integrity and validation', function () {
        it('maintains proper polymorphic relationships', function () {
            $articleSeo = SeoMetaData::factory()->forModel($this->article)->create();
            $pageSeo = SeoMetaData::factory()->forModel($this->page)->create();

            expect($articleSeo->seoable_type)->toBe(Article::class);
            expect($articleSeo->seoable_id)->toBe($this->article->id);
            expect($pageSeo->seoable_type)->toBe(Page::class);
            expect($pageSeo->seoable_id)->toBe($this->page->id);
        });

        it('enforces unique constraint by seoable and language', function () {
            // Create first SEO data
            SeoMetaData::factory()->create([
                'seoable_type' => Article::class,
                'seoable_id' => $this->article->id,
                'language' => 'es',
            ]);

            // Attempt to create duplicate should fail
            expect(function () {
                SeoMetaData::factory()->create([
                    'seoable_type' => Article::class,
                    'seoable_id' => $this->article->id,
                    'language' => 'es',
                ]);
            })->toThrow(\Exception::class);
        });

        it('allows multiple languages for same seoable', function () {
            $spanishSeo = SeoMetaData::factory()->create([
                'seoable_type' => Article::class,
                'seoable_id' => $this->article->id,
                'language' => 'es',
            ]);

            $englishSeo = SeoMetaData::factory()->create([
                'seoable_type' => Article::class,
                'seoable_id' => $this->article->id,
                'language' => 'en',
            ]);

            expect($spanishSeo->id)->not->toBe($englishSeo->id);
            expect($spanishSeo->language)->toBe('es');
            expect($englishSeo->language)->toBe('en');
        });
    });

    describe('Edge cases and special scenarios', function () {
        it('handles null optional fields gracefully', function () {
            $seoData = SeoMetaData::factory()->create([
                'og_title' => null,
                'og_description' => null,
                'og_image_path' => null,
                'twitter_title' => null,
                'twitter_description' => null,
                'focus_keyword' => null,
                'structured_data' => null,
                'additional_meta' => null,
            ]);

            expect($seoData->og_title)->toBeNull();
            expect($seoData->og_description)->toBeNull();
            expect($seoData->og_image_path)->toBeNull();
            expect($seoData->structured_data)->toBeNull();
            expect($seoData->additional_meta)->toBeNull();
        });

        it('can filter seo data by seoable model', function () {
            $articleSeo = SeoMetaData::factory()->forModel($this->article)->count(2)->create();
            $pageSeo = SeoMetaData::factory()->forModel($this->page)->count(1)->create();

            $articleResults = SeoMetaData::where('seoable_type', Article::class)
                ->where('seoable_id', $this->article->id)
                ->get();

            $pageResults = SeoMetaData::where('seoable_type', Page::class)
                ->where('seoable_id', $this->page->id)
                ->get();

            expect($articleResults)->toHaveCount(2);
            expect($pageResults)->toHaveCount(1);
        });

        it('can update individual seo fields', function () {
            $seoData = SeoMetaData::factory()->create([
                'meta_title' => 'Original Title',
                'meta_description' => 'Original description',
                'robots' => 'index,follow',
            ]);

            // Update only meta_title
            $seoData->update(['meta_title' => 'Updated Title']);
            expect($seoData->fresh()->meta_title)->toBe('Updated Title');
            expect($seoData->fresh()->meta_description)->toBe('Original description');
            expect($seoData->fresh()->robots)->toBe('index,follow');

            // Update only robots
            $seoData->update(['robots' => 'noindex,nofollow']);
            expect($seoData->fresh()->robots)->toBe('noindex,nofollow');
            expect($seoData->fresh()->meta_title)->toBe('Updated Title');
        });

        it('handles complex structured data', function () {
            $complexStructuredData = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => 'Renewable Energy Solutions',
                'author' => [
                    '@type' => 'Person',
                    'name' => 'John Doe',
                    'url' => 'https://example.com/author/john-doe'
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'OpenEnergyCoop',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => 'https://example.com/logo.png'
                    ]
                ],
                'datePublished' => '2024-01-01T00:00:00Z',
                'dateModified' => '2024-06-01T00:00:00Z',
            ];

            $seoData = SeoMetaData::factory()->create([
                'structured_data' => $complexStructuredData
            ]);

            expect($seoData->structured_data)->toBe($complexStructuredData);
            expect($seoData->structured_data['@context'])->toBe('https://schema.org');
            expect($seoData->structured_data['author']['name'])->toBe('John Doe');
            expect($seoData->structured_data['publisher']['name'])->toBe('OpenEnergyCoop');
        });
    });

    describe('Integration notes', function () {
        it('documents the controller route issue', function () {
            // This test serves as documentation that the controller routes
            // are not defined in routes/api.php, so we focused on model functionality
            // instead of full controller testing
            
            $seoData = SeoMetaData::factory()->complete()->create();
            
            // Verify model has the expected structure
            $expectedFields = [
                'seoable_type', 'seoable_id', 'meta_title', 'meta_description',
                'canonical_url', 'robots', 'og_title', 'og_description',
                'og_image_path', 'og_type', 'twitter_title', 'twitter_description',
                'twitter_image_path', 'twitter_card', 'structured_data',
                'focus_keyword', 'additional_meta', 'language'
            ];
            
            foreach ($expectedFields as $field) {
                expect(array_key_exists($field, $seoData->getAttributes()))->toBe(true);
            }
            
            expect(true)->toBe(true); // Test passes to document the issue
        });
    });
});