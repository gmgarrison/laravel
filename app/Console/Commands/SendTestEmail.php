<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    protected $signature = 'app:send-test-email {email : The email address to send the test email to}';

    protected $description = 'Send a test email to a given email address';

    public function handle(): int
    {
        $email = $this->argument('email');

        Mail::to($email)->send(new TestMail);

        $this->components->info("Test email sent to [{$email}].");

        return Command::SUCCESS;
    }
}
