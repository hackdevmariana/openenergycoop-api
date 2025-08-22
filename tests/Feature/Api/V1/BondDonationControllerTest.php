<?php

namespace Tests\Feature\Api\V1;

use App\Models\BondDonation;
use App\Models\EnergyBond;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BondDonationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Organization $organization;
    protected EnergyBond $energyBond;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->energyBond = EnergyBond::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_bond_donations()
    {
        BondDonation::factory()->count(5)->create([
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'donor_name',
                            'donor_email',
                            'amount',
                            'currency',
                            'donation_type',
                            'status',
                            'donation_date',
                            'created_at',
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'per_page',
                    ]
                ]);

        $this->assertCount(5, $response->json('data'));
    }

    /** @test */
    public function it_can_filter_bond_donations_by_donation_type()
    {
        BondDonation::factory()->create([
            'donation_type' => 'one_time',
            'energy_bond_id' => $this->energyBond->id,
        ]);
        
        BondDonation::factory()->create([
            'donation_type' => 'recurring',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations?donation_type=one_time');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('one_time', $response->json('data.0.donation_type'));
    }

    /** @test */
    public function it_can_filter_bond_donations_by_status()
    {
        BondDonation::factory()->create([
            'status' => 'pending',
            'energy_bond_id' => $this->energyBond->id,
        ]);
        
        BondDonation::factory()->create([
            'status' => 'confirmed',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations?status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_search_bond_donations()
    {
        BondDonation::factory()->create([
            'donor_name' => 'John Doe Alpha',
            'energy_bond_id' => $this->energyBond->id,
        ]);
        
        BondDonation::factory()->create([
            'donor_name' => 'Jane Smith Beta',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations?search=john');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('John', $response->json('data.0.donor_name'));
    }

    /** @test */
    public function it_can_create_bond_donation()
    {
        $donationData = [
            'donor_name' => 'Test Donor',
            'donor_email' => 'test@example.com',
            'energy_bond_id' => $this->energyBond->id,
            'donation_type' => 'one_time',
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_method' => 'credit_card',
            'payment_status' => 'pending',
            'status' => 'pending',
            'is_anonymous' => false,
            'is_public' => true,
            'message' => 'Test donation message',
        ];

        $response = $this->postJson('/api/v1/bond-donations', $donationData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'donor_name',
                        'donor_email',
                        'amount',
                        'donation_type',
                        'status',
                    ]
                ]);

        $this->assertDatabaseHas('bond_donations', [
            'donor_name' => 'Test Donor',
            'donor_email' => 'test@example.com',
            'amount' => 100.00,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_donation()
    {
        $response = $this->postJson('/api/v1/bond-donations', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'donor_name',
                    'donor_email',
                    'energy_bond_id',
                    'donation_type',
                    'amount',
                    'currency',
                    'payment_method',
                    'payment_status',
                    'status',
                ]);
    }

    /** @test */
    public function it_validates_donation_type_enum_when_creating()
    {
        $donationData = [
            'donor_name' => 'Test Donor',
            'donor_email' => 'test@example.com',
            'energy_bond_id' => $this->energyBond->id,
            'donation_type' => 'invalid_type',
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_method' => 'credit_card',
            'payment_status' => 'pending',
            'status' => 'pending',
        ];

        $response = $this->postJson('/api/v1/bond-donations', $donationData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['donation_type']);
    }

    /** @test */
    public function it_validates_amount_is_positive()
    {
        $donationData = [
            'donor_name' => 'Test Donor',
            'donor_email' => 'test@example.com',
            'energy_bond_id' => $this->energyBond->id,
            'donation_type' => 'one_time',
            'amount' => -50.00,
            'currency' => 'USD',
            'payment_method' => 'credit_card',
            'payment_status' => 'pending',
            'status' => 'pending',
        ];

        $response = $this->postJson('/api/v1/bond-donations', $donationData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function it_can_show_bond_donation()
    {
        $donation = BondDonation::factory()->create([
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson("/api/v1/bond-donations/{$donation->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'donor_name',
                        'donor_email',
                        'amount',
                        'donation_type',
                        'status',
                        'created_at',
                        'links',
                    ]
                ]);

        $this->assertEquals($donation->id, $response->json('data.id'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_donation()
    {
        $response = $this->getJson('/api/v1/bond-donations/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_bond_donation()
    {
        $donation = BondDonation::factory()->create([
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $updateData = [
            'donor_name' => 'Updated Donor Name',
            'amount' => 200.00,
        ];

        $response = $this->putJson("/api/v1/bond-donations/{$donation->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'donor_name',
                        'amount',
                    ]
                ]);

        $this->assertDatabaseHas('bond_donations', [
            'id' => $donation->id,
            'donor_name' => 'Updated Donor Name',
            'amount' => 200.00,
        ]);
    }

    /** @test */
    public function it_can_delete_bond_donation()
    {
        $donation = BondDonation::factory()->create([
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->deleteJson("/api/v1/bond-donations/{$donation->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Bond donation deleted successfully'
                ]);

        $this->assertDatabaseMissing('bond_donations', [
            'id' => $donation->id,
        ]);
    }

    /** @test */
    public function it_can_get_public_bond_donations()
    {
        BondDonation::factory()->create([
            'is_public' => true,
            'status' => 'confirmed',
            'energy_bond_id' => $this->energyBond->id,
        ]);
        
        BondDonation::factory()->create([
            'is_public' => false,
            'status' => 'confirmed',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations/public');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_public'));
    }

    /** @test */
    public function it_can_get_recent_bond_donations()
    {
        BondDonation::factory()->count(3)->create([
            'is_public' => true,
            'status' => 'confirmed',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations/recent');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_can_get_top_donors()
    {
        // Create multiple donations from the same donor
        $donorName = 'Top Donor';
        BondDonation::factory()->count(3)->create([
            'donor_name' => $donorName,
            'status' => 'confirmed',
            'amount' => 100.00,
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations/top-donors');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'period',
                    'total_donors'
                ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($donorName, $response->json('data.0.donor_name'));
        $this->assertEquals(300.00, $response->json('data.0.total_donated'));
    }

    /** @test */
    public function it_can_confirm_bond_donation()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'pending',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/confirm");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Bond donation confirmed successfully'
                ]);

        $this->assertDatabaseHas('bond_donations', [
            'id' => $donation->id,
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function it_cannot_confirm_non_pending_donation()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'confirmed',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/confirm");

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_reject_bond_donation()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'pending',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/reject", [
            'rejection_reason' => 'Invalid payment information'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Bond donation rejected successfully'
                ]);

        $this->assertDatabaseHas('bond_donations', [
            'id' => $donation->id,
            'status' => 'rejected',
            'rejection_reason' => 'Invalid payment information',
        ]);
    }

    /** @test */
    public function it_validates_rejection_reason()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'pending',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/reject", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['rejection_reason']);
    }

    /** @test */
    public function it_can_process_bond_donation()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'confirmed',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/process");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Bond donation processed successfully'
                ]);

        $this->assertDatabaseHas('bond_donations', [
            'id' => $donation->id,
            'status' => 'processed',
        ]);
    }

    /** @test */
    public function it_cannot_process_non_confirmed_donation()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'pending',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/process");

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_refund_bond_donation()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'confirmed',
            'amount' => 100.00,
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/refund", [
            'refund_reason' => 'Customer request',
            'refund_amount' => 50.00,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Bond donation refunded successfully'
                ]);

        $this->assertDatabaseHas('bond_donations', [
            'id' => $donation->id,
            'status' => 'refunded',
            'refund_reason' => 'Customer request',
            'refund_amount' => 50.00,
        ]);
    }

    /** @test */
    public function it_validates_refund_amount()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'confirmed',
            'amount' => 100.00,
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/refund", [
            'refund_reason' => 'Customer request',
            'refund_amount' => 150.00, // More than original amount
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['refund_amount']);
    }

    /** @test */
    public function it_can_make_donation_public()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'confirmed',
            'is_public' => false,
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/make-public");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Bond donation made public successfully'
                ]);

        $this->assertDatabaseHas('bond_donations', [
            'id' => $donation->id,
            'is_public' => true,
        ]);
    }

    /** @test */
    public function it_cannot_make_non_confirmed_donation_public()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'pending',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/make-public");

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_make_donation_private()
    {
        $donation = BondDonation::factory()->create([
            'is_public' => true,
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/make-private");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Bond donation made private successfully'
                ]);

        $this->assertDatabaseHas('bond_donations', [
            'id' => $donation->id,
            'is_public' => false,
        ]);
    }

    /** @test */
    public function it_can_send_thank_you_message()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'confirmed',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/send-thank-you", [
            'thank_you_message' => 'Thank you for your generous donation!',
            'send_email' => true,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Thank you message sent successfully'
                ]);

        $this->assertDatabaseHas('bond_donations', [
            'id' => $donation->id,
            'thank_you_message' => 'Thank you for your generous donation!',
            'thank_you_email_sent' => true,
        ]);
    }

    /** @test */
    public function it_cannot_send_thank_you_to_non_confirmed_donation()
    {
        $donation = BondDonation::factory()->create([
            'status' => 'pending',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/send-thank-you", [
            'thank_you_message' => 'Thank you!',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_duplicate_bond_donation()
    {
        $donation = BondDonation::factory()->create([
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->postJson("/api/v1/bond-donations/{$donation->id}/duplicate");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Bond donation duplicated successfully'
                ]);

        $this->assertDatabaseHas('bond_donations', [
            'donor_name' => $donation->donor_name,
            'donor_email' => $donation->donor_email,
            'amount' => $donation->amount,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_can_get_bond_donation_statistics()
    {
        BondDonation::factory()->count(3)->create([
            'status' => 'confirmed',
            'energy_bond_id' => $this->energyBond->id,
        ]);
        
        BondDonation::factory()->count(2)->create([
            'status' => 'pending',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_donations',
                        'total_amount',
                        'confirmed_donations',
                        'pending_donations',
                        'donations_by_type',
                        'donations_by_status',
                        'monthly_donations',
                        'top_donors',
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.total_donations'));
        $this->assertEquals(3, $response->json('data.confirmed_donations'));
        $this->assertEquals(2, $response->json('data.pending_donations'));
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        // Clear authentication
        $this->withoutMiddleware();

        $response = $this->getJson('/api/v1/bond-donations');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_by_amount_range()
    {
        BondDonation::factory()->create([
            'amount' => 50.00,
            'energy_bond_id' => $this->energyBond->id,
        ]);
        
        BondDonation::factory()->create([
            'amount' => 200.00,
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations?amount_min=100&amount_max=300');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(200.00, $response->json('data.0.amount'));
    }

    /** @test */
    public function it_can_sort_bond_donations()
    {
        BondDonation::factory()->create([
            'donor_name' => 'Alpha Donor',
            'energy_bond_id' => $this->energyBond->id,
        ]);
        
        BondDonation::factory()->create([
            'donor_name' => 'Beta Donor',
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations?sort_by=donor_name&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('Alpha Donor', $response->json('data.0.donor_name'));
        $this->assertEquals('Beta Donor', $response->json('data.1.donor_name'));
    }

    /** @test */
    public function it_can_paginate_results()
    {
        BondDonation::factory()->count(25)->create([
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /** @test */
    public function it_can_filter_by_anonymous_donations()
    {
        BondDonation::factory()->create([
            'is_anonymous' => true,
            'energy_bond_id' => $this->energyBond->id,
        ]);
        
        BondDonation::factory()->create([
            'is_anonymous' => false,
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations?is_anonymous=true');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_anonymous'));
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $oldDonation = BondDonation::factory()->create([
            'donation_date' => now()->subDays(10),
            'energy_bond_id' => $this->energyBond->id,
        ]);
        
        $newDonation = BondDonation::factory()->create([
            'donation_date' => now()->addDays(5),
            'energy_bond_id' => $this->energyBond->id,
        ]);

        $response = $this->getJson('/api/v1/bond-donations?donation_date_from=' . now()->toDateString());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($newDonation->id, $response->json('data.0.id'));
    }
}
