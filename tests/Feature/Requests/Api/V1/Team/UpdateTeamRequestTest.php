<?php

namespace Tests\Feature\Requests\Api\V1\Team;

use App\Http\Requests\Api\V1\Team\UpdateTeamRequest;
use App\Models\Organization;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateTeamRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'created_by_user_id' => $this->user->id
        ]);
        
        Sanctum::actingAs($this->user);
    }

    #[Test]
    public function it_validates_optional_fields()
    {
        $request = new UpdateTeamRequest();
        
        // Empty data should be valid (all fields are optional for updates)
        $validator = Validator::make([], $request->rules());
        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_validates_name_when_provided()
    {
        $request = new UpdateTeamRequest();
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        // Test name too long
        $data = ['name' => str_repeat('a', 256)];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        
        // Test valid name
        $data = ['name' => 'Valid Updated Name'];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('name'));
    }

    #[Test]
    public function it_validates_slug_uniqueness_ignoring_current_record()
    {
        // Create another team with a different slug
        $anotherTeam = Team::factory()->create(['slug' => 'another-slug']);
        
        $request = new UpdateTeamRequest();
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        // Should fail when trying to use another team's slug
        $data = ['slug' => 'another-slug'];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());
        
        // Should pass when using the same slug (ignoring current record)
        $data = ['slug' => $this->team->slug];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('slug'));
    }

    #[Test]
    public function it_validates_slug_format()
    {
        $request = new UpdateTeamRequest();
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        // Test invalid slug format
        $data = ['slug' => 'Invalid Slug With Spaces'];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());
        
        // Test valid slug format
        $data = ['slug' => 'valid-updated-slug'];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('slug'));
    }

    #[Test]
    public function it_validates_description_length()
    {
        $request = new UpdateTeamRequest();
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        // Test description too long
        $data = ['description' => str_repeat('a', 1001)];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
        
        // Test valid description
        $data = ['description' => 'Valid updated description'];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('description'));
    }

    #[Test]
    public function it_validates_max_members_range()
    {
        $request = new UpdateTeamRequest();
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        // Test max_members too low
        $data = ['max_members' => 0];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('max_members', $validator->errors()->toArray());
        
        // Test max_members too high
        $data = ['max_members' => 1001];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('max_members', $validator->errors()->toArray());
        
        // Test valid max_members
        $data = ['max_members' => 30];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('max_members'));
    }

    #[Test]
    public function it_auto_generates_slug_when_name_updated_without_slug()
    {
        $data = ['name' => 'Updated Team Name'];
        
        $request = new UpdateTeamRequest();
        $request->replace($data);
        $request->prepareForValidation();
        
        $this->assertEquals('updated-team-name', $request->get('slug'));
    }

    #[Test]
    public function it_preserves_explicit_slug_when_both_name_and_slug_provided()
    {
        $data = [
            'name' => 'Updated Team Name',
            'slug' => 'custom-updated-slug'
        ];
        
        $request = new UpdateTeamRequest();
        $request->replace($data);
        $request->prepareForValidation();
        
        $this->assertEquals('custom-updated-slug', $request->get('slug'));
    }

    #[Test]
    public function it_converts_is_open_to_boolean()
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
            $data = ['is_open' => $case['is_open']];
            
            $request = new UpdateTeamRequest();
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
    public function it_validates_max_members_not_less_than_current_members()
    {
        // Add 5 members to the team
        TeamMembership::factory()->count(5)->create(['team_id' => $this->team->id]);
        
        $request = new UpdateTeamRequest();
        $request->replace(['max_members' => 3]);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        $validator = Validator::make(['max_members' => 3], $request->rules());
        $request->withValidator($validator);
        
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('max_members'));
        $this->assertStringContains('no puede ser menor', $validator->errors()->first('max_members'));
    }

    #[Test]
    public function it_allows_max_members_equal_to_current_members()
    {
        // Add 5 members to the team
        TeamMembership::factory()->count(5)->create(['team_id' => $this->team->id]);
        
        $request = new UpdateTeamRequest();
        $request->replace(['max_members' => 5]);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        $validator = Validator::make(['max_members' => 5], $request->rules());
        $request->withValidator($validator);
        
        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_allows_max_members_greater_than_current_members()
    {
        // Add 5 members to the team
        TeamMembership::factory()->count(5)->create(['team_id' => $this->team->id]);
        
        $request = new UpdateTeamRequest();
        $request->replace(['max_members' => 10]);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        $validator = Validator::make(['max_members' => 10], $request->rules());
        $request->withValidator($validator);
        
        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_allows_null_max_members()
    {
        $request = new UpdateTeamRequest();
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        $data = ['max_members' => null];
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->errors()->has('max_members'));
    }

    #[Test]
    public function it_validates_slug_uniqueness_within_organization()
    {
        // Create another team in the same organization
        $anotherTeam = Team::factory()->create([
            'slug' => 'another-slug',
            'organization_id' => $this->organization->id
        ]);
        
        $request = new UpdateTeamRequest();
        $request->replace(['slug' => 'another-slug']);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        $validator = Validator::make(['slug' => 'another-slug'], $request->rules());
        $request->withValidator($validator);
        
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('slug'));
    }

    #[Test]
    public function it_allows_same_slug_in_different_organizations()
    {
        // Create another organization and team
        $anotherOrg = Organization::factory()->create();
        $anotherTeam = Team::factory()->create([
            'slug' => 'same-slug',
            'organization_id' => $anotherOrg->id
        ]);
        
        $request = new UpdateTeamRequest();
        $request->replace(['slug' => 'same-slug']);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        $validator = Validator::make(['slug' => 'same-slug'], $request->rules());
        $request->withValidator($validator);
        
        // Should pass because it's a different organization
        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_provides_custom_error_messages()
    {
        $request = new UpdateTeamRequest();
        $messages = $request->messages();
        
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('name.max', $messages);
        $this->assertArrayHasKey('slug.required', $messages);
        $this->assertArrayHasKey('slug.unique', $messages);
        $this->assertArrayHasKey('slug.regex', $messages);
        $this->assertArrayHasKey('description.max', $messages);
        $this->assertArrayHasKey('max_members.min', $messages);
        $this->assertArrayHasKey('max_members.max', $messages);
        
        // Check that messages are in Spanish
        $this->assertStringContains('obligatorio', $messages['name.required']);
        $this->assertStringContains('exceder', $messages['name.max']);
    }

    #[Test]
    public function it_authorizes_authenticated_users()
    {
        $request = new UpdateTeamRequest();
        
        $this->assertTrue($request->authorize());
    }

    #[Test]
    public function it_handles_partial_updates()
    {
        $request = new UpdateTeamRequest();
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('PUT', 'test/{team}', []);
            $route->bind(new \Illuminate\Http\Request());
            $route->setParameter('team', $this->team);
            return $route;
        });
        
        // Test updating only name
        $data = ['name' => 'Only Name Updated'];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
        
        // Test updating only description
        $data = ['description' => 'Only description updated'];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
        
        // Test updating only is_open
        $data = ['is_open' => true];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
        
        // Test updating only max_members
        $data = ['max_members' => 50];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_uses_sometimes_validation_for_all_fields()
    {
        $request = new UpdateTeamRequest();
        $rules = $request->rules();
        
        foreach ($rules as $field => $fieldRules) {
            if (is_array($fieldRules)) {
                $this->assertContains('sometimes', $fieldRules, "Field {$field} should have 'sometimes' rule");
            } else {
                $this->assertStringContains('sometimes', $fieldRules, "Field {$field} should have 'sometimes' rule");
            }
        }
    }

    #[Test]
    public function it_does_not_auto_generate_slug_when_only_slug_provided()
    {
        $data = ['slug' => 'explicit-slug-only'];
        
        $request = new UpdateTeamRequest();
        $request->replace($data);
        $request->prepareForValidation();
        
        $this->assertEquals('explicit-slug-only', $request->get('slug'));
        $this->assertNull($request->get('name'));
    }
}
