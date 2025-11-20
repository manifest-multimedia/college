<?php

namespace App\Livewire;

use App\Models\ElectionCandidate;
use App\Models\ElectionPosition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ElectionCandidateManager extends Component
{
    use WithFileUploads, WithPagination;

    public $position;

    public $candidates = [];

    public $name;

    public $bio;

    public $image;

    public $manifesto;

    public $is_active = true;

    public $display_order = 0;

    public $showForm = false;

    public $existingImage = null;

    public $existingManifesto = null;

    public $candidateId;

    public $isEditing = false;

    public $confirmingDeletion = false;

    public $candidateIdToDelete;

    protected $rules = [
        'name' => 'required|string|max:255',
        'bio' => 'nullable|string',
        'image' => 'nullable|image|max:1024', // 1MB max
        'manifesto' => 'nullable|file|mimes:pdf|max:5120', // 5MB max
        'is_active' => 'boolean',
        'display_order' => 'required|integer|min:0',
    ];

    /**
     * Safely get the image preview URL with robust error handling
     *
     * @return string|null
     */
    protected function getImagePreviewUrl()
    {
        try {
            if (! $this->image) {
                // No image uploaded yet, use existing image if available
                if ($this->existingImage) {
                    return Storage::disk('public')->url($this->existingImage);
                }

                return null;
            }

            // Check if image is a valid UploadedFile instance and has required methods
            if (! method_exists($this->image, 'temporaryUrl')) {
                Log::warning('Image is not a valid UploadedFile or missing temporaryUrl method', [
                    'image_type' => gettype($this->image),
                    'image_class' => get_class($this->image),
                ]);

                return null;
            }

            // Log the image information for debugging
            Log::info('Image metadata for preview URL generation', [
                'mime' => $this->safeGetFileMimeType($this->image),
                'extension' => $this->safeGetFileExtension($this->image),
                'size' => $this->safeGetFileSize($this->image),
            ]);

            return $this->image->temporaryUrl();
        } catch (\Exception $e) {
            Log::error('Error generating image preview URL', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return null instead of throwing an exception
            return null;
        }
    }

    /**
     * Safely get the manifesto preview URL with robust error handling
     *
     * @return string|null
     */
    protected function getManifestoPreviewUrl()
    {
        try {
            if ($this->manifesto && method_exists($this->manifesto, 'getClientOriginalName')) {
                // No preview for PDFs, just return the name
                return null;
            } elseif ($this->existingManifesto) {
                return Storage::disk('public')->url($this->existingManifesto);
            }
        } catch (\Exception $e) {
            Log::error('Error with manifesto preview', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Safely get file MIME type with error handling
     *
     * @param  mixed  $file
     * @return string|null
     */
    protected function safeGetFileMimeType($file)
    {
        try {
            if (method_exists($file, 'getMimeType')) {
                return $file->getMimeType();
            }
        } catch (\Exception $e) {
            Log::warning('Error getting file MIME type', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Safely get file extension with error handling
     *
     * @param  mixed  $file
     * @return string|null
     */
    protected function safeGetFileExtension($file)
    {
        try {
            if (method_exists($file, 'getClientOriginalExtension')) {
                return $file->getClientOriginalExtension();
            }
        } catch (\Exception $e) {
            Log::warning('Error getting file extension', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Safely get file size with error handling
     *
     * @param  mixed  $file
     * @return int|null
     */
    protected function safeGetFileSize($file)
    {
        try {
            if (method_exists($file, 'getSize')) {
                return $file->getSize();
            }
        } catch (\Exception $e) {
            Log::warning('Error getting file size', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function mount(ElectionPosition $position)
    {
        $this->position = $position;
        $this->loadCandidates();
    }

    public function loadCandidates()
    {
        $this->candidates = $this->position->candidates()->orderBy('display_order')->get();
    }

    public function create()
    {
        $this->resetErrorBag();
        $this->reset(['name', 'bio', 'image', 'manifesto', 'isEditing', 'candidateId', 'existingImage', 'existingManifesto']);
        $this->is_active = true;
        $this->display_order = $this->candidates->count();
        $this->showForm = true;
    }

    public function edit(ElectionCandidate $candidate)
    {
        $this->resetErrorBag();
        $this->isEditing = true;
        $this->candidateId = $candidate->id;
        $this->name = $candidate->name;
        $this->bio = $candidate->bio;
        $this->is_active = $candidate->is_active;
        $this->display_order = $candidate->display_order;
        $this->existingImage = $candidate->image_path;
        $this->existingManifesto = $candidate->manifesto_path;
        $this->showForm = true;
    }

    public function save()
    {
        Log::info('Starting candidate save process', [
            'isEditing' => $this->isEditing,
            'position_id' => $this->position->id,
            'candidateId' => $this->candidateId ?? 'new',
            'name' => $this->name,
        ]);

        try {
            // Before normal validation, apply custom validation handling for files
            if ($this->image) {
                // Instead of relying on the validation rule, manually check the uploaded file
                try {
                    $extension = $this->safeGetFileExtension($this->image);
                    $mimeType = $this->safeGetFileMimeType($this->image);
                    $size = $this->safeGetFileSize($this->image);

                    Log::info('Pre-validation image check', [
                        'extension' => $extension,
                        'mime' => $mimeType,
                        'size' => $size,
                    ]);

                    // Manual validation of image type
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if ($mimeType && ! in_array($mimeType, $allowedMimes)) {
                        $this->addError('image', 'The image must be a valid image file type (JPEG, PNG, GIF, WEBP).');

                        return;
                    }

                    // Manual validation of file size if available
                    if ($size && $size > 1024 * 1024) { // 1MB
                        $this->addError('image', 'The image must not be larger than 1MB.');

                        return;
                    }
                } catch (\Exception $e) {
                    Log::error('Error during pre-validation image check', [
                        'error' => $e->getMessage(),
                    ]);
                    $this->addError('image', 'Unable to process the uploaded image. Please try a different file.');

                    return;
                }
            }

            // Standard validation
            Log::info('Validating candidate data');
            $validated = $this->validate();
            Log::info('Validation passed', ['rules' => $this->rules]);

            DB::beginTransaction();
            Log::info('Database transaction started');

            try {
                $data = [
                    'name' => $this->name,
                    'bio' => $this->bio,
                    'is_active' => $this->is_active,
                    'display_order' => $this->display_order,
                ];
                Log::info('Prepared base candidate data', $data);

                // Handle image upload with enhanced error logging and metadata protection
                if ($this->image) {
                    Log::info('Processing image upload');

                    try {
                        // More robust handling of file properties
                        $originalName = method_exists($this->image, 'getClientOriginalName')
                            ? $this->image->getClientOriginalName()
                            : 'image_'.time();

                        $extension = $this->safeGetFileExtension($this->image);
                        if (empty($extension)) {
                            $mimeType = $this->safeGetFileMimeType($this->image);
                            $extension = $this->getMimeExtension($mimeType);

                            Log::info('Assigned extension based on MIME type', [
                                'mime' => $mimeType,
                                'extension' => $extension,
                            ]);
                        }

                        $filename = pathinfo($originalName, PATHINFO_FILENAME);
                        $safeFilename = Str::slug($filename) ?: 'candidate_image';
                        $newFilename = $safeFilename.'_'.time().'.'.$extension;

                        $imagePath = $this->image->storeAs('election-candidates', $newFilename, 'public');
                        Log::info('Image uploaded successfully', ['path' => $imagePath]);
                        $data['image_path'] = $imagePath;

                        // Remove old image if it exists and we're editing
                        if ($this->isEditing && $this->existingImage) {
                            Log::info('Removing old image', ['path' => $this->existingImage]);
                            Storage::disk('public')->delete($this->existingImage);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error uploading image', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        throw $e;
                    }
                }

                // Handle manifesto upload with similar robust handling
                if ($this->manifesto) {
                    Log::info('Processing manifesto upload');

                    try {
                        $originalName = method_exists($this->manifesto, 'getClientOriginalName')
                            ? $this->manifesto->getClientOriginalName()
                            : 'manifesto_'.time();

                        $extension = $this->safeGetFileExtension($this->manifesto) ?: 'pdf';

                        $filename = pathinfo($originalName, PATHINFO_FILENAME);
                        $safeFilename = Str::slug($filename) ?: 'candidate_manifesto';
                        $newFilename = $safeFilename.'_'.time().'.'.$extension;

                        $manifestoPath = $this->manifesto->storeAs('election-manifestos', $newFilename, 'public');
                        Log::info('Manifesto uploaded successfully', ['path' => $manifestoPath]);
                        $data['manifesto_path'] = $manifestoPath;

                        // Remove old manifesto if it exists and we're editing
                        if ($this->isEditing && $this->existingManifesto) {
                            Log::info('Removing old manifesto', ['path' => $this->existingManifesto]);
                            Storage::disk('public')->delete($this->existingManifesto);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error uploading manifesto', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        throw $e;
                    }
                }

                if ($this->isEditing) {
                    Log::info('Updating existing candidate', ['id' => $this->candidateId]);
                    $candidate = ElectionCandidate::findOrFail($this->candidateId);
                    $candidate->update($data);

                    \App\Models\ElectionAuditLog::log(
                        $this->position->election,
                        'admin',
                        auth()->id(),
                        'candidate_updated',
                        'Updated candidate: '.$candidate->name,
                        [
                            'position_id' => $this->position->id,
                            'candidate_id' => $candidate->id,
                        ]
                    );

                    $message = 'Candidate updated successfully!';
                    Log::info('Candidate updated successfully', ['id' => $candidate->id]);
                } else {
                    Log::info('Creating new candidate for position', ['position_id' => $this->position->id]);
                    $data['election_id'] = $this->position->election_id;
                    $data['election_position_id'] = $this->position->id;

                    Log::info('Final candidate data before creation', $data);
                    $candidate = ElectionCandidate::create($data);

                    \App\Models\ElectionAuditLog::log(
                        $this->position->election,
                        'admin',
                        auth()->id(),
                        'candidate_created',
                        'Created candidate: '.$candidate->name,
                        [
                            'position_id' => $this->position->id,
                            'candidate_id' => $candidate->id,
                        ]
                    );

                    $message = 'Candidate created successfully!';
                    Log::info('New candidate created successfully', ['id' => $candidate->id]);
                }

                DB::commit();
                Log::info('Database transaction committed');

                $this->reset(['name', 'bio', 'image', 'manifesto', 'isEditing', 'candidateId', 'existingImage', 'existingManifesto', 'showForm']);
                $this->loadCandidates();

                $this->dispatch('alert', [
                    'type' => 'success',
                    'message' => $message,
                ]);

                Log::info('Candidate save process completed successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during candidate save/update transaction', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'An error occurred: '.$e->getMessage(),
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Candidate validation failed', [
                'errors' => $e->errors(),
            ]);
            throw $e; // Re-throw to let Livewire handle displaying validation errors
        } catch (\Exception $e) {
            Log::error('Unexpected error during candidate save process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'An error occurred: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Convert MIME type to file extension
     *
     * @param  string|null  $mimeType
     * @return string
     */
    protected function getMimeExtension($mimeType)
    {
        if (! $mimeType) {
            return 'jpg'; // Default fallback
        }

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
        ];

        return $mimeToExt[$mimeType] ?? 'jpg';
    }

    public function confirmDelete(ElectionCandidate $candidate)
    {
        $this->confirmingDeletion = true;
        $this->candidateIdToDelete = $candidate->id;
    }

    public function delete()
    {
        $candidate = ElectionCandidate::findOrFail($this->candidateIdToDelete);
        $candidateName = $candidate->name;

        DB::beginTransaction();
        try {
            // Check if there are votes for this candidate
            $votesCount = $candidate->votes()->count();

            if ($votesCount > 0) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Cannot delete this candidate. They have votes associated with them.',
                ]);
                $this->confirmingDeletion = false;
                $this->candidateIdToDelete = null;

                return;
            }

            // Log deletion
            \App\Models\ElectionAuditLog::log(
                $this->position->election,
                'admin',
                auth()->id(),
                'candidate_deleted',
                'Deleted candidate: '.$candidateName,
                [
                    'position_id' => $this->position->id,
                    'candidate_id' => $candidate->id,
                ]
            );

            // Delete associated files
            if ($candidate->image_path) {
                Storage::disk('public')->delete($candidate->image_path);
            }

            if ($candidate->manifesto_path) {
                Storage::disk('public')->delete($candidate->manifesto_path);
            }

            $candidate->delete();
            DB::commit();

            $this->confirmingDeletion = false;
            $this->candidateIdToDelete = null;
            $this->loadCandidates();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Candidate deleted successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'An error occurred: '.$e->getMessage(),
            ]);
        }
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->candidateIdToDelete = null;
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'bio', 'image', 'manifesto', 'isEditing', 'candidateId', 'existingImage', 'existingManifesto', 'showForm']);
    }

    public function toggleActiveStatus(ElectionCandidate $candidate)
    {
        $candidate->update(['is_active' => ! $candidate->is_active]);

        \App\Models\ElectionAuditLog::log(
            $this->position->election,
            'admin',
            auth()->id(),
            'candidate_status_changed',
            'Changed candidate status to: '.($candidate->is_active ? 'active' : 'inactive'),
            [
                'position_id' => $this->position->id,
                'candidate_id' => $candidate->id,
            ]
        );

        $message = $candidate->is_active ? 'Candidate activated' : 'Candidate deactivated';
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => $message,
        ]);

        $this->loadCandidates();
    }

    public function render()
    {
        return view('livewire.election-candidate-manager', [
            'imagePreview' => $this->getImagePreviewUrl(),
            'manifestoPreview' => $this->getManifestoPreviewUrl(),
        ])->layout('components.dashboard.default', ['title' => 'Manage Election Candidates']);
    }
}
