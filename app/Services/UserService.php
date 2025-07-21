<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function updateBirthdays(array $birthdays): array
    {
        $results = [
            'success' => [],
            'errors' => []
        ];

        $userIds = array_keys($birthdays);
        
        // Получаем существующих пользователей
        $existingUsers = User::whereIn('id', $userIds)
            ->select('id')
            ->get()
            ->pluck('id')
            ->toArray();

        // Находим несуществующих пользователей
        $nonExistingUsers = array_diff($userIds, $existingUsers);
        foreach ($nonExistingUsers as $userId) {
            $message = "Пользователь {$userId} не найден";
            Log::error($message);
            $results['errors'][$userId] = $message;
        }

        if (empty($existingUsers)) {
            return $results;
        }

        $cases = [];
        $params = [];
        $ids = [];

        foreach ($existingUsers as $userId) {
            $cases[] = "WHEN ? THEN ?";
            $ids[] = $userId;
            $params[] = $userId;
            $params[] = $birthdays[$userId];
            $results['success'][] = $userId;
        }

        $ids = implode(',', $ids);
        $cases = implode(' ', $cases);

        try {
            // Обновление одним запросом
            DB::update("
                UPDATE users 
                SET birthday = CASE id 
                    {$cases}
                END 
                WHERE id IN ({$ids})
            ", $params);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }

        return $results;
    }
} 