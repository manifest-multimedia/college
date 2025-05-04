<?php

namespace App\Livewire;

use App\Models\ElectionPosition;
use App\Models\ElectionCandidate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ElectionCandidateManager extends Component
{
    use WithPagination, WithFileUploads;

    public $position;
    public $candidates = [];
    
    public $name;
    public $bio;
    public $image;
    public $manifesto;
    public $is_active = true;
    public $display_order = 0;
    public $showForm = false; // Add this property to track form visibility

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
        $this->showForm = true; // Show the form modal
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
        $this->showForm = true; // Show the form modal
    }

    public function save()
    {
        Log::info('Starting candidate save process', [
            'isEditing' => $this->isEditing,
            'position_id' => $this->position->id, 
            'candidateId' => $this->candidateId ?? 'new',
            'name' => $this->name,
            'has_image' => !empty($this->image),
            'has_manifesto' => !empty($this->manifesto)
        ]);
        
        try {
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
                
                // Handle image upload
                if ($this->image) {
                    Log::info('Processing image upload', [
                        'originalName' => $this->image->getClientOriginalName(),
                        'size' => $this->image->getSize(),
                        'mimeType' => $this->image->getMimeType()
                    ]);
                    
                    try {
                        $imagePath = $this->image->store('election-candidates', 'public');
                        Log::info('Image uploaded successfully', ['path' => $imagePath]);
                        $data['image_path'] = $imagePath;
                        
                        // Remove old image if it exists and we're editing
                        if ($this->isEditing && $this->existingImage) {
                            Log::info('Removing old image', ['path' => $this->existingImage]);
                            Storage::disk('public')->delete($this->existingImage);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error uploading image', ['error' => $e->getMessage()]);
                        throw $e;
                    }
                }
                
                // Handle manifesto upload
                if ($this->manifesto) {
                    Log::info('Processing manifesto upload', [
                        'originalName' => $this->manifesto->getClientOriginalName(),
                        'size' => $this->manifesto->getSize(),
                        'mimeType' => $this->manifesto->getMimeType()
                    ]);
                    
                    try {
                        $manifestoPath = $this->manifesto->store('election-manifestos', 'public');
                        Log::info('Manifesto uploaded successfully', ['path' => $manifestoPath]);
                        $data['manifesto_path'] = $manifestoPath;
                        
                        // Remove old manifesto if it exists and we're editing
                        if ($this->isEditing && $this->existingManifesto) {
                            Log::info('Removing old manifesto', ['path' => $this->existingManifesto]);
                            Storage::disk('public')->delete($this->existingManifesto);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error uploading manifesto', ['error' => $e->getMessage()]);
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
                        'Updated candidate: ' . $candidate->name,
                        [
                            'position_id' => $this->position->id,
                            'candidate_id' => $candidate->id
                        ]
                    );
                    
                    $message = 'Candidate updated successfully!';
                    Log::info('Candidate updated successfully', ['id' => $candidate->id]);
                } else {
                    Log::info('Creating new candidate for position', ['position_id' => $this->position->id]);
                    $data['election_id'] = $this->position->election_id; // Add election_id
                    $data['election_position_id'] = $this->position->id;
                    
                    Log::info('Final candidate data before creation', $data);
                    $candidate = ElectionCandidate::create($data);
                    
                    \App\Models\ElectionAuditLog::log(
                        $this->position->election,
                        'admin',
                        auth()->id(),
                        'candidate_created',
                        'Created candidate: ' . $candidate->name,
                        [
                            'position_id' => $this->position->id,
                            'candidate_id' => $candidate->id
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
                    'message' => $message
                ]);
                
                Log::info('Candidate save process completed successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during candidate save/update transaction', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'An error occurred: ' . $e->getMessage()
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
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
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
                    'message' => 'Cannot delete this candidate. They have votes associated with them.'
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
                'Deleted candidate: ' . $candidateName,
                [
                    'position_id' => $this->position->id,
                    'candidate_id' => $candidate->id
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
                'message' => 'Candidate deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
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
        $candidate->update(['is_active' => !$candidate->is_active]);
        
        \App\Models\ElectionAuditLog::log(
            $this->position->election,
            'admin',
            auth()->id(),
            'candidate_status_changed',
            'Changed candidate status to: ' . ($candidate->is_active ? 'active' : 'inactive'),
            [
                'position_id' => $this->position->id,
                'candidate_id' => $candidate->id
            ]
        );
        
        $message = $candidate->is_active ? 'Candidate activated' : 'Candidate deactivated';
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => $message
        ]);
        
        $this->loadCandidates();
    }

    public function render()
    {
        return view('livewire.election-candidate-manager')->layout('components.dashboard.default', ['title' => 'Manage Election Candidates']);
    }
}