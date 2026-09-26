<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin
                            {email : Email address of the user to promote (or create)}
                            {--name= : Name to use when a new account is created}
                            {--password= : Password for a new account (prompted if omitted)}';

    protected $description = 'Promote a user to administrator, creating the account if it does not exist';

    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));

        if (Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']])->fails()) {
            $this->components->error('Please provide a valid email address.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            if ($this->input->isInteractive() && ! $this->option('password')
                && ! $this->components->confirm("No user with {$email} exists. Create one?", true)) {
                return self::FAILURE;
            }

            $password = $this->option('password') ?: ($this->input->isInteractive() ? $this->secret('Password') : null);

            $validator = Validator::make(
                ['password' => $password],
                ['password' => ['required', 'string', Password::defaults()]],
            );

            if ($validator->fails()) {
                $this->components->error($validator->errors()->first('password'));

                return self::FAILURE;
            }

            $user = new User([
                'name' => $this->option('name') ?: Str::headline(Str::before($email, '@')),
                'email' => $email,
                'password' => $password,
            ]);
            $user->email_verified_at = now();
        }

        if ($user->exists && $user->isAdmin()) {
            $this->components->info("{$email} is already an administrator.");

            return self::SUCCESS;
        }

        $user->is_admin = true;
        $user->save();

        $this->components->info("{$email} is now an administrator.");

        return self::SUCCESS;
    }
}
