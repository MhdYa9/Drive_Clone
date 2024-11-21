<?php

namespace App\Rules;

use App\Models\Node;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidFolderName implements ValidationRule
{

    /*
     *
     * @param parent folder id
     * */

    public function __construct(private int $parent_id ,private string $type )
    {

    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(Node::whereParentId($this->parent_id)->whereName($value)->whereType($this->type)->exists()){
            $fail("the name you just entered already exists in the same directory");
        }
    }
}
