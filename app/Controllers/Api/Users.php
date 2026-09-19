<?php

namespace App\Controllers\Api;

use App\Exceptions\UserException;
use App\Services\UserService;

class Users extends ApiController
{
    protected UserService $userService;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController(
            $request,
            $response,
            $logger
        );

        $this->userService = new UserService();
    }

    public function index()
    {
        $permissionError = $this->requirePermission('user.read');

        if ($permissionError !== null) {
            return $permissionError;
        }

        $search = trim(
            $this->request->getGet('search') ?? ''
        );

        $page = max(
            1,
            (int) ($this->request->getGet('page') ?? 1)
        );

        $perPage = (int) (
            $this->request->getGet('per_page') ?? 20
        );

        $result = $this->userService->getList(
            $search,
            $page,
            $perPage
        );

        return $this->apiResponse->success(
            $this->response,
            $result['data'],
            'Users retrieved successfully.',
            200,
            $result['meta']
        );
    }

    public function create()
    {
        $permissionError = $this->requirePermission('user.create');

        if ($permissionError !== null) {
            return $permissionError;
        }

        $data = $this->request->getJSON(true);

        $rules = [
            'username'  => 'required|min_length[3]|max_length[100]|alpha_numeric',
            'email'     => 'required|valid_email|max_length[255]',
            'full_name' => 'required|min_length[3]|max_length[150]',
            'password'  => 'required|min_length[8]',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->apiResponse->error(
                $this->response,
                'Validation failed.',
                422,
                $this->validator->getErrors()
            );
        }

        try {
            $user = $this->userService->create($data);

            return $this->apiResponse->success(
                $this->response,
                $user,
                'User created successfully.',
                201
            );
        } catch (UserException $e) {
            return $this->errorResponse($e);
        }
    }

    public function show($id)
    {
        $permissionError = $this->requirePermission('user.read');

        if ($permissionError !== null) {
            return $permissionError;
        }

        $user = $this->userService->findById($id);

        // lanjut kode yang sudah ada...

        if (!$user) {
            return $this->apiResponse->error(
                $this->response,
                'User not found.',
                404
            );
        }

        return $this->apiResponse->success(
            $this->response,
            $user,
            'User retrieved successfully.'
        );
    }

    public function update($id)
    {
        $permissionError = $this->requirePermission('user.update');

        if ($permissionError !== null) {
            return $permissionError;
        }

        $data = $this->request->getJSON(true);

        // lanjut kode yang sudah ada...

        $rules = [
            'username'  => 'required|min_length[3]|max_length[100]|alpha_numeric',
            'email'     => 'required|valid_email|max_length[255]',
            'full_name' => 'required|min_length[3]|max_length[150]',
            'status'    => 'required|in_list[ACTIVE,INACTIVE]',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->apiResponse->error(
                $this->response,
                'Validation failed.',
                422,
                $this->validator->getErrors()
            );
        }

        try {
            $user = $this->userService->update(
                $id,
                $data
            );

            return $this->apiResponse->success(
                $this->response,
                $user,
                'User updated successfully.'
            );
        } catch (UserException $e) {
            return $this->errorResponse($e);
        }
    }

    public function delete($id)
    {
        $permissionError = $this->requirePermission('user.delete');

        if ($permissionError !== null) {
            return $permissionError;
        }

        try {
            $user = $this->userService->delete($id);

            // lanjut kode yang sudah ada...

            return $this->apiResponse->success(
                $this->response,
                $user,
                'User deleted successfully.'
            );
        } catch (UserException $e) {
            return $this->errorResponse($e);
        }
    }
}
