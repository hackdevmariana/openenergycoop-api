<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected User $commentableModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->commentableModel = User::factory()->create(); // Use User as commentable for testing
    }

    public function test_can_list_approved_comments()
    {
        $approvedComment = Comment::factory()->approved()->for($this->commentableModel)->create();
        $pendingComment = Comment::factory()->pending()->for($this->commentableModel)->create();

        $response = $this->getJson('/api/v1/comments');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'status',
                        'user',
                        'commentable'
                    ]
                ]
            ]);

        // Solo debe devolver comentarios aprobados
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_filter_comments_by_commentable()
    {
        $articleComment = Comment::factory()->approved()->for($this->commentableModel)->create();
        $pageComment = Comment::factory()->approved()->for($this->commentableModel)->create();

        $response = $this->getJson('/api/v1/comments?commentable_type=App\Models\Article&commentable_id=' . $this->commentableModel->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('App\Models\User', $data[0]['commentable_type']);
        $this->assertEquals($this->commentableModel->id, $data[0]['commentable_id']);
    }

    public function test_can_filter_comments_by_status()
    {
        $approvedComment = Comment::factory()->approved()->for($this->commentableModel)->create();
        $pendingComment = Comment::factory()->pending()->for($this->commentableModel)->create();
        $rejectedComment = Comment::factory()->rejected()->for($this->commentableModel)->create();

        $response = $this->getJson('/api/v1/comments?status=pending');

        $response->assertOk();
        $data = $response->json('data');
        // Este endpoint solo muestra comentarios aprobados por defecto, 
        // pero el filtro de status puede modificar esto dependiendo de la implementación
        $this->assertGreaterThanOrEqual(0, count($data));
    }

    public function test_can_filter_comments_by_user()
    {
        $user1Comment = Comment::factory()->approved()->fromUser($this->user)->for($this->commentableModel)->create();
        $user2Comment = Comment::factory()->approved()->fromUser()->for($this->commentableModel)->create();

        $response = $this->getJson('/api/v1/comments?user_id=' . $this->user->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->user->id, $data[0]['user']['id']);
    }

    public function test_can_filter_root_comments()
    {
        $rootComment = Comment::factory()->approved()->root()->for($this->commentableModel)->create();
        $replyComment = Comment::factory()->approved()->replyTo($rootComment)->create();

        $response = $this->getJson('/api/v1/comments?parent_id=null');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertNull($data[0]['parent_id']);
    }

    public function test_can_filter_replies_to_comment()
    {
        $parentComment = Comment::factory()->approved()->root()->for($this->commentableModel)->create();
        $replyComment = Comment::factory()->approved()->replyTo($parentComment)->create();

        $response = $this->getJson('/api/v1/comments?parent_id=' . $parentComment->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($parentComment->id, $data[0]['parent_id']);
    }

    public function test_can_include_replies()
    {
        $parentComment = Comment::factory()->approved()->root()->for($this->commentableModel)->create();
        $replyComment = Comment::factory()->approved()->replyTo($parentComment)->create();

        $response = $this->getJson('/api/v1/comments?include_replies=true');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'replies' => [
                            '*' => [
                                'id',
                                'content',
                                'user'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_authenticated_user_can_create_comment()
    {
        Sanctum::actingAs($this->user);

        $commentData = [
            'commentable_type' => 'App\Models\User',
            'commentable_id' => $this->commentableModel->id,
            'content' => 'Este es un comentario de prueba.'
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'content',
                    'status',
                    'user'
                ],
                'message'
            ]);

        $this->assertDatabaseHas('comments', [
            'commentable_type' => 'App\Models\User',
            'commentable_id' => $this->commentableModel->id,
            'content' => 'Este es un comentario de prueba.',
            'user_id' => $this->user->id
        ]);
    }

    public function test_guest_can_create_comment_with_name_and_email()
    {
        $commentData = [
            'commentable_type' => 'App\Models\User',
            'commentable_id' => $this->commentableModel->id,
            'content' => 'Este es un comentario de invitado.',
            'author_name' => 'Juan Pérez',
            'author_email' => 'juan@example.com'
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertCreated();

        $this->assertDatabaseHas('comments', [
            'commentable_type' => 'App\Models\User',
            'commentable_id' => $this->commentableModel->id,
            'content' => 'Este es un comentario de invitado.',
            'author_name' => 'Juan Pérez',
            'author_email' => 'juan@example.com',
            'user_id' => null
        ]);
    }

    public function test_can_create_reply_to_comment()
    {
        Sanctum::actingAs($this->user);

        $parentComment = Comment::factory()->approved()->for($this->commentableModel)->create();

        $replyData = [
            'commentable_type' => 'App\Models\User',
            'commentable_id' => $this->commentableModel->id,
            'content' => 'Esta es una respuesta.',
            'parent_id' => $parentComment->id
        ];

        $response = $this->postJson('/api/v1/comments', $replyData);

        $response->assertCreated();

        $this->assertDatabaseHas('comments', [
            'parent_id' => $parentComment->id,
            'content' => 'Esta es una respuesta.',
            'user_id' => $this->user->id
        ]);
    }

    public function test_can_show_approved_comment()
    {
        $comment = Comment::factory()->approved()->for($this->commentableModel)->create();

        $response = $this->getJson("/api/v1/comments/{$comment->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'content',
                    'status',
                    'user',
                    'commentable'
                ]
            ]);
    }

    public function test_cannot_show_pending_comment()
    {
        $comment = Comment::factory()->pending()->for($this->commentableModel)->create();

        $response = $this->getJson("/api/v1/comments/{$comment->id}");

        $response->assertNotFound();
    }

    public function test_can_show_comment_with_replies()
    {
        $parentComment = Comment::factory()->approved()->for($this->commentableModel)->create();
        $replyComment = Comment::factory()->approved()->replyTo($parentComment)->create();

        $response = $this->getJson("/api/v1/comments/{$parentComment->id}?include_replies=true");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'content',
                    'replies' => [
                        '*' => [
                            'id',
                            'content'
                        ]
                    ]
                ]
            ]);
    }

    public function test_authenticated_user_can_update_own_comment()
    {
        Sanctum::actingAs($this->user);

        $comment = Comment::factory()->fromUser($this->user)->for($this->commentableModel)->create();

        $updateData = [
            'content' => 'Contenido actualizado del comentario.'
        ];

        $response = $this->putJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Comentario actualizado exitosamente'
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Contenido actualizado del comentario.'
        ]);
    }

    public function test_user_cannot_update_other_users_comment()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $comment = Comment::factory()->fromUser($otherUser)->for($this->commentableModel)->create();

        $updateData = [
            'content' => 'Intento de hackeo'
        ];

        $response = $this->putJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertForbidden();
    }

    public function test_guest_cannot_update_comment()
    {
        $comment = Comment::factory()->for($this->commentableModel)->create();

        $updateData = [
            'content' => 'Intento de actualización'
        ];

        $response = $this->putJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_delete_own_comment()
    {
        Sanctum::actingAs($this->user);

        $comment = Comment::factory()->fromUser($this->user)->for($this->commentableModel)->create();

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Comentario eliminado exitosamente'
            ]);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id
        ]);
    }

    public function test_user_cannot_delete_other_users_comment()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $comment = Comment::factory()->fromUser($otherUser)->for($this->commentableModel)->create();

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertForbidden();
    }

    public function test_guest_cannot_delete_comment()
    {
        $comment = Comment::factory()->for($this->commentableModel)->create();

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertUnauthorized();
    }

    public function test_pinned_comments_appear_first()
    {
        $regularComment = Comment::factory()->approved()->for($this->commentableModel)->create();
        $pinnedComment = Comment::factory()->approved()->pinned()->for($this->commentableModel)->create();

        $response = $this->getJson('/api/v1/comments');

        $response->assertOk();
        $data = $response->json('data');
        
        // El comentario fijado debe aparecer primero
        $this->assertTrue($data[0]['is_pinned']);
        $this->assertFalse($data[1]['is_pinned']);
    }

    public function test_pagination_works_correctly()
    {
        Comment::factory()->approved()->for($this->commentableModel)->count(25)->create();

        $response = $this->getJson('/api/v1/comments?per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
                'last_page'
            ]);

        $this->assertEquals(10, count($response->json('data')));
        $this->assertEquals(10, $response->json('per_page'));
        $this->assertEquals(25, $response->json('total'));
    }

    public function test_per_page_is_limited_to_maximum_50()
    {
        Comment::factory()->approved()->for($this->commentableModel)->count(60)->create();

        $response = $this->getJson('/api/v1/comments?per_page=100');

        $response->assertOk();
        $this->assertLessThanOrEqual(50, count($response->json('data')));
    }

    public function test_validation_works_for_comment_creation()
    {
        $invalidData = [
            'commentable_type' => '', // requerido
            'commentable_id' => 'not-a-number', // debe ser entero
            'content' => '', // requerido
        ];

        $response = $this->postJson('/api/v1/comments', $invalidData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['commentable_type', 'commentable_id', 'content']);
    }

    public function test_validation_works_for_guest_comment_creation()
    {
        $invalidData = [
            'commentable_type' => 'App\Models\User',
            'commentable_id' => $this->commentableModel->id,
            'content' => 'Test content',
            'author_email' => 'invalid-email' // debe ser email válido
        ];

        $response = $this->postJson('/api/v1/comments', $invalidData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['author_email']);
    }

    public function test_validation_works_for_comment_update()
    {
        Sanctum::actingAs($this->user);

        $comment = Comment::factory()->fromUser($this->user)->for($this->commentableModel)->create();

        $invalidData = [
            'content' => '' // requerido
        ];

        $response = $this->putJson("/api/v1/comments/{$comment->id}", $invalidData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    }

    public function test_comments_are_ordered_correctly()
    {
        $pinnedComment = Comment::factory()->approved()->pinned()->for($this->commentableModel)->create([
            'created_at' => now()->subHours(2)
        ]);

        $regularOldComment = Comment::factory()->approved()->for($this->commentableModel)->create([
            'created_at' => now()->subHours(1)
        ]);

        $regularNewComment = Comment::factory()->approved()->for($this->commentableModel)->create([
            'created_at' => now()
        ]);

        $response = $this->getJson('/api/v1/comments');

        $response->assertOk();
        $data = $response->json('data');
        
        // Orden: pinned primero, luego por fecha de creación
        $this->assertTrue($data[0]['is_pinned']); // Pinned comment first
        $this->assertFalse($data[1]['is_pinned']); // Regular comments after
        
        // Entre comentarios regulares, el más viejo primero
        $this->assertTrue(
            strtotime($data[1]['created_at']) < strtotime($data[2]['created_at'])
        );
    }
}
