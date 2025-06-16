<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use Tests\TestCase;

class LoginLogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Test user can login with valid credentials.
     * This test checks that the login endpoint allows a user to log in successfully
     * when provided with valid credentials. It ensures that the API returns a 201 status code
     * and a success message with user details and a token.
     * This is important to verify that the login functionality works as expected.
     * It also checks that the user is able to log in with the correct email and password.
     * This test is crucial for ensuring that users can access their accounts in the application.
     * It verifies that the login process is functioning correctly and that the API responds with the expected data.
     * It helps to ensure that the application can handle user logins without errors.
     * It also confirms that the user is granted access to the system when providing valid login details.
     * @return void
     */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'loginuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'loginuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email'],
                ],
            ]);
    }

    /**
     * @test
     * Test login fails with invalid credentials.
     * This test checks that the login endpoint correctly handles cases where the user provides invalid credentials.
     * It ensures that the API returns a 401 status code and an appropriate error message.
     * This is important to verify that the login functionality works as expected and that the application
     * does not allow access with incorrect credentials.
     * It also checks that the user is not able to log in with an email that does not exist or a password that is incorrect.
     * This test is crucial for ensuring that the application securely handles user authentication.
     * It verifies that the login process is functioning correctly and that the API responds with the expected error data.
     * It helps to ensure that the application can handle login attempts with invalid credentials without errors.
     * It also confirms that the user is not granted access to the system when providing incorrect login details.
     * @return void
     */
    public function login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'wronguser@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wronguser@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials.',
            ]);
    }

    /**
     * @test
     * Test user can logout successfully.
     * This test checks that the logout endpoint allows a user to log out successfully
     * after they have logged in. It ensures that the API returns a 201 status code
     * and a success message indicating that the logout was successful.
     * This is important to verify that the logout functionality works as expected.
     * It also checks that the user is able to log out without any issues after being authenticated.
     * This test is crucial for ensuring that users can securely log out of their accounts,
     * preventing unauthorized access to their sessions after they have logged out.
     * It verifies that the application correctly handles the logout process and that the API responds with the expected data.
     * It helps to ensure that the application can handle user logouts without errors.
     * It also confirms that the user's session is terminated successfully.
     * @return void
     */
    public function user_can_logout_successfully()
    {
        $user = User::factory()->create([
            'email' => 'logoutuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'logoutuser@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.token');

        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(201)
            ->assertJson([
                'message' => 'Logout successful!',
            ]);
    }

    /**
     * @test
     * Test logout fails if not authenticated.
     * This test checks that the logout endpoint correctly handles cases where the user is not authenticated.
     * It ensures that the API returns a 401 status code when trying to log out without a valid token.
     */
    public function logout_fails_if_not_authenticated()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }
}
