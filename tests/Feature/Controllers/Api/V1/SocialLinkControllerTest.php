<?php

use App\Models\SocialLink;
use App\Models\Organization;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // Clean up data before each test
    SocialLink::query()->delete();
});

// GET /api/v1/social-links - List Social Links
it('can list published and active social links', function () {
    $publishedLinks = SocialLink::factory()->count(3)->published()->create();
    $draftLink = SocialLink::factory()->draft()->create();
    $inactiveLink = SocialLink::factory()->inactive()->create();

    $response = $this->getJson('/api/v1/social-links');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'platform', 'url', 'icon', 'css_class', 'color',
                        'order', 'is_active', 'followers_count', 'is_draft',
                        'created_at', 'updated_at', 'is_published', 'platform_label',
                        'platform_color', 'platform_icon', 'formatted_followers_count',
                        'is_verified', 'css_class_computed'
                    ]
                ]
            ]);
});

it('orders social links by order and platform', function () {
    $link1 = SocialLink::factory()->published()->create(['order' => 3, 'platform' => 'twitter']);
    $link2 = SocialLink::factory()->published()->create(['order' => 1, 'platform' => 'facebook']);
    $link3 = SocialLink::factory()->published()->create(['order' => 2, 'platform' => 'instagram']);

    $response = $this->getJson('/api/v1/social-links');

    $response->assertStatus(200);
    $data = $response->json('data');
    
    expect($data[0]['id'])->toBe($link2->id); // order 1
    expect($data[1]['id'])->toBe($link3->id); // order 2
    expect($data[2]['id'])->toBe($link1->id); // order 3
});

it('can filter social links by platform', function () {
    SocialLink::factory()->count(2)->published()->facebook()->create();
    SocialLink::factory()->count(3)->published()->twitter()->create();

    $response = $this->getJson('/api/v1/social-links?platform=facebook');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $link) {
        expect($link['platform'])->toBe('facebook');
    }
});

it('can filter only verified social links', function () {
    SocialLink::factory()->count(2)->published()->verified()->create();
    SocialLink::factory()->count(3)->published()->noFollowers()->create();

    $response = $this->getJson('/api/v1/social-links?only_verified=1');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $link) {
        expect($link['is_verified'])->toBeTrue();
        expect($link['followers_count'])->toBeGreaterThanOrEqual(10000);
    }
});

// POST /api/v1/social-links - Store Social Link
it('can create a new social link', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $linkData = [
        'platform' => 'facebook',
        'url' => 'https://facebook.com/openenergycoop',
        'icon' => 'fab fa-facebook-f',
        'css_class' => 'social-facebook',
        'color' => '#1877F2',
        'order' => 1,
        'is_active' => true,
        'followers_count' => 1500,
        'organization_id' => $organization->id,
        'is_draft' => false,
    ];

    $response = $this->postJson('/api/v1/social-links', $linkData);

    $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'platform', 'url', 'platform_label'],
                'message'
            ]);

    $this->assertDatabaseHas('social_links', [
        'platform' => 'facebook',
        'url' => 'https://facebook.com/openenergycoop',
        'created_by_user_id' => $user->id,
    ]);
});

it('sets default values when creating social link', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $linkData = [
        'platform' => 'twitter',
        'url' => 'https://twitter.com/test',
    ];

    $response = $this->postJson('/api/v1/social-links', $linkData);

    $response->assertStatus(201);
    
    $link = SocialLink::latest()->first();
    expect($link->is_active)->toBeTrue(); // Default
    expect($link->is_draft)->toBeTrue(); // Default
    expect($link->order)->toBe(0); // Default
});

it('auto-sets platform color and css class when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $linkData = [
        'platform' => 'instagram',
        'url' => 'https://instagram.com/test',
    ];

    $response = $this->postJson('/api/v1/social-links', $linkData);

    $response->assertStatus(201);
    
    $link = SocialLink::latest()->first();
    expect($link->color)->toBe('#E4405F'); // Instagram brand color
    expect($link->css_class)->toBe('social-link-instagram'); // Auto-generated
});

