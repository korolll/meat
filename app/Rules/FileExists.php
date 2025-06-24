<?php

namespace App\Rules;

use App\Models\File;
use Illuminate\Contracts\Validation\Rule;

class FileExists implements Rule
{
    /**
     * @var string|null
     */
    private $fileCategoryId;

    /**
     * @param string|null $fileCategoryId
     */
    public function __construct($fileCategoryId = null)
    {
        $this->fileCategoryId = $fileCategoryId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $query = File::where('uuid', $value);

        if ($this->fileCategoryId) {
            $query->where('file_category_id', $this->fileCategoryId);
        }

        if (user() && !user()->is_admin) {
            $query->where('user_uuid', user()->uuid);
        }

        return $query->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.exists');
    }
}
