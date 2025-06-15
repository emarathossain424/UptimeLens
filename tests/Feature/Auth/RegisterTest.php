<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Test user can register with valid data.
     * This test checks that the registration endpoint allows a new user to register successfully
     * when provided with valid data.
     * It ensures that the API returns a 201 status code and a success message with user details.
     * This is important to verify that the registration functionality works as expected.
     * It also checks that the user is correctly saved in the database with the provided email and name.
     * This test is crucial for ensuring that new users can create accounts in the application.
     * It verifies that the registration process is functioning correctly and that the API responds with the expected data.
     * It helps to ensure that the application can handle user registrations without errors.
     */
    public function user_can_register_with_valid_data()
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'organization_name'     => 'Test Org',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Registration successful!',
                'data' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name'  => 'Test User',
        ]);
    }

    /**
     * @test
     * Test registration fails with missing fields.
     * This test checks that the registration endpoint correctly handles cases where required fields are missing.
     * It ensures that the API returns a 422 status code and a validation error for the missing fields.
     */
    public function registration_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    /** @test */
    public function registration_fails_with_invalid_email()
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Test User',
            'email'                 => 'not-an-email',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * @test
     * Test registration fails with short password.
     * This test checks that the registration endpoint correctly handles cases where the password is too short.
     * It ensures that the API returns a 422 status code and a validation error for the password field.
     * This is important to enforce security standards and ensure that users create strong passwords.
     * It verifies that the password must be at least 6 characters long.
     * This test is crucial for maintaining the security of user accounts.
     * It ensures that the API does not allow weak passwords that could compromise user security.
     * It helps to prevent potential security vulnerabilities by enforcing a minimum password length.
     */
    public function registration_fails_with_short_password()
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Test User',
            'email'                 => 'test2@example.com',
            'password'              => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * @test
     * Test registration fails when password confirmation does not match.
     * This test checks that the registration endpoint correctly handles cases where the password confirmation
     * does not match the original password.
     * It ensures that the API returns a 422 status code and a validation error for the password field.
     * This is important to ensure that users provide consistent and matching passwords during registration.
     */
    public function registration_fails_when_password_confirmation_does_not_match()
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Test User',
            'email'                 => 'test3@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * @test
     * Test registration fails with duplicate email.
     * This test checks that the registration endpoint correctly handles attempts to register with an email
     * that is already in use by another user.
     * It ensures that the API returns a 422 status code and a validation error for the email field.
     * This is important to prevent duplicate accounts and maintain data integrity.
     */
    public function registration_fails_with_duplicate_email()
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name'                  => 'Another User',
            'email'                 => 'duplicate@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
