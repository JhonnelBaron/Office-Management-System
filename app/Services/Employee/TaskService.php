<?php

namespace App\Services\Employee;

use App\Events\TaskCreated;
use App\Models\DocumentLink;
use App\Models\Employee\Task;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskService
{
    public function add(array $payload)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $payload['user_id'] = $user->id;
        $payload['status'] = 'In Progress';
        $payload['date_added'] = now();
    
        // Validate if document_links match exactly no_of_document
        if (isset($payload['document_links'])) {
            $documentLinksCount = count($payload['document_links']);
            $noOfDocument = $payload['no_of_document'];
    
            // Check if the number of document links is exactly equal to no_of_document
            if ($documentLinksCount !== $noOfDocument) {
                return [
                    'status' => 422,
                    'message' => "The number of document links must match exactly the specified no_of_document. Expected: $noOfDocument, Got: $documentLinksCount",
                ];
            }
    
            // Validate that each link is a valid URL
            foreach ($payload['document_links'] as $link) {
                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    return $this->errorResponse('One or more document links are invalid.');
                }
            }
        }
    
        $task = Task::create($payload);
    
        // Add document links or placeholders
        $remainingLinks = $payload['no_of_document'];
        if (!empty($payload['document_links'])) {
            foreach ($payload['document_links'] as $link) {
                DocumentLink::create([
                    'user_id' => $user->id,
                    'task_id' => $task->id,
                    'document_link' => $link,
                ]);
                $remainingLinks--;
            }
        }
    
        // Create placeholders for remaining document links if any
        for ($i = 0; $i < $remainingLinks; $i++) {
            DocumentLink::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'document_link' => null,
            ]);
        }
    
        // Eager load document links
        $task = $task->load('documentLinks');
    
        event(new TaskCreated($task));
    
        return [
            'data' => $task, // Include task with loaded document links
            'status' => 201,
            'message' => 'New task added successfully!',
        ];
    }
    
    
    

    public function get()
    {    
        $user = JWTAuth::parseToken()->authenticate();

        $tasks = Task::where('user_id', $user->id)
        ->with('documentLinks') // Eager load the related document links
        ->orderBy('created_at', 'desc')
        ->get();
        return [
            'tasks' => $tasks,
            'message' => 'Tasks retrieved successfully',
            'status' => 200,
        ];
    }

    // public function update(int $id, array $payload)
    // {
    //     $task = Task::find($id);
    
    //     if (!$task) {
    //         return $this->errorResponse('Task not found!');
    //     }

    //         // Ensure only one task can be "In Progress"
    //         if (isset($payload['status']) && $payload['status'] === 'In Progress') {
    //             $existingTaskInProgress = Task::where('status', 'In Progress')
    //                                         ->where('id', '!=', $id)
    //                                         ->exists();

    //             if ($existingTaskInProgress) {
    //                 return [
    //                     'status' => 400,
    //                     'message' => 'Another task is already In Progress. You cannot update this task to In Progress.',
    //                 ];
    //             }
    //         }
    
    //     // Check and handle task completion logic
    //     if (isset($payload['status']) && $payload['status'] === 'Done') {
    //         $payload['date_finished'] = Carbon::now();
    //         if ($task->date_added) {
    //             $dateAdded = Carbon::parse($task->date_added);
    //             $payload['hours_worked'] = $dateAdded->diffInHours(Carbon::now());
    //         } else {
    //             $payload['hours_worked'] = 0;
    //         }
    //     }
    
    //     // Validate the document links count
    //     if (isset($payload['document_links'])) {
    //         $documentLinksCount = count($payload['document_links']);
    //         $noOfDocument = $payload['no_of_document'] ?? $task->no_of_document;
    
    //         if ($documentLinksCount !== $noOfDocument) {
    //             return [
    //                 'status' => 422,
    //                 'message' => "The number of document links must match exactly the specified no_of_document. Expected: $noOfDocument, Got: $documentLinksCount",
    //             ];
    //         }
    
    //         // Separate the document links update logic
    //         $this->updateDocumentLinks($task, $payload['document_links']);
    //     }
    
    //     // Update task fields
    //     $task->update($payload);
    
    //     // Reload task with its document links to reflect the updated data
    //     $task = $task->load('documentLinks');
    
    //     return [
    //         'data' => $task,
    //         'status' => 200,
    //         'message' => 'Task updated successfully!',
    //     ];
    // }

//     public function update(int $id, array $payload)
// {
//     $task = Task::find($id);
//     $user = JWTAuth::parseToken()->authenticate();


//     if (!$task) {
//         return $this->errorResponse('Task not found!');
//     }