it('validates required platform when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/social-links', [
        'url' => 'https://example.com',
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['platform']);
});

it('validates required url when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/social-links', [
        'platform' => 'facebook',
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
});

it('validates platform enum when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $linkData = [
        'platform' => 'invalid_platform',
        'url' => 'https://example.com',
    ];

    $response = $this->postJson('/api/v1/social-links', $linkData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['platform']);
});

it('validates url format when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $linkData = [
        'platform' => 'facebook',
        'url' => 'not-a-valid-url',
    ];

    $response = $this->postJson('/api/v1/social-links', $linkData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
});

it('validates url matches platform when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $linkData = [
        'platform' => 'facebook',
        'url' => 'https://twitter.com/test', // Wrong platform
    ];

    $response = $this->postJson('/api/v1/social-links', $linkData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
});

it('validates color format when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $linkData = [
        'platform' => 'facebook',
        'url' => 'https://facebook.com/test',
        'color' => 'invalid-color',
    ];

    $response = $this->postJson('/api/v1/social-links', $linkData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['color']);
});

it('auto-adds https protocol to url', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $linkData = [
        'platform' => 'facebook',
        'url' => 'facebook.com/test', // No protocol
    ];

    $response = $this->postJson('/api/v1/social-links', $linkData);

    $response->assertStatus(201);
    
    $link = SocialLink::latest()->first();
    expect($link->url)->toBe('https://facebook.com/test');
});

// GET /api/v1/social-links/{socialLink} - Show Social Link
it('can show a published and active social link', function () {
    $link = SocialLink::factory()->published()->create();

    $response = $this->getJson("/api/v1/social-links/{$link->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'platform', 'url', 'is_published', 'platform_label',
                    'platform_color', 'platform_icon', 'formatted_followers_count'
                ]
            ]);
});

it('returns 404 for draft social link', function () {
    $link = SocialLink::factory()->draft()->create();

    $response = $this->getJson("/api/v1/social-links/{$link->id}");

    $response->assertStatus(404)
            ->assertJson(['message' => 'Enlace social no encontrado']);
});

it('returns 404 for inactive social link', function () {
    $link = SocialLink::factory()->published()->inactive()->create();

    $response = $this->getJson("/api/v1/social-links/{$link->id}");

    $response->assertStatus(404)
            ->assertJson(['message' => 'Enlace social no encontrado']);
});

it('returns 404 for non-existent social link', function () {
    $response = $this->getJson('/api/v1/social-links/999999');

    $response->assertStatus(404);
});

// PUT /api/v1/social-links/{socialLink} - Update Social Link
it('can update a social link', function () {
    $user = User::factory()->create();
    $link = SocialLink::factory()->published()->facebook()->create([
        'followers_count' => 1000,
        'order' => 5,
    ]);
    Sanctum::actingAs($user);

    $updateData = [
        'url' => 'https://facebook.com/updated',
        'followers_count' => 2000,
        'order' => 1,
        'is_active' => false,
    ];

    $response = $this->putJson("/api/v1/social-links/{$link->id}", $updateData);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'url', 'followers_count'],
                'message'
            ]);

    $this->assertDatabaseHas('social_links', [
        'id' => $link->id,
        'url' => 'https://facebook.com/updated',
        'followers_count' => 2000,
        'order' => 1,
        'is_active' => false,
    ]);
});

