<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Article;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = array_keys(Comment::STATUSES);
        $isRegisteredUser = $this->faker->boolean(70); // 70% chance of registered user

        return [
            'commentable_type' => $this->faker->randomElement([Article::class, Page::class]),
            'commentable_id' => function (array $attributes) {
                // Create the related model dynamically
                $modelClass = $attributes['commentable_type'];
                if (class_exists($modelClass)) {
                    return $modelClass::factory()->create()->id;
                }
                return 1; // Fallback
            },
            'user_id' => $isRegisteredUser ? User::factory() : null,
            'author_name' => $isRegisteredUser ? null : $this->faker->name(),
            'author_email' => $isRegisteredUser ? null : $this->faker->email(),
            'content' => $this->faker->paragraphs(2, true),
            'status' => $this->faker->randomElement($statuses),
            'parent_id' => null, // Default to root comment
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'likes_count' => $this->faker->numberBetween(0, 50),
            'dislikes_count' => $this->faker->numberBetween(0, 10),
            'is_pinned' => $this->faker->boolean(5), // 5% chance of being pinned
            'approved_at' => function (array $attributes) {
                return $attributes['status'] === 'approved' ? $this->faker->dateTimeBetween('-1 month') : null;
            },
            'approved_by_user_id' => function (array $attributes) {
                return $attributes['status'] === 'approved' ? User::factory() : null;
            },
        ];
    }

    /**
     * Indicate that the comment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => $this->faker->dateTimeBetween('-1 month'),
            'approved_by_user_id' => User::factory(),
        ]);
    }

    /**
     * Indicate that the comment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_at' => null,
            'approved_by_user_id' => null,
        ]);
    }

    /**
     * Indicate that the comment is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by_user_id' => null,
        ]);
    }

    /**
     * Indicate that the comment is spam.
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'spam',
            'approved_at' => null,
            'approved_by_user_id' => null,
        ]);
    }

    /**
     * Indicate that the comment is pinned.
     */
    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
        ]);
    }

    /**
     * Create a comment from a registered user.
     */
    public function fromUser(User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user ? $user->id : User::factory(),
            'author_name' => null,
            'author_email' => null,
        ]);
    }

    /**
     * Create a comment from a guest.
     */
    public function fromGuest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'author_name' => $this->faker->name(),
            'author_email' => $this->faker->email(),
        ]);
    }

    /**
     * Create a reply to another comment.
     */
    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'commentable_type' => $parent->commentable_type,
            'commentable_id' => $parent->commentable_id,
        ]);
    }

    /**
     * Set specific commentable model.
     */
    public function for($commentable): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => get_class($commentable),
            'commentable_id' => $commentable->id,
        ]);
    }

    /**
     * Create a root comment (no parent).
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }
}