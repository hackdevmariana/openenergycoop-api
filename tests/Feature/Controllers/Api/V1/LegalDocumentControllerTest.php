<?php

use App\Models\CustomerProfile;
use App\Models\LegalDocument;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    LegalDocument::query()->delete();
    
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
    $this->customerProfile = CustomerProfile::factory()
        ->forOrganization($this->organization)
        ->create();
});

describe('LegalDocumentController', function () {
    describe('Index endpoint', function () {
        it('can list legal documents when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->create();

            $response = $this->getJson('/api/v1/legal-documents');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'customer_profile_id',
                                'organization_id',
                                'type',
                                'version',
                                'uploaded_at',
                                'verified_at',
                                'notes',
                                'expires_at'
                            ]
                        ],
                        'links',
                        'meta' => [
                            'current_page',
                            'total',
                            'per_page'
                        ]
                    ]);

            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($document->id);
        });

        it('can filter by customer profile id', function () {
            Sanctum::actingAs($this->user);
            
            $document1 = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->create();
            
            $otherCustomer = CustomerProfile::factory()
                ->forOrganization($this->organization)
                ->create();
            $document2 = LegalDocument::factory()
                ->forCustomerProfile($otherCustomer)
                ->create();

            $response = $this->getJson("/api/v1/legal-documents?customer_profile_id={$this->customerProfile->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($document1->id);
        });

        it('can filter by document type', function () {
            Sanctum::actingAs($this->user);
            
            $dniDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->create();
            
            $contractDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->contract()
                ->create();

            $response = $this->getJson('/api/v1/legal-documents?document_type=dni');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($dniDocument->id);
            expect($data[0]['type'])->toBe('dni');
        });

        it('can filter by verifier user id', function () {
            Sanctum::actingAs($this->user);
            
            $verifier = User::factory()->create();
            $verifiedDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->verifiedBy($verifier)
                ->create();
            
            $unverifiedDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->unverified()
                ->create();

            $response = $this->getJson("/api/v1/legal-documents?verifier_user_id={$verifier->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($verifiedDocument->id);
        });

        it('supports pagination', function () {
            Sanctum::actingAs($this->user);
            
            LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->count(25)
                ->create();

            $response = $this->getJson('/api/v1/legal-documents?per_page=10');

            $response->assertStatus(200);
            $data = $response->json('data');
            $meta = $response->json('meta');
            
            expect($data)->toHaveCount(10);
            expect($meta['total'])->toBe(25);
            expect($meta['per_page'])->toBe(10);
            expect($meta['last_page'])->toBe(3);
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/legal-documents');

            $response->assertStatus(401);
        });
    });

    describe('Show endpoint', function () {
        it('can show a legal document when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->verified()
                ->create();

            $response = $this->getJson("/api/v1/legal-documents/{$document->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $document->id,
                            'customer_profile_id' => $document->customer_profile_id,
                            'type' => $document->type,
                            'version' => $document->version,
                        ]
                    ]);
        });

        it('returns 404 for non-existent document', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/legal-documents/999');

            $response->assertStatus(404)
                    ->assertJson([
                        'message' => 'Legal document not found.',
                        'error' => 'not_found'
                    ]);
        });

        it('requires authentication', function () {
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->create();

            $response = $this->getJson("/api/v1/legal-documents/{$document->id}");

            $response->assertStatus(401);
        });
    });

    describe('Verify endpoint', function () {
        it('can verify a legal document when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->unverified()
                ->create();

            $verificationData = [
                'verification_status' => 'verified',
                'verification_notes' => 'Document is valid and accepted.',
            ];

            $response = $this->postJson("/api/v1/legal-documents/{$document->id}/verify", $verificationData);

            $response->assertStatus(200)
                    ->assertJson([
                        'message' => 'Legal document verified successfully',
                        'data' => [
                            'id' => $document->id,
                        ]
                    ]);

            $document->refresh();
            expect($document->verified_at)->not->toBeNull();
            expect($document->verifier_user_id)->toBe($this->user->id);
        });

        it('can reject a legal document', function () {
            Sanctum::actingAs($this->user);
            
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->unverified()
                ->create();

            $verificationData = [
                'verification_status' => 'rejected',
                'verification_notes' => 'Document quality is insufficient.',
            ];

            $response = $this->postJson("/api/v1/legal-documents/{$document->id}/verify", $verificationData);

            $response->assertStatus(200);

            $document->refresh();
            expect($document->verified_at)->not->toBeNull();
            expect($document->verifier_user_id)->toBe($this->user->id);
        });

        it('validates verification status', function () {
            Sanctum::actingAs($this->user);
            
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->create();

            $response = $this->postJson("/api/v1/legal-documents/{$document->id}/verify", [
                'verification_status' => 'invalid_status',
            ]);

            $response->assertStatus(400)
                    ->assertJsonValidationErrors(['verification_status']);
        });

        it('requires verification status', function () {
            Sanctum::actingAs($this->user);
            
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->create();

            $response = $this->postJson("/api/v1/legal-documents/{$document->id}/verify", []);

            $response->assertStatus(400)
                    ->assertJsonValidationErrors(['verification_status']);
        });

        it('requires authentication', function () {
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->create();

            $response = $this->postJson("/api/v1/legal-documents/{$document->id}/verify", [
                'verification_status' => 'verified',
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Model business logic', function () {
        it('can mark document as verified', function () {
            $verifier = User::factory()->create();
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->unverified()
                ->create();

            $document->markAsVerified($verifier, 'Verification complete');

            $document->refresh();
            expect($document->verified_at)->not->toBeNull();
            expect($document->verifier_user_id)->toBe($verifier->id);
            expect($document->notes)->toBe('Verification complete');
        });

        it('can check if document is verified', function () {
            $unverifiedDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->unverified()
                ->create();

            $verifiedDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->verified()
                ->create();

            expect($unverifiedDocument->verified_at)->toBeNull();
            expect($verifiedDocument->verified_at)->not->toBeNull();
        });

        it('can check if document is expired', function () {
            $expiredDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->expired()
                ->create();

            $validDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->expiringSoon(90)
                ->create();

            expect($expiredDocument->isExpired())->toBe(true);
            expect($validDocument->isExpired())->toBe(false);
        });

        it('can check if document is expiring soon', function () {
            $expiringSoonDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->expiringSoon(15)
                ->create();

            $notExpiringSoonDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->expiringSoon(90)
                ->create();

            expect($expiringSoonDocument->isExpiringSoon(30))->toBe(true);
            expect($notExpiringSoonDocument->isExpiringSoon(30))->toBe(false);
        });

        it('can get all versions of a document', function () {
            $document1 = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->version('1.0')
                ->create();

            $document2 = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->version('2.0')
                ->create();

            $document3 = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->contract() // Different type
                ->version('1.0')
                ->create();

            $allVersions = $document1->getAllVersions();
            
            expect($allVersions)->toHaveCount(2);
            expect($allVersions->pluck('id')->sort()->values()->all())
                ->toBe([$document1->id, $document2->id]);
        });

        it('can get latest version of a document', function () {
            $document1 = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->version('1.0')
                ->create();

            $latestDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->version('3.0')
                ->create();

            $document2 = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->version('2.0')
                ->create();

            $latest = $document1->getLatestVersion();
            
            expect($latest->id)->toBe($latestDocument->id);
            expect($latest->version)->toBe('3.0');
        });

        it('can check if document is latest version', function () {
            $oldDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->version('1.0')
                ->create();

            $latestDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->version('2.0')
                ->create();

            expect($oldDocument->isLatestVersion())->toBe(false);
            expect($latestDocument->isLatestVersion())->toBe(true);
        });

        it('can get type label', function () {
            $dniDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->create();

            // This will depend on the DOCUMENT_TYPES constant
            $label = $dniDocument->getTypeLabel();
            expect($label)->toBeString();
        });
    });

    describe('Scopes', function () {
        it('can filter by type using scope', function () {
            $dniDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->create();

            $contractDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->contract()
                ->create();

            $dniDocuments = LegalDocument::byType('dni')->get();
            
            expect($dniDocuments)->toHaveCount(1);
            expect($dniDocuments->first()->id)->toBe($dniDocument->id);
        });

        it('can filter verified documents using scope', function () {
            $verifiedDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->verified()
                ->create();

            $unverifiedDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->unverified()
                ->create();

            $verifiedDocuments = LegalDocument::verified()->get();
            
            expect($verifiedDocuments)->toHaveCount(1);
            expect($verifiedDocuments->first()->id)->toBe($verifiedDocument->id);
        });

        it('can filter pending verification documents using scope', function () {
            $verifiedDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->verified()
                ->create();

            $unverifiedDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->unverified()
                ->create();

            $pendingDocuments = LegalDocument::pendingVerification()->get();
            
            expect($pendingDocuments)->toHaveCount(1);
            expect($pendingDocuments->first()->id)->toBe($unverifiedDocument->id);
        });

        it('can filter expired documents using scope', function () {
            $expiredDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->expired()
                ->create();

            $validDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->expiringSoon(90)
                ->create();

            $expiredDocuments = LegalDocument::expired()->get();
            
            expect($expiredDocuments)->toHaveCount(1);
            expect($expiredDocuments->first()->id)->toBe($expiredDocument->id);
        });

        it('can filter documents expiring within days using scope', function () {
            $expiringSoonDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->expiringSoon(15)
                ->create();

            $validDocument = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->expiringSoon(90)
                ->create();

            $documentsExpiringSoon = LegalDocument::expiringWithin(30)->get();
            
            expect($documentsExpiringSoon)->toHaveCount(1);
            expect($documentsExpiringSoon->first()->id)->toBe($expiringSoonDocument->id);
        });
    });

    describe('Edge cases and validation', function () {
        it('handles documents without expiry date', function () {
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->withoutExpiry()
                ->create();

            expect($document->isExpired())->toBe(false);
            expect($document->isExpiringSoon())->toBe(false);
            expect($document->expires_at)->toBeNull();
        });

        it('handles documents without notes', function () {
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->withoutNotes()
                ->create();

            expect($document->notes)->toBeNull();
        });

        it('handles documents with all fields populated', function () {
            $document = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->complete()
                ->create();

            expect($document->verified_at)->not->toBeNull();
            expect($document->verifier_user_id)->not->toBeNull();
            expect($document->notes)->not->toBeNull();
            expect($document->expires_at)->not->toBeNull();
        });

        it('handles version comparison correctly', function () {
            $v1 = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->version('1.0')
                ->create();

            $v2 = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->version('2.0')
                ->create();

            $v1_1 = LegalDocument::factory()
                ->forCustomerProfile($this->customerProfile)
                ->dni()
                ->version('1.1')
                ->create();

            $latest = $v1->getLatestVersion();
            expect($latest->version)->toBe('2.0');
        });
    });
});
