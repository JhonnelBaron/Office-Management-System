<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPhotoRequest;
use App\Services\FileService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function store(UploadPhotoRequest $request)
    {
        $file = $this->fileService->uploadPhoto($request);
        return response($file, $file['status']);
    }
}