//     // Ensure only one task can be "In Progress"
//     if (isset($payload['status']) && $payload['status'] === 'In Progress') {
//         $existingTaskInProgress = Task::where('status', 'In Progress')
//                                     ->where('user_id', $user->id)
//                                     ->where('id', '!=', $id)
//                                     ->exists();

//         if ($existingTaskInProgress) {
//             return [
//                 'status' => 400,
//                 'message' => 'Another task is already In Progress. You cannot update this task to In Progress.',
//             ];
//         }

//         // Handle resuming from "Suspended"
//         if ($task->status === 'Suspended') {
//             $suspensionDuration = Carbon::parse($task->time_suspended)->diffInSeconds(Carbon::now());
//             $task->time_suspended = null;
//             $task->hours_worked = ($task->hours_worked ?? 0) + $suspensionDuration / 3600; // Convert seconds to hours
//         }
//     }

//     // Handle task suspension
//     if (isset($payload['status']) && $payload['status'] === 'Suspended') {
//         $payload['time_suspended'] = Carbon::now();
//     }

//     // Handle task completion logic
//     if (isset($payload['status']) && $payload['status'] === 'Done') {
//         $payload['date_finished'] = Carbon::now();

//         // Calculate hours worked considering suspension
//         if ($task->status === 'Suspended' && $task->time_suspended) {
//             $suspensionDuration = Carbon::parse($task->time_suspended)->diffInSeconds(Carbon::now());
//             $task->hours_worked = ($task->hours_worked ?? 0) + $suspensionDuration / 3600; // Convert seconds to hours
//         }

//         if ($task->date_added) {
//             $dateAdded = Carbon::parse($task->date_added);
//             $totalWorkedDuration = $dateAdded->diffInSeconds(Carbon::now()) - ($suspensionDuration ?? 0);
//             $payload['hours_worked'] = $totalWorkedDuration / 3600; // Convert seconds to hours
//         } else {
//             $payload['hours_worked'] = $task->hours_worked ?? 0;
//         }

//         $payload['time_suspended'] = null; // Clear suspension on completion
//     }

//     // Validate the document links count
//     if (isset($payload['document_links'])) {
//         $documentLinksCount = count($payload['document_links']);
//         $noOfDocument = $payload['no_of_document'] ?? $task->no_of_document;

//         if ($documentLinksCount !== $noOfDocument) {
//             return [
//                 'status' => 422,
//                 'message' => "The number of document links must match exactly the specified no_of_document. Expected: $noOfDocument, Got: $documentLinksCount",
//             ];
//         }

//         // Separate the document links update logic
//         $this->updateDocumentLinks($task, $payload['document_links']);
//     }

//     // Update task fields
//     $task->update($payload);

//     // Reload task with its document links to reflect the updated data
//     $task = $task->load('documentLinks');

//     return [
//         'data' => $task,
//         'status' => 200,
//         'message' => 'Task updated successfully!',
//     ];
// }
// public function update(int $id, array $payload)
// {
//     $task = Task::find($id);
//     $user = JWTAuth::parseToken()->authenticate();

//     if (!$task) {
//         return $this->errorResponse('Task not found!');
//     }

//     if (isset($payload['status']) && $payload['status'] === 'In Progress') {
//         $existingTaskInProgress = Task::where('status', 'In Progress')
//             ->where('user_id', $user->id)
//             ->where('id', '!=', $id)
//             ->exists();

//         if ($existingTaskInProgress) {
//             return [
//                 'status' => 400,
//                 'message' => 'Another task is already In Progress. You cannot update this task to In Progress.',
//             ];
//         }

//         if ($task->status === 'Suspended' && $task->time_suspended) {
//             $suspensionDuration = Carbon::parse($task->time_suspended)->diffInSeconds(Carbon::now());
//             $task->hours_worked = ($task->hours_worked ?? 0) + $suspensionDuration / 3600;
//             $task->time_suspended = null;
//         }
//     }

//     if (isset($payload['status']) && $payload['status'] === 'Suspended') {
//         if ($task->status === 'In Progress' && $task->updated_at) {
//             $workDuration = $task->updated_at->diffInSeconds(Carbon::now());
//             $task->hours_worked = ($task->hours_worked ?? 0) + $workDuration / 3600;
//         }
//         $payload['hours_worked'] = $task->hours_worked;
//         $payload['time_suspended'] = Carbon::now();
//     }

//     if (isset($payload['status']) && $payload['status'] === 'Done') {
//         if ($task->status === 'In Progress') {
//             $workDuration = $task->updated_at->diffInSeconds(Carbon::now());
//             $task->hours_worked = ($task->hours_worked ?? 0) + $workDuration / 3600;
//         }
//         $payload['hours_worked'] = $task->hours_worked;
//         $payload['date_finished'] = Carbon::now();
//         $payload['time_suspended'] = null;
//     }

