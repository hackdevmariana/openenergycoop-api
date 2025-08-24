<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class SimpleEnergyChallengeRouteTest extends TestCase
{
    /** @test */
    public function it_can_access_energy_challenges_route()
    {
        $response = $this->get('/api/v1/energy-challenges');
        
        // Debería devolver 401 (no autenticado) o 200 si es público
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_active_energy_challenges_route()
    {
        $response = $this->get('/api/v1/energy-challenges/active');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_upcoming_energy_challenges_route()
    {
        $response = $this->get('/api/v1/energy-challenges/upcoming');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_user_challenge_progress_route()
    {
        $response = $this->get('/api/v1/user-challenge-progress');
        
        // Debería devolver 401 (no autenticado)
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_access_my_progress_route()
    {
        $response = $this->get('/api/v1/user-challenge-progress/my-progress');
        
        // Debería devolver 401 (no autenticado)
        $response->assertStatus(401);
    }

    /** @test */
    public function it_cannot_access_protected_routes_without_authentication()
    {
        $response = $this->post('/api/v1/energy-challenges');
        $response->assertStatus(401);

        $response = $this->put('/api/v1/energy-challenges/1');
        $response->assertStatus(401);

        $response = $this->delete('/api/v1/energy-challenges/1');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_correct_json_structure_for_public_routes()
    {
        $response = $this->get('/api/v1/energy-challenges');
        
        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/json')
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }
}
