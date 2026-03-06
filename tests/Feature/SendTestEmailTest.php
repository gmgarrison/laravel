<?php

use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;

it('sends a test email to the given address', function () {
    Mail::fake();

    $this->artisan('app:send-test-email', ['email' => 'user@example.com'])
        ->expectsOutputToContain('Test email sent to [user@example.com]')
        ->assertSuccessful();

    Mail::assertSent(TestMail::class, function (TestMail $mail) {
        return $mail->hasTo('user@example.com');
    });
});