//     if (isset($payload['document_links'])) {
//         $documentLinksCount = count($payload['document_links']);
//         $noOfDocument = $payload['no_of_document'] ?? $task->no_of_document;

//         if ($documentLinksCount !== $noOfDocument) {
//             return [
//                 'status' => 422,
//                 'message' => "The number of document links must match exactly the specified no_of_document. Expected: $noOfDocument, Got: $documentLinksCount",
//             ];
//         }
//         $this->updateDocumentLinks($task, $payload['document_links']);
//     }

//     $task->update(array_merge($payload, ['hours_worked' => $task->hours_worked]));
//     $task = $task->load('documentLinks');

//     return [
//         'data' => $task,
//         'status' => 200,
//         'message' => 'Task updated successfully!',
//     ];
// }

public function update(int $id, array $payload)
{
    $task = Task::find($id);
    $user = JWTAuth::parseToken()->authenticate();

    if (!$task) {
        return $this->errorResponse('Task not found!');
    }

    if (isset($payload['status']) && $payload['status'] === 'In Progress') {
        $existingTaskInProgress = Task::where('status', 'In Progress')
            ->where('user_id', $user->id)
            ->where('id', '!=', $id)
            ->exists();

        if ($existingTaskInProgress) {
            return [
                'status' => 400,
                'message' => 'Another task is already In Progress. You cannot update this task to In Progress.',
            ];
        }

        // Resume from suspended state
        if ($task->status === 'Suspended' && $task->time_suspended) {
            $suspensionDuration = Carbon::parse($task->time_suspended)->diffInSeconds(Carbon::now());
            $task->time_suspended = null; // Clear suspension timestamp
        }
    }

    if (isset($payload['status']) && $payload['status'] === 'Suspended') {
        if ($task->status === 'In Progress' && $task->updated_at) {
            $workDuration = $task->updated_at->diffInSeconds(Carbon::now());
            $task->hours_worked = ($task->hours_worked ?? 0) + $workDuration / 3600;
        }
        $payload['hours_worked'] = $task->hours_worked;
        $payload['time_suspended'] = Carbon::now();
    }

    if (isset($payload['status']) && $payload['status'] === 'Done') {
        if ($task->status === 'Suspended') {
            return [
                'status' => 400,
                'message' => 'A task in Suspended status cannot be marked as Done directly. Please set it to In Progress first.',
            ];
        }
        
        if ($task->status === 'In Progress') {
            $workDuration = $task->updated_at->diffInSeconds(Carbon::now());
            $task->hours_worked = ($task->hours_worked ?? 0) + $workDuration / 3600;
        }
        $payload['hours_worked'] = $task->hours_worked;
        $payload['date_finished'] = Carbon::now();
        $payload['time_suspended'] = null;
    }

    if (isset($payload['document_links'])) {
        $documentLinksCount = count($payload['document_links']);
        $noOfDocument = $payload['no_of_document'] ?? $task->no_of_document;

        if ($documentLinksCount !== $noOfDocument) {
            return [
                'status' => 422,
                'message' => "The number of document links must match exactly the specified no_of_document. Expected: $noOfDocument, Got: $documentLinksCount",
            ];
        }
        $this->updateDocumentLinks($task, $payload['document_links']);
    }

    $task->update(array_merge($payload, ['hours_worked' => $task->hours_worked]));
    $task = $task->load('documentLinks');

    return [
        'data' => $task,
        'status' => 200,
        'message' => 'Task updated successfully!',
    ];
}



    
    private function updateDocumentLinks(Task $task, array $documentLinks)
    {
        // Get all current links associated with the task
        $currentLinks = $task->documentLinks;
        $currentCount = $currentLinks->count();
        $newCount = count($documentLinks);
    
        // Update existing links
        foreach ($documentLinks as $index => $link) {
            if ($index < $currentCount) {
                // Update existing link
                $currentLinks[$index]->update([
                    'document_link' => $link['document_link'], // Update the link URL
                ]);
            } else {
                // Create new links if count exceeds current count
                $task->documentLinks()->create([
                    'user_id' => $task->user_id,
                    'document_link' => $link['document_link'],
                    'task_id' => $task->id,
                ]);
            }
        }
    
        // Delete excess links if `no_of_documents` decreases
        if ($currentCount > $newCount) {
            $extraLinks = $currentLinks->slice($newCount);
            foreach ($extraLinks as $extraLink) {
                $extraLink->delete();
            }
        }
    }
    
    
    
    public function show($id)
    {
        $task = Task::with('documentLinks')->find($id);
        
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return [
            'status' => 200,
            'data' => $task
        ];
    }
    


    private function errorResponse($message): array
    {
        return [
            'status' => '400',
            'message' => $message,
        ];
    }
}