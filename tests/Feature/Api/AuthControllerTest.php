<?php

namespace Tests\Feature\Api;

use AuthServer\User;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('passport:install');
    }

    /** @test */
    public function testUserCanRegister()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertCount(1, User::all());

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com'
        ]);

        $response->assertExactJson(['created' => true])
                 ->assertStatus(201);
    }

    /** @test */
    public function testNameIsRequiredInRegistration()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'name' => '',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertCount(0, User::all());

        $response->assertJsonValidationErrors('name')
                ->assertJsonStructure(['errors' => ['name']])
                ->assertStatus(422);
    }

    /** @test */
    public function testEmailIsRequiredInRegistration()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'name' => 'John Doe',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertCount(0, User::all());

        $response->assertJsonValidationErrors('email')
                ->assertJsonStructure(['errors' => ['email']])
                ->assertStatus(422);
    }

    /** @test */
    public function testEmailMustBeUniqueInRegistration()
    {
        factory(User::class)->create(['email' => 'johndoe@example.com']);

        $response = $this->json('POST', '/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertCount(1, User::all());

        $response->assertJsonValidationErrors('email')
                ->assertJsonStructure(['errors' => ['email']])
                ->assertStatus(422);
    }

    /** @test */
    public function testPasswordIsRequiredInRegistration()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => '',
            'password_confirmation' => 'password',
        ]);

        $this->assertCount(0, User::all());

        $response->assertJsonValidationErrors('password')
                ->assertJsonStructure(['errors' => ['password']])
                ->assertStatus(422);
    }

    /** @test */
    public function testPasswordMustBeAtleast8CharactersInRegistration()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'passwor',
            'password_confirmation' => 'passwor',
        ]);

        $this->assertCount(0, User::all());

        $response->assertJsonValidationErrors('password')
                ->assertJsonStructure(['errors' => ['password']])
                ->assertStatus(422);
    }

    /** @test */
    public function testPassAndConfirmationIsMatchedInRegistration()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'password_confirmation' => '',
        ]);

        $this->assertCount(0, User::all());

        $response->assertJsonValidationErrors('password')
                ->assertJsonStructure(['errors' => ['password']])
                ->assertStatus(422);
    }

    /** @test */
    public function testUserCanLogin()
    {
        $this->withoutExceptionHandling();

        factory(User::class)->create(['email' => 'johndoe@example.com']);

        $response = $this->json('POST', '/api/auth/login', [
            'email' => 'johndoe@example.com',
            'password' => 'password',
        ]);

        $response->assertJsonStructure(['token'])
                 ->assertStatus(200);
    }

    /** @test */
    public function testUserEmailIsRequiredToLogin()
    {
        $response = $this->json('POST', '/api/auth/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertJsonValidationErrors('email')
                ->assertJsonStructure(['errors' => ['email']])
                ->assertStatus(422);
    }

    /** @test */
    public function testUserPasswordIsRequiredToLogin()
    {
        $response = $this->json('POST', '/api/auth/login', [
            'email' => 'johndoe@example.com',
            'password' => '',
        ]);

        $response->assertJsonValidationErrors('password')
                ->assertJsonStructure(['errors' => ['password']])
                ->assertStatus(422);
    }

    /** @test */
    public function testUserCanViewProfile()
    {
        Passport::actingAs(
            factory(User::class)->create()
        );

        $response = $this->json('GET', '/api/auth/profile');

        $response->assertStatus(200);
    }

    /** @test */
    public function testGuestUserCannotViewProfile()
    {
        $response = $this->json('GET', '/api/auth/profile');

        $response->assertStatus(401);
    }

    /** @test */
    public function testUserCanLogout()
    {
        $this->withoutExceptionHandling();

        Passport::actingAs(
            factory(User::class)->create()
        );

        $response = $this->json('POST', '/api/auth/logout');
        
        $response->assertJsonStructure(['logged_out'])
                ->assertStatus(200);
    }

    /** @test */
    public function testGuestUserCannotLogout()
    {
        $response = $this->json('POST', '/api/auth/logout');

        $response->assertStatus(401);
    }
}
