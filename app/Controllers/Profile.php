<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserCredential;

class Profile extends BaseController
{
    public function index()
    {
        $userId = session()->get('user_id');

        $userModel = new User();

        $user = $userModel->find($userId);

        if (!$user) {
            session()->destroy();

            return redirect()
                ->to('/login')
                ->with('error', 'Data user tidak ditemukan.');
        }

        return view('profile/index', [
            'title' => 'My Profile',
            'user'  => $user,
        ]);
    }

    public function update()
    {
        $userId = session()->get('user_id');

        $userModel = new User();

        $user = $userModel->find($userId);

        if (!$user) {
            session()->destroy();

            return redirect()
                ->to('/login')
                ->with('error', 'Data user tidak ditemukan.');
        }

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[150]',
            'username'  => 'required|min_length[3]|max_length[100]|alpha_numeric',
            'email'     => 'required|valid_email|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $username = trim($this->request->getPost('username'));
        $email    = trim($this->request->getPost('email'));

        $existingUsername = $userModel
            ->where('username', $username)
            ->where('id !=', $userId)
            ->first();

        if ($existingUsername) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Username sudah digunakan.');
        }

        $existingEmail = $userModel
            ->where('email', $email)
            ->where('id !=', $userId)
            ->first();

        if ($existingEmail) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Email sudah digunakan.');
        }

        $userModel->update($userId, [
            'full_name' => trim($this->request->getPost('full_name')),
            'username'  => $username,
            'email'     => $email,
        ]);

        // Update session agar nama terbaru langsung digunakan.
        session()->set([
            'username' => $username,
            'email'    => $email,
            'full_name' => trim($this->request->getPost('full_name')),
        ]);

        return redirect()
            ->to('/profile')
            ->with('success', 'Profile berhasil diperbarui.');
    }
    public function password()
    {
        return view('profile/password', [
            'title' => 'Change Password',
        ]);
    }

    public function updatePassword()
    {
        $userId = session()->get('user_id');

        $credentialModel = new UserCredential();

        $credential = $credentialModel
            ->where('user_id', $userId)
            ->first();

        if (!$credential) {
            return redirect()
                ->to('/profile')
                ->with('error', 'Credential user tidak ditemukan.');
        }

        $rules = [
            'current_password' => 'required',
            'password' => 'required|min_length[8]',
            'password_confirmation' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword     = $this->request->getPost('password');

        if (!password_verify($currentPassword, $credential['password_hash'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Password lama tidak benar.');
        }

        if (password_verify($newPassword, $credential['password_hash'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Password baru harus berbeda dengan password lama.');
        }

        $credentialModel->update($credential['id'], [
            'password_hash' => password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            ),
        ]);

        return redirect()
            ->to('/profile')
            ->with('success', 'Password berhasil diubah.');
    }
}
