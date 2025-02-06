<?php

namespace App\Services;

use App\Models\LoginPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function uploadPhoto(Request $request)
    {
        // Get the base64 encoded image data
        $imageData = $request->input('photo');

        // Extract the file extension (jpeg, png, etc.)
        $extension = strpos($imageData, 'jpeg') ? 'jpg' : 'png'; 
        $fileName = 'photo_' . time() . '.' . $extension;

        // Decode the base64 image
        $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageData));

        // Define the file path
        $filePath = 'public/login_photos/' . $fileName;

        // Save the file to storage
        file_put_contents(storage_path('app/' . $filePath), $image);

        // Generate the file URL
        $fileUrl = Storage::url('login_photos/' . $fileName);

        // Save the file record in the LoginPhoto model
        $loginPhoto = LoginPhoto::create([
            'user_id' => $request->user()->id, // Assuming you have an authenticated user
            'file_path' => $filePath,
            'dateTime_taken' => now(),
            'status' => 'active', // Set the status
        ]);

        // Return the URL or any other necessary response
        return [
            'url' => $fileUrl, 
            'login_photo_id' => $loginPhoto->id,
            'status' => 201,
        ];
    }
}
