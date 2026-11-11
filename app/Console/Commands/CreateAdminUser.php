<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {--from-env : Read credentials from ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD}';

    protected $description = 'Create or promote an administrator account';

    public function handle(): int
    {
        if ($this->option('from-env')) {
            $name = config('admin.bootstrap.name');
            $email = config('admin.bootstrap.email');
            $password = config('admin.bootstrap.password');

            if (! is_string($name) || trim($name) === ''
                || ! is_string($email) || trim($email) === ''
                || ! is_string($password) || $password === '') {
                $this->components->error('Set ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD before using --from-env.');

                return self::FAILURE;
            }
        } else {
            $name = $this->ask('Admin name');
            $email = $this->ask('Admin email');
            $password = null;
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email],
            ['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:190']],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user && ! $this->confirm("Account {$email} already exists. Promote and update it?")) {
            $this->components->info('No changes made.');

            return self::SUCCESS;
        }

        if (! $this->option('from-env')) {
            $password = $this->secret('Password (minimum 12 characters)');
        }

        $passwordValidator = Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string', Password::min(12)]],
        );

        if ($passwordValidator->fails()) {
            foreach ($passwordValidator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        if (! $this->option('from-env')
            && ! hash_equals((string) $password, (string) $this->secret('Confirm password'))) {
            $this->components->error('Password confirmation does not match.');

            return self::FAILURE;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'adalah_admin' => true,
            ],
        );

        $this->components->success("Admin account {$email} is ready.");

        return self::SUCCESS;
    }
}
