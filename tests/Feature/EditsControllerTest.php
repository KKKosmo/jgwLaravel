<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Edit;

class EditsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_and_get_all_edits()
    {
        // Step 1: Create a new edit using POST request
        $createResponse = $this->postJson('/api/edits', [
            'record_id' => 1,
            'edit_timestamp' => now(),
            'summary' => 'Test Edit',
            'user' => 'testuser',
        ]);

        $createResponse->assertStatus(201);

        // Step 2: Retrieve all edits
        $allEditsResponse = $this->getJson('/api/edits');

        $allEditsResponse->assertStatus(200);
        $allEditsResponse->assertJsonFragment(['summary' => 'Test Edit']);
    }
}
