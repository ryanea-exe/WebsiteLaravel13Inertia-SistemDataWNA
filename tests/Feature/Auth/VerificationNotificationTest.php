<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

test('sends verification notification', function () {
    if (! Features::enabled(Features::emailVerification())) {
        $this->markTestSkipped('Email verification is disabled.');
    }

    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('home'));

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('does not send verification notification if email is verified', function () {
    if (! Features::enabled(Features::emailVerification())) {
        $this->markTestSkipped('Email verification is disabled.');
    }

    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect('/dashboard');

    Notification::assertNothingSent();
});
