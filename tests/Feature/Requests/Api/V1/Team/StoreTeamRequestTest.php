<?php

namespace Tests\Feature\Requests\Api\V1\Team;

use App\Http\Requests\Api\V1\Team\StoreTeamRequest;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreTeamRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        
        Sanctum::actingAs($this->user);
    }

    #[Test]
    public function it_validates_required_fields()
    {
        $request = new StoreTeamRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[Test]
    public function it_validates_name_field()
    {
        $request = new StoreTeamRequest();
        
        // Test name too long
        $data = ['name' => str_repeat('a', 256)];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        
        // Test valid name
        $data = ['name' => 'Valid Team Name'];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('name'));
    }

    #[Test]
    public function it_validates_slug_uniqueness()
    {
        Team::factory()->create(['slug' => 'existing-slug']);
        
        $request = new StoreTeamRequest();
        $data = [
            'name' => 'Test Team',
            'slug' => 'existing-slug'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());
    }

    #[Test]
    public function it_validates_slug_format()
    {
        $request = new StoreTeamRequest();
        
        // Test invalid slug format
        $data = [
            'name' => 'Test Team',
            'slug' => 'Invalid Slug With Spaces'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());
        
        // Test valid slug format
        $data = [
            'name' => 'Test Team',
            'slug' => 'valid-slug-123'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('slug'));
    }

    #[Test]
    public function it_validates_description_length()
    {
        $request = new StoreTeamRequest();
        
        // Test description too long
        $data = [
            'name' => 'Test Team',
            'description' => str_repeat('a', 1001)
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
        
        // Test valid description
        $data = [
            'name' => 'Test Team',
            'description' => 'Valid description'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('description'));
    }

    #[Test]
    public function it_validates_organization_exists()
    {
        $request = new StoreTeamRequest();
        
        // Test non-existent organization
        $data = [
            'name' => 'Test Team',
            'organization_id' => 999999
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('organization_id', $validator->errors()->toArray());
        
        // Test valid organization
        $data = [
            'name' => 'Test Team',
            'organization_id' => $this->organization->id
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('organization_id'));
    }

    #[Test]
    public function it_validates_max_members_range()
    {
        $request = new StoreTeamRequest();
        
        // Test max_members too low
        $data = [
            'name' => 'Test Team',
            'max_members' => 0
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('max_members', $validator->errors()->toArray());
        
        // Test max_members too high
        $data = [
            'name' => 'Test Team',
            'max_members' => 1001
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('max_members', $validator->errors()->toArray());
        
        // Test valid max_members
        $data = [
            'name' => 'Test Team',
            'max_members' => 25
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('max_members'));
    }

    #[Test]
    public function it_auto_generates_slug_from_name()
    {
        $data = ['name' => 'My Awesome Team'];
        
        $request = new StoreTeamRequest();
        $request->replace($data);
        $request->prepareForValidation();
        
        $this->assertEquals('my-awesome-team', $request->get('slug'));
    }

    #[Test]
    public function it_sets_created_by_user_id_automatically()
    {
        $data = ['name' => 'Test Team'];
        
        $request = new StoreTeamRequest();
        $request->replace($data);
        $request->prepareForValidation();
        
        $this->assertEquals($this->user->id, $request->get('created_by_user_id'));
    }

    #[Test]
    public function it_sets_default_is_open_value()
    {
        $data = ['name' => 'Test Team'];
        
        $request = new StoreTeamRequest();
        $request->replace($data);
        $request->prepareForValidation();
        
        $this->assertFalse($request->get('is_open'));
        
        // Test with explicit value
        $data = ['name' => 'Test Team', 'is_open' => '1'];
        
        $request = new StoreTeamRequest();
        $request->replace($data);
        $request->prepareForValidation();
        
        $this->assertTrue($request->get('is_open'));
    }

    #[Test]
    public function it_validates_slug_uniqueness_within_organization()
    {
        // Create team with slug in organization A
        Team::factory()->create([
            'slug' => 'test-slug',
            'organization_id' => $this->organization->id
        ]);
        
        // Create another organization
        $anotherOrg = Organization::factory()->create();
        
        $data = [
            'name' => 'Test Team',
            'slug' => 'test-slug',
            'organization_id' => $anotherOrg->id
        ];
        
        $request = new StoreTeamRequest();
        $request->replace($data);
        $request->setRouteResolver(function () {
            return new \Illuminate\Routing\Route('POST', 'test', []);
        });
        
        $validator = Validator::make($data, $request->rules());
        
        // Should pass because it's a different organization
        $this->assertFalse($validator->errors()->has('slug'));
        
        // Test same organization should fail
        $data['organization_id'] = $this->organization->id;
        
        $request = new StoreTeamRequest();
        $request->replace($data);
        $request->setRouteResolver(function () {
            return new \Illuminate\Routing\Route('POST', 'test', []);
        });
        
        // Simulate the withValidator method
        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);
        
        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function it_allows_null_values_for_optional_fields()
    {
        $request = new StoreTeamRequest();
        
        $data = [
            'name' => 'Test Team',
            'slug' => null,
            'description' => null,
            'organization_id' => null,
            'max_members' => null
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        // Should not have errors for nullable fields
        $this->assertFalse($validator->errors()->has('slug'));
        $this->assertFalse($validator->errors()->has('description'));
        $this->assertFalse($validator->errors()->has('organization_id'));
        $this->assertFalse($validator->errors()->has('max_members'));
    }

    #[Test]
    public function it_provides_custom_error_messages()
    {
        $request = new StoreTeamRequest();
        $messages = $request->messages();
        
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('name.max', $messages);
        $this->assertArrayHasKey('slug.unique', $messages);
        $this->assertArrayHasKey('slug.regex', $messages);
        $this->assertArrayHasKey('description.max', $messages);
        $this->assertArrayHasKey('organization_id.exists', $messages);
        $this->assertArrayHasKey('max_members.min', $messages);
        $this->assertArrayHasKey('max_members.max', $messages);
        
        // Check that messages are in Spanish
        $this->assertStringContains('obligatorio', $messages['name.required']);
        $this->assertStringContains('exceder', $messages['name.max']);
    }

    #[Test]
    public function it_authorizes_authenticated_users()
    {
        $request = new StoreTeamRequest();
        
        $this->assertTrue($request->authorize());
    }

    #[Test]
    public function it_handles_boolean_conversion_correctly()
    {
        $testCases = [
            ['is_open' => 'true', 'expected' => true],
            ['is_open' => '1', 'expected' => true],
            ['is_open' => 1, 'expected' => true],
            ['is_open' => true, 'expected' => true],
            ['is_open' => 'false', 'expected' => false],
            ['is_open' => '0', 'expected' => false],
            ['is_open' => 0, 'expected' => false],
            ['is_open' => false, 'expected' => false],
        ];
        
        foreach ($testCases as $case) {
            $data = ['name' => 'Test Team', 'is_open' => $case['is_open']];
            
            $request = new StoreTeamRequest();
            $request->replace($data);
            $request->prepareForValidation();
            
            $this->assertEquals(
                $case['expected'], 
                $request->get('is_open'),
                "Failed for input: " . json_encode($case['is_open'])
            );
        }
    }

    #[Test]
    public function it_preserves_explicit_slug_when_provided()
    {
        $data = [
            'name' => 'My Awesome Team',
            'slug' => 'custom-slug'
        ];
        
        $request = new StoreTeamRequest();
        $request->replace($data);
        $request->prepareForValidation();
        
        $this->assertEquals('custom-slug', $request->get('slug'));
    }
}
