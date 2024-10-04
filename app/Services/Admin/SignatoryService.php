<?php

namespace App\Services\Admin;

use App\Models\Admin\Signatory;

class SignatoryService
{
    public function add(array $payload)
    {
        $sign = Signatory::create($payload);

        return [
            
        ];
        
    }
}

$doc = HrsDocument::create($payload);

return [
    'data' => $doc,
    'status' => 201,
    'message' => 'New document added successfully!'
];