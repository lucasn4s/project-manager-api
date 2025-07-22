<?php

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_saves_projects()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'multipart/form-data',
        ])->post(route('projects.store'), [
            'name' => 'Test Project',
            'customer' => 'Test Customer',
            'start_date' => '2023-01-01',
            'end_date' => '2023-01-31',
        ]);

        $this->assertEquals(1, Project::count());
        $this->assertEquals(201, $response->status());
    }

    public function test_saves_projects_with_image()
    {
        $file = UploadedFile::fake()->image('test.png');

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'multipart/form-data',
        ])->post(route('projects.store'), [
            'name' => 'Test Project',
            'customer' => 'Test Customer',
            'start_date' => '2023-01-01',
            'end_date' => '2023-01-31',
            'image' => $file,
        ]);

        $this->assertEquals(1, Project::count());
        $this->assertTrue(Storage::exists('public/images/' . $file->hashName()));
        $response->assertStatus(201);
    }

    public function test_shows_project()
    {
        $project = Project::factory()->create();
        $response = $this->getJson(route('projects.show', $project));

        $this->assertEquals(200, $response->status());
        $this->assertEquals($project->toArray(), $response->json()['project']);
    }

    public function test_shows_project_with_image()
    {
        $project = Project::factory()->create();
        $file = UploadedFile::fake()->image('test.png');
        Storage::putFileAs('public/images', $file, $file->hashName());
        $project->image = $file->hashName();
        $project->save();

        $response = $this->getJson(route('projects.show', $project));

        $this->assertEquals(200, $response->status());
        $this->assertEquals(Storage::url('public/images/' . $file->hashName()), $response->json()['image']);
    }

    public function test_updates_project()
    {
        $project = Project::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'multipart/form-data',
        ])->put(route('projects.update', $project), [
            'name' => 'Test Project Updated',
            'customer' => 'Test Customer Updated',
            'start_date' => '2023-01-01',
            'end_date' => '2023-01-31',
        ]);

        $this->assertEquals(1, Project::count());
        $this->assertEquals(200, $response->status());
        $this->assertEquals('Test Project Updated', $project->fresh()->name);
    }

    public function test_updates_project_with_image()
    {
        $project = Project::factory()->create();
        $file = UploadedFile::fake()->image('test.png');

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'multipart/form-data',
        ])->put(route('projects.update', $project), [
            'name' => 'Test Project Updated',
            'customer' => 'Test Customer Updated',
            'start_date' => '2023-01-01',
            'end_date' => '2023-01-31',
            'image' => $file,
        ]);

        $this->assertEquals(1, Project::count());
        $this->assertTrue(Storage::exists('public/images/' . $file->hashName()));
        $this->assertEquals($file->hashName(), $project->fresh()->image);
    }
}

