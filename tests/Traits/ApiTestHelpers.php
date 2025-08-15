<?php

namespace Tests\Traits;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait ApiTestHelpers
{
    /**
     * Create and authenticate a user with specific role
     */
    protected function authenticateAs(string $role = 'admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        Sanctum::actingAs($user);
        
        return $user;
    }

    /**
     * Create a user without authentication
     */
    protected function createUserWithRole(string $role = 'admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        
        return $user;
    }

    /**
     * Assert that response has correct JSON structure for a resource collection
     */
    protected function assertResourceCollectionStructure($response, array $resourceStructure): void
    {
        $response->assertJsonStructure([
            'data' => [
                '*' => $resourceStructure
            ],
            'links',
            'meta'
        ]);
    }

    /**
     * Assert that response has correct JSON structure for a single resource
     */
    protected function assertResourceStructure($response, array $resourceStructure): void
    {
        $response->assertJsonStructure([
            'data' => $resourceStructure
        ]);
    }

    /**
     * Assert that all endpoints require authentication
     */
    protected function assertEndpointsRequireAuth(array $endpoints): void
    {
        foreach ($endpoints as $method => $urls) {
            if (is_numeric($method)) {
                // If no method specified, assume GET
                $method = 'GET';
                $urls = is_array($urls) ? $urls : [$urls];
            } else {
                $urls = is_array($urls) ? $urls : [$urls];
            }
            
            foreach ($urls as $url) {
                $response = match (strtoupper($method)) {
                    'GET' => $this->getJson($url),
                    'POST' => $this->postJson($url, []),
                    'PUT' => $this->putJson($url, []),
                    'PATCH' => $this->patchJson($url, []),
                    'DELETE' => $this->deleteJson($url),
                    default => $this->getJson($url)
                };
                
                $response->assertStatus(401, "Endpoint {$method} {$url} should require authentication");
            }
        }
    }

    /**
     * Assert that response contains validation errors for specific fields
     */
    protected function assertValidationErrors($response, array $fields): void
    {
        $response->assertStatus(422);
        $response->assertJsonValidationErrors($fields);
    }

