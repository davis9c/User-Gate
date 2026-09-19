<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserCredential;

class Login extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('login/index');
    }

    public function authenticate()
    {
        $rules = [
            'identity' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->to('/login')
                ->withInput()
                ->with('error', 'Username/email dan password wajib diisi.');
        }

        $identity = trim($this->request->getPost('identity'));
        $password = $this->request->getPost('password');

        $userModel = new User();

        $user = $userModel
            ->groupStart()
            ->where('username', $identity)
            ->orWhere('email', $identity)
            ->groupEnd()
            ->first();

        if (!$user || $user['status'] !== 'ACTIVE') {
            return redirect()
                ->to('/login')
                ->withInput()
                ->with('error', 'Username/email atau password salah.');
        }

        $credentialModel = new UserCredential();

        $credential = $credentialModel
            ->where('user_id', $user['id'])
            ->first();

        if (!$credential || !password_verify($password, $credential['password_hash'])) {
            return redirect()
                ->to('/login')
                ->withInput()
                ->with('error', 'Username/email atau password salah.');
        }

        session()->regenerate();

        session()->set([
            'isLoggedIn' => true,
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'email'      => $user['email'],
            'full_name'  => $user['full_name'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()
            ->to('/login')
            ->with('success', 'Anda telah logout.');
    }
}
