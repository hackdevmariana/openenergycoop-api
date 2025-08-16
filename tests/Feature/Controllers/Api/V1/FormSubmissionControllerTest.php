<?php

use App\Models\FormSubmission;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    FormSubmission::query()->delete();
    
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
});

describe('FormSubmissionController', function () {
    describe('Public endpoints', function () {
        it('can create a new form submission publicly', function () {
            $formData = [
                'form_name' => 'contact',
                'fields' => [
                    'name' => 'Juan Pérez',
                    'email' => 'juan@example.com',
                    'message' => 'Hola, necesito información sobre sus servicios.'
                ],
                'source_url' => 'https://example.com/contact',
                'organization_id' => $this->organization->id,
            ];

            $response = $this->postJson('/api/v1/form-submissions', $formData);

            $response->assertStatus(201)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'form_name',
                            'fields',
                            'status',
                            'source_url',
                            'created_at'
                        ],
                        'message'
                    ]);

            $this->assertDatabaseHas('form_submissions', [
                'form_name' => 'contact',
                'status' => 'pending',
                'organization_id' => $this->organization->id,
            ]);
        });

        it('validates required fields when creating submission', function () {
            $response = $this->postJson('/api/v1/form-submissions', []);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['form_name', 'fields']);
        });

        it('validates form name is allowed', function () {
            $response = $this->postJson('/api/v1/form-submissions', [
                'form_name' => 'invalid_form_type',
                'fields' => ['name' => 'Test'],
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['form_name']);
        });

        it('validates contact form has required fields', function () {
            $response = $this->postJson('/api/v1/form-submissions', [
                'form_name' => 'contact',
                'fields' => ['random_field' => 'test'],
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['fields']);
        });

        it('validates newsletter form has email', function () {
            $response = $this->postJson('/api/v1/form-submissions', [
                'form_name' => 'newsletter',
                'fields' => ['name' => 'Test User'],
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['fields']);
        });

        it('detects spam patterns', function () {
            $response = $this->postJson('/api/v1/form-submissions', [
                'form_name' => 'contact',
                'fields' => [
                    'name' => 'Spam User',
                    'email' => 'spam@test.com',
                    'message' => 'Buy cheap viagra now! Click here: http://spam.com and http://fake.com'
                ],
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['fields']);
        });

        it('enforces rate limiting by IP in production', function () {
            // Override environment for this test only
            app()->detectEnvironment(function () {
                return 'production';
            });
            
            $formData = [
                'form_name' => 'contact',
                'fields' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'message' => 'Test message'
                ],
            ];

            // Create 5 submissions (at the limit)
            for ($i = 1; $i <= 5; $i++) {
                $response = $this->postJson('/api/v1/form-submissions', array_merge($formData, [
                    'fields' => array_merge($formData['fields'], ['email' => "test{$i}@example.com"])
                ]));
                $response->assertStatus(201);
            }

            // 6th submission should be rate limited
            $response = $this->postJson('/api/v1/form-submissions', array_merge($formData, [
                'fields' => array_merge($formData['fields'], ['email' => 'test6@example.com'])
            ]));
            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['form_name']);
                    
            // Reset environment
            app()->detectEnvironment(function () {
                return 'testing';
            });
        });

        it('captures client information automatically', function () {
            $formData = [
                'form_name' => 'contact',
                'fields' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'message' => 'Test message'
                ],
            ];

            $response = $this->postJson('/api/v1/form-submissions', $formData, [
                'User-Agent' => 'Test Browser 1.0',
                'Referer' => 'https://referring-site.com/page'
            ]);

            $response->assertStatus(201);

            $submission = FormSubmission::first();
            expect($submission->ip_address)->not->toBeNull();
            expect($submission->user_agent)->toBe('Test Browser 1.0');
            expect($submission->referrer)->toBe('https://referring-site.com/page');
        });

        it('can create different types of forms', function () {
            $formTypes = ['contact', 'newsletter', 'survey', 'feedback', 'support'];

            foreach ($formTypes as $formType) {
                $fields = match ($formType) {
                    'contact' => ['name' => 'Test', 'email' => 'test@example.com', 'message' => 'Test message'],
                    'newsletter' => ['email' => 'test@example.com'],
                    'survey' => ['rating' => 5, 'comments' => 'Great service'],
                    'feedback' => ['feedback' => 'Good experience', 'rating' => 4],
                    'support' => ['name' => 'Test', 'email' => 'test@example.com', 'issue' => 'Need help'],
                };

                $response = $this->postJson('/api/v1/form-submissions', [
                    'form_name' => $formType,
                    'fields' => $fields,
                ]);

                $response->assertStatus(201);
                $this->assertDatabaseHas('form_submissions', ['form_name' => $formType]);
            }
        });
    });

    describe('Authenticated endpoints', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->user);
        });

        it('can list form submissions with pagination', function () {
            FormSubmission::factory()->count(25)->create();

            $response = $this->getJson('/api/v1/form-submissions');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'form_name',
                                'fields',
                                'status',
                                'created_at'
                            ]
                        ],
                        'links',
                        'meta'
                    ]);
        });

        it('can filter submissions by status', function () {
            FormSubmission::factory()->pending()->count(3)->create();
            FormSubmission::factory()->processed()->count(2)->create();
            FormSubmission::factory()->spam()->count(1)->create();

            $response = $this->getJson('/api/v1/form-submissions?status=pending');
            
            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(3);
        });

        it('can filter submissions by form name', function () {
            FormSubmission::factory()->contact()->count(2)->create();
            FormSubmission::factory()->newsletter()->count(3)->create();

            $response = $this->getJson('/api/v1/form-submissions?form_name=contact');
            
            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(2);
        });

        it('can filter unprocessed submissions only', function () {
            FormSubmission::factory()->pending()->count(3)->create();
            FormSubmission::factory()->processed()->count(2)->create();

            $response = $this->getJson('/api/v1/form-submissions?unprocessed_only=true');
            
            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(3);
        });

        it('can filter submissions by processed by user', function () {
            $processorUser = User::factory()->create();
            FormSubmission::factory()->processed()->count(2)->create(['processed_by_user_id' => $processorUser->id]);
            FormSubmission::factory()->processed()->count(1)->create();

            $response = $this->getJson("/api/v1/form-submissions?processed_by={$processorUser->id}");
            
            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(2);
        });

        it('can filter recent submissions', function () {
            FormSubmission::factory()->recent()->count(2)->create();
            FormSubmission::factory()->old()->count(3)->create();

            $response = $this->getJson('/api/v1/form-submissions?recent_days=7');
            
            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(2);
        });

        it('can filter submissions by IP address', function () {
            $targetIp = '192.168.1.100';
            FormSubmission::factory()->fromIp($targetIp)->count(2)->create();
            FormSubmission::factory()->count(3)->create();

            $response = $this->getJson("/api/v1/form-submissions?ip_address={$targetIp}");
            
            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(2);
        });

        it('can search in form fields', function () {
            FormSubmission::factory()->create([
                'fields' => ['name' => 'Juan Pérez', 'email' => 'juan@example.com', 'message' => 'Hola mundo']
            ]);
            FormSubmission::factory()->create([
                'fields' => ['name' => 'María García', 'email' => 'maria@example.com', 'message' => 'Buenos días']
            ]);

            $response = $this->getJson('/api/v1/form-submissions?search=Juan');
            
            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(1);
        });

        it('can show a specific form submission', function () {
            $submission = FormSubmission::factory()->create();

            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'form_name',
                            'fields',
                            'status',
                            'status_label',
                            'form_type_label',
                            'name',
                            'email',
                            'message',
                            'is_pending',
                            'is_processed',
                            'days_since_submission',
                            'has_required_fields',
                            'is_potential_spam',
                        ]
                    ]);
        });

        it('can update form submission status', function () {
            $submission = FormSubmission::factory()->pending()->create();

            $response = $this->putJson("/api/v1/form-submissions/{$submission->id}", [
                'status' => 'processed',
                'processing_notes' => 'Handled by customer service'
            ]);

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'status' => 'processed',
                            'processing_notes' => 'Handled by customer service'
                        ]
                    ]);

            $submission->refresh();
            expect($submission->status)->toBe('processed');
            expect($submission->processed_at)->not->toBeNull();
            expect($submission->processed_by_user_id)->toBe($this->user->id);
        });

        it('can delete a form submission', function () {
            $submission = FormSubmission::factory()->create();

            $response = $this->deleteJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200)
                    ->assertJson(['message' => 'Envío eliminado exitosamente']);

            $this->assertDatabaseMissing('form_submissions', ['id' => $submission->id]);
        });

        it('can mark submission as processed', function () {
            $submission = FormSubmission::factory()->pending()->create();

            $response = $this->postJson("/api/v1/form-submissions/{$submission->id}/mark-as-processed", [
                'processing_notes' => 'Resolved customer inquiry'
            ]);

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => ['status' => 'processed'],
                        'message' => 'Envío marcado como procesado'
                    ]);

            $submission->refresh();
            expect($submission->status)->toBe('processed');
            expect($submission->processing_notes)->toBe('Resolved customer inquiry');
        });

        it('can mark submission as spam', function () {
            $submission = FormSubmission::factory()->pending()->create();

            $response = $this->postJson("/api/v1/form-submissions/{$submission->id}/mark-as-spam");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => ['status' => 'spam'],
                        'message' => 'Envío marcado como spam'
                    ]);

            $submission->refresh();
            expect($submission->status)->toBe('spam');
        });

        it('can archive submission', function () {
            $submission = FormSubmission::factory()->processed()->create();

            $response = $this->postJson("/api/v1/form-submissions/{$submission->id}/archive");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => ['status' => 'archived'],
                        'message' => 'Envío archivado exitosamente'
                    ]);

            $submission->refresh();
            expect($submission->status)->toBe('archived');
        });

        it('can reopen submission', function () {
            $submission = FormSubmission::factory()->processed()->create();

            $response = $this->postJson("/api/v1/form-submissions/{$submission->id}/reopen");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => ['status' => 'pending'],
                        'message' => 'Envío reabierto exitosamente'
                    ]);

            $submission->refresh();
            expect($submission->status)->toBe('pending');
            expect($submission->processed_at)->toBeNull();
            expect($submission->processed_by_user_id)->toBeNull();
        });

        it('can get form submission statistics', function () {
            // Clean slate for accurate counts
            FormSubmission::query()->delete();

            FormSubmission::factory()->pending()->count(3)->create();
            FormSubmission::factory()->processed()->count(2)->create();
            FormSubmission::factory()->archived()->count(1)->create();
            FormSubmission::factory()->spam()->count(1)->create();

            $response = $this->getJson('/api/v1/form-submissions/stats');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'stats' => [
                            'total_submissions',
                            'pending',
                            'processed',
                            'archived',
                            'spam',
                            'this_month',
                            'this_week',
                            'today',
                            'by_form_type',
                            'processing_time',
                            'top_sources'
                        ],
                        'generated_at'
                    ]);

            $stats = $response->json('stats');
            expect($stats['total_submissions'])->toBe(7);
            expect($stats['pending'])->toBe(3);
            expect($stats['processed'])->toBe(2);
            expect($stats['archived'])->toBe(1);
            expect($stats['spam'])->toBe(1);
        });

        it('can filter stats by organization', function () {
            $otherOrg = Organization::factory()->create();
            
            FormSubmission::factory()->pending()->count(2)->create(['organization_id' => $this->organization->id]);
            FormSubmission::factory()->pending()->count(3)->create(['organization_id' => $otherOrg->id]);

            $response = $this->getJson("/api/v1/form-submissions/stats?organization_id={$this->organization->id}");

            $response->assertStatus(200);
            $stats = $response->json('stats');
            expect($stats['total_submissions'])->toBe(2);
        });
    });

    describe('Business logic and computed fields', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->user);
        });

        it('includes computed properties in API response', function () {
            $submission = FormSubmission::factory()->contact()->pending()->create([
                'fields' => [
                    'name' => 'Juan Pérez',
                    'email' => 'juan@example.com',
                    'phone' => '+1234567890',
                    'message' => 'Test message'
                ]
            ]);

            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200);
            $data = $response->json('data');

            expect($data['name'])->toBe('Juan Pérez');
            expect($data['email'])->toBe('juan@example.com');
            expect($data['phone'])->toBe('+1234567890');
            expect($data['message'])->toBe('Test message');
            expect($data['is_pending'])->toBe(true);
            expect($data['is_processed'])->toBe(false);
            expect($data['has_required_fields'])->toBe(true);
            expect($data['is_potential_spam'])->toBe(false);
            expect($data['field_count'])->toBe(4);
        });

        it('detects browser from user agent', function () {
            $submission = FormSubmission::factory()->create([
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]);

            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200);
            expect($response->json('data.browser'))->toBe('Chrome');
        });

        it('detects mobile devices', function () {
            $submission = FormSubmission::factory()->mobile()->create();

            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200);
            expect($response->json('data.is_mobile'))->toBe(true);
        });

        it('calculates processing time for processed submissions', function () {
            $processedAt = now();
            $createdAt = $processedAt->copy()->subHours(5);
            
            $submission = FormSubmission::factory()->processed()->create([
                'created_at' => $createdAt,
                'processed_at' => $processedAt,
            ]);

            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200);
            expect($response->json('data.processing_time_hours'))->toBe(5);
        });

        it('extracts domain from source URL', function () {
            $submission = FormSubmission::factory()->create([
                'source_url' => 'https://example.com/contact-page?utm_source=google'
            ]);

            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200);
            expect($response->json('data.source_domain'))->toBe('example.com');
        });

        it('identifies potential spam submissions', function () {
            $submission = FormSubmission::factory()->spam()->create();

            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200);
            expect($response->json('data.is_potential_spam'))->toBe(true);
        });

        it('handles submissions with alternative field names', function () {
            $submission = FormSubmission::factory()->create([
                'fields' => [
                    'full_name' => 'María García',
                    'email_address' => 'maria@example.com',
                    'mensaje' => 'Hola en español',
                    'asunto' => 'Consulta general'
                ]
            ]);

            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data['name'])->toBe('María García');
            expect($data['email'])->toBe('maria@example.com');
            expect($data['message'])->toBe('Hola en español');
            expect($data['subject'])->toBe('Consulta general');
        });
    });

    describe('Validation and edge cases', function () {
        it('validates status transitions when updating', function () {
            Sanctum::actingAs($this->user);
            $submission = FormSubmission::factory()->spam()->create();

            // Try to change spam back to pending (should be prevented)
            $response = $this->putJson("/api/v1/form-submissions/{$submission->id}", [
                'status' => 'pending'
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['status']);
        });

        it('requires processing notes when marking as processed', function () {
            Sanctum::actingAs($this->user);
            $submission = FormSubmission::factory()->pending()->create();

            $response = $this->putJson("/api/v1/form-submissions/{$submission->id}", [
                'status' => 'processed'
                // Missing processing_notes
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['processing_notes']);
        });

        it('cleans form data when creating submission', function () {
            $response = $this->postJson('/api/v1/form-submissions', [
                'form_name' => '  CONTACT  ',
                'fields' => [
                    '  Name  ' => '  Juan Pérez  ',
                    'email' => 'juan@example.com',
                    'empty_field' => '',
                    'null_field' => null,
                    'message' => '  Test message  '
                ],
            ]);

            $response->assertStatus(201);
            
            $submission = FormSubmission::first();
            expect($submission->form_name)->toBe('contact');
            expect($submission->fields['name'])->toBe('Juan Pérez');
            expect($submission->fields['message'])->toBe('Test message');
            expect(array_key_exists('empty_field', $submission->fields))->toBe(false);
            expect(array_key_exists('null_field', $submission->fields))->toBe(false);
        });

        it('handles forms with minimal data', function () {
            $submission = FormSubmission::factory()->minimal()->create();

            Sanctum::actingAs($this->user);
            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data['field_count'])->toBe(1);
            expect($data['has_required_fields'])->toBe(true); // Has email
        });

        it('handles forms with extensive data', function () {
            $submission = FormSubmission::factory()->extensive()->create();

            Sanctum::actingAs($this->user);
            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data['field_count'])->toBeGreaterThan(5);
            expect($data['has_required_fields'])->toBe(true);
        });
    });

    describe('Authentication and authorization', function () {
        it('requires authentication for listing submissions', function () {
            $response = $this->getJson('/api/v1/form-submissions');

            $response->assertStatus(401);
        });

        it('requires authentication for viewing submission', function () {
            $submission = FormSubmission::factory()->create();

            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(401);
        });

        it('requires authentication for updating submission', function () {
            $submission = FormSubmission::factory()->create();

            $response = $this->putJson("/api/v1/form-submissions/{$submission->id}", [
                'status' => 'processed'
            ]);

            $response->assertStatus(401);
        });

        it('requires authentication for deleting submission', function () {
            $submission = FormSubmission::factory()->create();

            $response = $this->deleteJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(401);
        });

        it('requires authentication for getting statistics', function () {
            $response = $this->getJson('/api/v1/form-submissions/stats');

            $response->assertStatus(401);
        });

        it('hides sensitive fields from unauthenticated users', function () {
            $submission = FormSubmission::factory()->create();

            // Create the submission publicly
            $response = $this->postJson('/api/v1/form-submissions', [
                'form_name' => 'contact',
                'fields' => [
                    'name' => 'Public User',
                    'email' => 'public@example.com',
                    'message' => 'Public message'
                ]
            ]);

            $response->assertStatus(201);
            $data = $response->json('data');
            
            // IP and user agent should be hidden for public submissions
            expect(array_key_exists('ip_address', $data))->toBe(false);
            expect(array_key_exists('user_agent', $data))->toBe(false);
        });

        it('shows sensitive fields to authenticated users', function () {
            $submission = FormSubmission::factory()->create();

            Sanctum::actingAs($this->user);
            $response = $this->getJson("/api/v1/form-submissions/{$submission->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            
            // IP and user agent should be visible for authenticated users
            expect(array_key_exists('ip_address', $data))->toBe(true);
            expect(array_key_exists('user_agent', $data))->toBe(true);
        });
    });
});
