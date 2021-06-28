<?php

namespace Tests\E2E;

use App\Models\Commerce;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;

class CreateCommerceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_commerce()
    {
        $commerce = Commerce::factory()->make()->toArray();
        $user = User::factory()->create();

        $commerce['currency'] = ['id' => 1];


        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/commerces', $commerce);


        $commerceDB = Commerce::firstOrFail();

        $this->assertAuthenticated('sanctum');
        $this->assertTrue(!! Commerce::ofUser($user)->first());

        $response->assertCreated()
            ->assertJson(['id' => $commerceDB->id]);

    }

    public function test_can_update_commerce()
    {
        $commerce = Commerce::factory()->create();
        $user = User::factory()->create();

        $commerce->users()->attach($user->id);


        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/auth/commerces/' . $commerce->id, ['fullname' => 'Custom Fake Fullname']);


        $this->assertAuthenticated('sanctum');

        $response->assertOk()
            ->assertJson(['fullname' => 'Custom Fake Fullname']);
    }

    // public function test_avatars_can_be_uploaded()
    // {
    //     Storage::fake('avatars');

    //     $file = UploadedFile::fake()->image('avatar.jpg');

    //     $response = $this->post('/avatar', [
    //         'avatar' => $file,
    //     ]);

    //     Storage::disk('avatars')->assertExists($file->hashName());
    // }

    public function test_admin_can_update_any_commerce()
    {
        $commerce = Commerce::factory()->create();
        $user = User::factory()->create([
            'admin' => 1,
        ]);


        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/auth/commerces/' . $commerce->id, ['fullname' => 'Custom Fake Fullname']);


        $response->assertOk();
    }

    public function test_user_cannot_update_not_own_commerce()
    {
        $commerce = Commerce::factory()->create();
        $user = User::factory()->create();


        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/auth/commerces/' . $commerce->id, ['fullname' => 'Custom Fake Fullname']);


        $response->assertUnauthorized();
    }

    public function test_cannot_create_commerce_name_is_taken()
    {
        $commerce = Commerce::factory()->create();
        $user = User::factory()->create();

        $commerceArray = $commerce->toArray();
        $commerceArray['currency'] = ['id' => 1];


        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/commerces', $commerceArray);


        $this->assertAuthenticated('sanctum');

        $response->assertStatus(422)
            ->assertJson(["message" => "The given data was invalid."])
            ->assertJsonStructure(['message', 'errors']);
    }
}