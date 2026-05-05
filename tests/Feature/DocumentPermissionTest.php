<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_permissions_follow_owner_editor_viewer_rules(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $viewer = User::factory()->create();
        $outsider = User::factory()->create();

        $document = Document::create([
            'owner_id' => $owner->id,
            'title' => 'Team Plan',
            'content' => '<p>Draft</p>',
        ]);

        DocumentShare::create([
            'document_id' => $document->id,
            'user_id' => $editor->id,
            'role' => 'editor',
        ]);

        DocumentShare::create([
            'document_id' => $document->id,
            'user_id' => $viewer->id,
            'role' => 'viewer',
        ]);

        Sanctum::actingAs($owner);
        $this->putJson("/api/v1/documents/{$document->id}", [
            'title' => 'Owner Updated',
        ])->assertOk();

        Sanctum::actingAs($editor);
        $this->putJson("/api/v1/documents/{$document->id}", [
            'content' => '<p>Editor Updated</p>',
        ])->assertOk();

        Sanctum::actingAs($viewer);
        $this->getJson("/api/v1/documents/{$document->id}")
            ->assertOk()
            ->assertJsonPath('data.access_role', 'viewer');
        $this->putJson("/api/v1/documents/{$document->id}", [
            'content' => '<p>Viewer Attempt</p>',
        ])->assertForbidden();

        Sanctum::actingAs($outsider);
        $this->getJson("/api/v1/documents/{$document->id}")
            ->assertNotFound();
    }

    public function test_owner_can_share_document_with_multiple_users_in_one_request(): void
    {
        $owner = User::factory()->create();
        $firstCollaborator = User::factory()->create();
        $secondCollaborator = User::factory()->create();

        $document = Document::create([
            'owner_id' => $owner->id,
            'title' => 'Team Plan',
            'content' => '<p>Draft</p>',
        ]);

        Sanctum::actingAs($owner);

        $this->postJson("/api/v1/documents/{$document->id}/share", [
            'user_ids' => [$firstCollaborator->id, $secondCollaborator->id],
            'role' => 'editor',
        ])
            ->assertOk()
            ->assertJsonPath('data.count', 2)
            ->assertJsonPath('data.role', 'editor');

        $this->assertDatabaseHas('document_shares', [
            'document_id' => $document->id,
            'user_id' => $firstCollaborator->id,
            'role' => 'editor',
        ]);

        $this->assertDatabaseHas('document_shares', [
            'document_id' => $document->id,
            'user_id' => $secondCollaborator->id,
            'role' => 'editor',
        ]);
    }

    public function test_owner_can_view_and_update_existing_document_shares(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();

        $document = Document::create([
            'owner_id' => $owner->id,
            'title' => 'Team Plan',
            'content' => '<p>Draft</p>',
        ]);

        $share = DocumentShare::create([
            'document_id' => $document->id,
            'user_id' => $editor->id,
            'role' => 'viewer',
        ]);

        Sanctum::actingAs($owner);

        $this->getJson("/api/v1/documents/{$document->id}/share")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user.email', $editor->email)
            ->assertJsonPath('data.0.role', 'viewer');

        $this->putJson("/api/v1/documents/{$document->id}/share/{$share->id}", [
            'role' => 'editor',
        ])
            ->assertOk()
            ->assertJsonPath('data.role', 'editor');

        $this->assertDatabaseHas('document_shares', [
            'id' => $share->id,
            'role' => 'editor',
        ]);
    }

    public function test_only_owner_can_delete_document(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();

        $document = Document::create([
            'owner_id' => $owner->id,
            'title' => 'Delete me',
            'content' => '<p>Draft</p>',
        ]);

        DocumentShare::create([
            'document_id' => $document->id,
            'user_id' => $editor->id,
            'role' => 'editor',
        ]);

        Sanctum::actingAs($editor);
        $this->deleteJson("/api/v1/documents/{$document->id}")
            ->assertForbidden();

        Sanctum::actingAs($owner);
        $this->deleteJson("/api/v1/documents/{$document->id}")
            ->assertOk();

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
        $this->assertDatabaseMissing('document_shares', ['document_id' => $document->id]);
    }
}
