<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class IndonesianCopyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('id');
    }

    public function test_required_message_is_in_indonesian(): void
    {
        $validator = Validator::make(['name' => ''], ['name' => ['required']]);

        $this->assertSame('Nama wajib diisi.', $validator->errors()->first('name'));
    }

    public function test_password_length_message_is_in_indonesian(): void
    {
        $validator = Validator::make(['password' => 'abc'], ['password' => [Password::min(8)]]);

        $this->assertSame('Kata sandi minimal 8 karakter.', $validator->errors()->first('password'));
    }

    public function test_reset_password_email_uses_store_brand(): void
    {
        $user = new User(['name' => 'Budi', 'email' => 'budi@demo.local']);

        $mail = (new ResetPasswordNotification('token-uji'))->toMail($user);

        $this->assertStringContainsString('Thethingscorner', $mail->subject);
        $this->assertStringNotContainsString('The Things Corner', $mail->subject);
    }
}
