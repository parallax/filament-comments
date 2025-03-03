<?php

namespace FilamentComments\Tests\Feature;

use FilamentComments\Actions\MarkCommentsAsRead;
use FilamentComments\Models\CommentRead;
use FilamentComments\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Parallax\FilamentComments\Models\FilamentComment;

class MarkCommentsAsReadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_marks_comments_as_read()
    {
        // Given
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $comments = FilamentComment::factory()->count(3)->create([
            'user_id' => $otherUser->id,
        ]);

        // When
        MarkCommentsAsRead::run($comments, $user->id);

        // Then
        $this->assertCount(3, CommentRead::all());
        foreach ($comments as $comment) {
            $this->assertTrue($comment->isReadByUser($user->id));
        }
    }

    /** @test */
    public function it_does_not_duplicate_read_status()
    {
        // Given
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $comments = FilamentComment::factory()->count(3)->create([
            'user_id' => $otherUser->id,
        ]);

        // When
        MarkCommentsAsRead::run($comments, $user->id);
        MarkCommentsAsRead::run($comments, $user->id);

        // Then
        $this->assertCount(3, CommentRead::all());
    }

    /** @test */
    public function it_marks_comments_as_read_when_opening_component()
    {
        // Given
        $this->actingAs($user = $this->createUser());
        $otherUser = $this->createUser();

        $record = YourModel::factory()->create();
        $comments = FilamentComment::factory()->count(3)->create([
            'user_id' => $otherUser->id,
            'subject_type' => get_class($record),
            'subject_id' => $record->id,
        ]);

        // When
        Livewire::test(CommentsComponent::class, [
            'record' => $record,
        ])->assertOk();

        // Then
        $this->assertCount(3, CommentRead::all());
        foreach ($comments as $comment) {
            $this->assertTrue($comment->isReadByUser($user->id));
        }
    }
}
