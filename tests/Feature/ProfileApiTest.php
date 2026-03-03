<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_name_and_bio(): void
    {
        $user = User::factory()->create([
            'name' => 'Nombre inicial',
            'bio' => null,
        ]);

        $token = Str::random(80);
        $user->forceFill(['api_token' => hash('sha256', $token)])->save();

        $response = $this->withToken($token)->patchJson('/api/profile', [
            'name' => 'Nuevo Nombre',
            'bio' => 'Bio personalizada para pruebas.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Nuevo Nombre')
            ->assertJsonPath('data.bio', 'Bio personalizada para pruebas.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nuevo Nombre',
            'bio' => 'Bio personalizada para pruebas.',
        ]);
    }

    public function test_user_can_upload_avatar_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar_url' => null,
        ]);

        $token = Str::random(80);
        $user->forceFill(['api_token' => hash('sha256', $token)])->save();

        $file = UploadedFile::fake()->image('avatar.jpg', 320, 320);

        $response = $this->withToken($token)->post(
            '/api/profile/avatar',
            ['avatar' => $file],
            ['Accept' => 'application/json']
        );

        $response->assertOk()->assertJsonStructure([
            'data' => ['id', 'name', 'avatar_url'],
        ]);

        $user->refresh();

        $this->assertNotNull($user->avatar_url);

        $path = parse_url($user->avatar_url, PHP_URL_PATH);
        $relativePath = ltrim(Str::after((string) $path, '/storage/'), '/');

        Storage::disk('public')->assertExists($relativePath);
    }

    public function test_user_can_delete_avatar_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $path = UploadedFile::fake()->image('avatar.png', 280, 280)
            ->store("avatars/{$user->id}", 'public');

        $user->update([
            'avatar_url' => Storage::disk('public')->url($path),
        ]);

        $token = Str::random(80);
        $user->forceFill(['api_token' => hash('sha256', $token)])->save();

        $response = $this->withToken($token)->deleteJson('/api/profile/avatar');

        $response->assertOk()->assertJsonPath('message', 'Avatar eliminado correctamente.');

        $user->refresh();

        $this->assertNull($user->avatar_url);
        Storage::disk('public')->assertMissing($path);
    }
}
