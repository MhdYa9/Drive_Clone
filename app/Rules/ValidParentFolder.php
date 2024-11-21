<?php

namespace App\Rules;

use App\Models\Node;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use function Laravel\Prompts\note;

class ValidParentFolder implements ValidationRule
{

    private $flag = false;

    private $parent;

    private $adj = [];

    private $visited = [];

    public function dfs(int $n)
    {
        $this->visited[$n] = true;
        foreach ($this->adj[$n] as $subNode) {
            if($subNode == $this->parent){
                $this->flag = true;
                return;
            }
            if(!isset($this->visited[$subNode])) {
                $this->dfs($subNode);
            }
        }
    }

    public function __construct(private int $node)
    {
    }


    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->parent = $value;
        $nodes = Node::select('id','parent_id')->whereType('folder')->get();
        foreach ($nodes as $node) {
             $this->adj[$node->parent_id][] = $node->id;
             $this->adj[$node->id] = [];
        }

        $this->dfs($this->node);


        if($this->flag){
            $fail("you can't move your folder to a subFolder inside it");
        }
    }



}