    /**
     * Assert that response is paginated and has expected count
     */
    protected function assertPaginatedResponse($response, int $expectedCount = null, int $perPage = 15): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links' => [
                'first',
                'last',
                'prev',
                'next'
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'per_page',
                'to',
                'total'
            ]
        ]);
        
        if ($expectedCount !== null) {
            $this->assertCount($expectedCount, $response->json('data'));
        }
        
        $this->assertEquals($perPage, $response->json('meta.per_page'));
    }

    /**
     * Assert that response contains success message with data
     */
    protected function assertSuccessWithData($response, string $expectedMessage = null): void
    {
        $response->assertStatus(200);
        
        if ($expectedMessage) {
            $response->assertJson(['message' => $expectedMessage]);
        }
        
        $response->assertJsonStructure(['data']);
    }

    /**
     * Assert that response contains error message
     */
    protected function assertErrorResponse($response, int $expectedStatus, string $expectedMessage = null): void
    {
        $response->assertStatus($expectedStatus);
        
        if ($expectedMessage) {
            $response->assertJson(['message' => $expectedMessage]);
        }
    }

    /**
     * Assert that collection is filtered correctly
     */
    protected function assertCollectionFiltered(array $collection, string $field, $expectedValue): void
    {
        foreach ($collection as $item) {
            $actualValue = data_get($item, $field);
            $this->assertEquals(
                $expectedValue, 
                $actualValue, 
                "Item in collection has unexpected value for filter field {$field}. Expected: {$expectedValue}, Got: {$actualValue}"
            );
        }
    }

    /**
     * Assert that collection is ordered correctly
     */
    protected function assertCollectionOrdered(array $collection, string $field, string $direction = 'desc'): void
    {
        $values = array_map(function ($item) use ($field) {
            return data_get($item, $field);
        }, $collection);
        
        $sortedValues = $values;
        if ($direction === 'desc') {
            rsort($sortedValues);
        } else {
            sort($sortedValues);
        }
        
        $this->assertEquals(
            $sortedValues, 
            $values, 
            "Collection is not properly ordered by {$field} in {$direction} order"
        );
    }

    /**
     * Create test data for pagination testing
     */
    protected function createPaginationTestData(string $modelClass, int $count = 25, array $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return $modelClass::factory()->count($count)->create($attributes);
    }

    /**
     * Test pagination with different page sizes
     */
    protected function assertPaginationWorks(string $endpoint, int $totalRecords): void
    {
        $user = $this->authenticateAs();
        
        // Test default pagination
        $response = $this->getJson($endpoint);
        $response->assertStatus(200);
        $this->assertLessThanOrEqual(15, count($response->json('data')));
        
        // Test custom page size
        $response = $this->getJson($endpoint . '?per_page=10');
        $response->assertStatus(200);
        $this->assertLessThanOrEqual(10, count($response->json('data')));
        
        // Test pagination metadata
        $response->assertJsonStructure([
            'meta' => [
                'total',
                'per_page',
                'current_page',
                'last_page'
            ]
        ]);
        
        $meta = $response->json('meta');
        $this->assertEquals($totalRecords, $meta['total']);
    }

    /**
     * Assert that forbidden response is returned for non-admin users
     */
    protected function assertForbiddenForNonAdmin(string $method, string $endpoint, array $data = []): void
    {
        $regularUser = User::factory()->create();
        // Don't assign admin role
        Sanctum::actingAs($regularUser);
        
        $response = match (strtoupper($method)) {
            'GET' => $this->getJson($endpoint),
            'POST' => $this->postJson($endpoint, $data),
            'PUT' => $this->putJson($endpoint, $data),
            'PATCH' => $this->patchJson($endpoint, $data),
            'DELETE' => $this->deleteJson($endpoint),
            default => $this->getJson($endpoint)
        };
        
        $response->assertStatus(403);
    }

    /**
     * Create test data with relationships
     */
    protected function createTestDataWithRelationships(): array
    {
        // This method should be overridden in specific test classes
        // to create the appropriate test data structure
        return [];
    }

    /**
     * Assert that response includes expected relationships
     */
    protected function assertIncludesRelationships($response, array $expectedRelationships): void
    {
        $data = $response->json('data');
        
        if (isset($data[0])) {
            // Collection response
            foreach ($expectedRelationships as $relationship) {
                $this->assertArrayHasKey($relationship, $data[0], "Missing relationship: {$relationship}");
            }
        } else {
            // Single resource response
            foreach ($expectedRelationships as $relationship) {
                $this->assertArrayHasKey($relationship, $data, "Missing relationship: {$relationship}");
            }
        }
    }

    /**
     * Test that filters work correctly
     */
    protected function assertFilterWorks(string $endpoint, string $filterParam, $filterValue, string $resultField, $expectedValue): void
    {
        $user = $this->authenticateAs();
        
        $response = $this->getJson($endpoint . "?{$filterParam}={$filterValue}");
        $response->assertStatus(200);
        
        $data = $response->json('data');
        if (!empty($data)) {
            foreach ($data as $item) {
                $actualValue = data_get($item, $resultField);
                $this->assertEquals($expectedValue, $actualValue, "Filter {$filterParam} not working correctly");
            }
        }
    }

    /**
     * Assert that search functionality works
     */
    protected function assertSearchWorks(string $endpoint, string $searchTerm, array $searchFields): void
    {
        $user = $this->authenticateAs();
        
        $response = $this->getJson($endpoint . "?search={$searchTerm}");
        $response->assertStatus(200);
        
        // This is a basic assertion - in real tests you might want to verify
        // that the search term appears in one of the expected fields
        $data = $response->json('data');
        $this->assertIsArray($data);
    }
}