it('validates url matches existing platform when updating', function () {
    $user = User::factory()->create();
    $link = SocialLink::factory()->published()->facebook()->create();
    Sanctum::actingAs($user);

    $updateData = [
        'url' => 'https://twitter.com/test', // Wrong platform
    ];

    $response = $this->putJson("/api/v1/social-links/{$link->id}", $updateData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
});

// DELETE /api/v1/social-links/{socialLink} - Delete Social Link
it('can delete a social link', function () {
    $user = User::factory()->create();
    $link = SocialLink::factory()->published()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/social-links/{$link->id}");

    $response->assertStatus(200)
            ->assertJson(['message' => 'Enlace social eliminado exitosamente']);

    $this->assertDatabaseMissing('social_links', ['id' => $link->id]);
});

// GET /api/v1/social-links/by-platform/{platform} - Get Social Link by Platform
it('can get social link by platform', function () {
    $facebookLink = SocialLink::factory()->published()->facebook()->create();
    $twitterLink = SocialLink::factory()->published()->twitter()->create();

    $response = $this->getJson('/api/v1/social-links/by-platform/facebook');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'platform', 'url']
            ]);
    
    $data = $response->json('data');
    expect($data['platform'])->toBe('facebook');
    expect($data['id'])->toBe($facebookLink->id);
});

it('returns 404 when platform not found', function () {
    SocialLink::factory()->published()->twitter()->create();

    $response = $this->getJson('/api/v1/social-links/by-platform/facebook');

    $response->assertStatus(404)
            ->assertJson(['message' => 'Enlace social no encontrado']);
});

it('only returns published and active links by platform', function () {
    SocialLink::factory()->draft()->facebook()->create();
    SocialLink::factory()->published()->inactive()->facebook()->create();

    $response = $this->getJson('/api/v1/social-links/by-platform/facebook');

    $response->assertStatus(404);
});

// GET /api/v1/social-links/popular - Get Popular Social Links
it('can get popular social links', function () {
    SocialLink::factory()->published()->create(['followers_count' => 50000]);
    SocialLink::factory()->published()->create(['followers_count' => 25000]);
    SocialLink::factory()->published()->create(['followers_count' => 10000]);
    SocialLink::factory()->published()->noFollowers()->create();

    $response = $this->getJson('/api/v1/social-links/popular');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'platform', 'followers_count']],
                'total'
            ])
            ->assertJsonCount(3, 'data'); // Only links with followers
    
    $data = $response->json('data');
    // Should be ordered by followers count descending
    expect($data[0]['followers_count'])->toBe(50000);
    expect($data[1]['followers_count'])->toBe(25000);
    expect($data[2]['followers_count'])->toBe(10000);
});

it('can limit popular social links results', function () {
    SocialLink::factory()->count(8)->published()->popular()->create();

    $response = $this->getJson('/api/v1/social-links/popular?limit=3');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
});

it('respects maximum limit for popular social links', function () {
    SocialLink::factory()->count(15)->published()->popular()->create();

    $response = $this->getJson('/api/v1/social-links/popular?limit=20'); // Requesting more than max

    $response->assertStatus(200)
            ->assertJsonCount(10, 'data'); // Should be limited to 10
});

