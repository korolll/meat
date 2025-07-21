<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserBirthdaysRequest;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function updateBirthdays(UpdateUserBirthdaysRequest $request)
    {
        try {
            $results = $this->userService->updateBirthdays($request->input('birthdays'));
            
            return response([
                'status' => 'success',
                'message' => 'Даты рождения обновлены',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => 'Ошибка при обновлении дат рождения',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 