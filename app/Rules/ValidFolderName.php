<?php

namespace App\Rules;

use App\Models\File;
use App\Models\Folder;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidFolderName implements ValidationRule
{

    /*
     *
     * @param parent folder id
     * */

    public function __construct(private int $parent)
    {

    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(Folder::whereParentId($this->parent)->whereName($value)->exists() || File::whereFolderId($this->parent)->whereName($value)->exists()){
            $fail("the name you just entered already exists in the same directory");
        }
    }
}
