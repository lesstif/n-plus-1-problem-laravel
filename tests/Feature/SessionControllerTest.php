<?php

use App\Models\User;

test('guests can open the login form', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSeeText('로그인');
});

test('users can log in with valid credentials and receive a new session', function () {
    $user = User::factory()->create();
    $this->get(route('login'));
    $sessionId = session()->getId();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirectToRoute('posts.index')
        ->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
    expect(session()->getId())->not->toBe($sessionId);
});

test('login returns users to the post form they requested', function () {
    $user = User::factory()->create();

    $this->withSession(['url.intended' => route('posts.create')])
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirectToRoute('posts.create');

    $this->assertAuthenticatedAs($user);
});

test('invalid credentials leave the user signed out without flashing the password', function () {
    $user = User::factory()->create();

    $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertRedirectToRoute('login')
        ->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.',
        ])
        ->assertSessionMissing('_old_input.password');

    $this->assertGuest();
});

test('login requires an email and a password', function () {
    $this->post(route('login.store'), [])
        ->assertSessionHasErrors([
            'email' => 'The email field is required.',
            'password' => 'The password field is required.',
        ]);

    $this->assertGuest();
});

test('invalid login input is rejected', function (string $field, mixed $value, string $message) {
    $payload = ['email' => 'person@example.com', 'password' => 'password'];
    $payload[$field] = $value;

    $this->post(route('login.store'), $payload)
        ->assertSessionHasErrors([$field => $message]);

    $this->assertGuest();
})->with([
    'email must be text' => ['email', ['invalid'], 'The email field must be a string.'],
    'email must be an email address' => ['email', 'invalid', 'The email field must be a valid email address.'],
    'email is too long' => ['email', str_repeat('a', 244).'@example.com', 'The email field must not be greater than 255 characters.'],
    'password must be text' => ['password', ['invalid'], 'The password field must be a string.'],
]);

test('login attempts are limited for the same email and IP', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertTooManyRequests();

    $this->assertGuest();
});

test('an authenticated user cannot open the guest login page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirectToRoute('home');
});

test('users can log out and their session data is cleared', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->withSession(['private-data' => 'clear-on-logout']);

    $this->post(route('logout'))
        ->assertRedirectToRoute('posts.index')
        ->assertSessionMissing('private-data');

    $this->assertGuest();
});

test('guests cannot log out', function () {
    $this->post(route('logout'))
        ->assertRedirectToRoute('login');
});
