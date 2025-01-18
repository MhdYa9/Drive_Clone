<?php

namespace App\Rules;

use App\Models\Folder;
use App\Services\FolderService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use function Laravel\Prompts\note;

class ValidParentFolder implements ValidationRule
{

    public function __construct(public Folder $folder){}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $folder_service = new FolderService($this->folder);
        if(!$folder_service->validDestParent($value)){
            $fail("you cannot move a parent to a subfolder");
        }
    }



}