// Authentication Tests
it('requires authentication for creating social link', function () {
    $response = $this->postJson('/api/v1/social-links', [
        'platform' => 'facebook',
        'url' => 'https://facebook.com/test',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for updating social link', function () {
    $link = SocialLink::factory()->published()->create();

    $response = $this->putJson("/api/v1/social-links/{$link->id}", [
        'url' => 'https://facebook.com/updated',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for deleting social link', function () {
    $link = SocialLink::factory()->published()->create();

    $response = $this->deleteJson("/api/v1/social-links/{$link->id}");

    $response->assertStatus(401);
});

// Model Logic Tests
it('calculates platform label correctly', function () {
    $link = SocialLink::factory()->published()->create(['platform' => 'facebook']);

    expect($link->getPlatformLabel())->toBe('Facebook');
});

it('calculates platform color correctly', function () {
    $linkWithColor = SocialLink::factory()->published()->create([
        'platform' => 'facebook',
        'color' => '#FF0000',
    ]);
    
    $linkWithoutColor = SocialLink::factory()->published()->create([
        'platform' => 'facebook',
        'color' => null,
    ]);

    expect($linkWithColor->getPlatformColor())->toBe('#FF0000'); // Custom color
    expect($linkWithoutColor->getPlatformColor())->toBe('#1877F2'); // Default Facebook color
});

it('calculates platform icon correctly', function () {
    $linkWithIcon = SocialLink::factory()->published()->create([
        'platform' => 'facebook',
        'icon' => 'custom-icon',
    ]);
    
    $linkWithoutIcon = SocialLink::factory()->published()->create([
        'platform' => 'facebook',
        'icon' => null,
    ]);

    expect($linkWithIcon->getPlatformIcon())->toBe('custom-icon'); // Custom icon
    expect($linkWithoutIcon->getPlatformIcon())->toBe('fab fa-facebook-f'); // Default Facebook icon
});

it('formats followers count correctly', function () {
    $linkMillion = SocialLink::factory()->published()->create(['followers_count' => 1500000]);
    $linkThousand = SocialLink::factory()->published()->create(['followers_count' => 2500]);
    $linkHundred = SocialLink::factory()->published()->create(['followers_count' => 500]);
    $linkZero = SocialLink::factory()->published()->create(['followers_count' => null]);

    expect($linkMillion->getFormattedFollowersCount())->toBe('1.5M');
    expect($linkThousand->getFormattedFollowersCount())->toBe('2.5K');
    expect($linkHundred->getFormattedFollowersCount())->toBe('500');
    expect($linkZero->getFormattedFollowersCount())->toBe('0');
});

it('determines verification status correctly', function () {
    $verifiedLink = SocialLink::factory()->published()->create(['followers_count' => 15000]);
    $unverifiedLink = SocialLink::factory()->published()->create(['followers_count' => 5000]);

    expect($verifiedLink->isVerified())->toBeTrue();
    expect($unverifiedLink->isVerified())->toBeFalse();
});

it('gets css class correctly', function () {
    $linkWithClass = SocialLink::factory()->published()->create([
        'platform' => 'facebook',
        'css_class' => 'custom-class',
    ]);
    
    $linkWithoutClass = SocialLink::factory()->published()->create([
        'platform' => 'facebook',
        'css_class' => null,
    ]);

    expect($linkWithClass->getCssClass())->toBe('custom-class'); // Custom class
    expect($linkWithoutClass->getCssClass())->toBe('social-link-facebook'); // Auto-generated
});

it('includes computed properties in API response', function () {
    $link = SocialLink::factory()->published()->facebook()->create([
        'followers_count' => 15000,
        'color' => null,
        'icon' => null,
    ]);

    $response = $this->getJson("/api/v1/social-links/{$link->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'is_published', 'platform_label', 'platform_color',
                    'platform_icon', 'formatted_followers_count', 'is_verified',
                    'css_class_computed'
                ]
            ]);
    
    $data = $response->json('data');
    expect($data['is_published'])->toBeTrue();
    expect($data['platform_label'])->toBe('Facebook');
    expect($data['platform_color'])->toBe('#1877F2');
    expect($data['platform_icon'])->toBe('fab fa-facebook-f');
    expect($data['formatted_followers_count'])->toBe('15.0K');
    expect($data['is_verified'])->toBeTrue();
    expect($data['css_class_computed'])->toBe('social-link-facebook');
});

it('can create social links for different platforms', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $platforms = [
        ['platform' => 'facebook', 'url' => 'https://facebook.com/test'],
        ['platform' => 'twitter', 'url' => 'https://twitter.com/test'],
        ['platform' => 'instagram', 'url' => 'https://instagram.com/test'],
        ['platform' => 'linkedin', 'url' => 'https://linkedin.com/company/test'],
        ['platform' => 'youtube', 'url' => 'https://youtube.com/@test'],
    ];

    foreach ($platforms as $platformData) {
        $response = $this->postJson('/api/v1/social-links', $platformData);
        $response->assertStatus(201);
    }

    expect(SocialLink::count())->toBe(5);
});
