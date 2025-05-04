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
        $this->validate();
        
        DB::beginTransaction();
        try {
            $data = [
                'name' => $this->name,
                'bio' => $this->bio,
                'is_active' => $this->is_active,
                'display_order' => $this->display_order,
            ];
            
            // Handle image upload
            if ($this->image) {
                $imagePath = $this->image->store('election-candidates', 'public');
                $data['image_path'] = $imagePath;
                
                // Remove old image if it exists and we're editing
                if ($this->isEditing && $this->existingImage) {
                    Storage::disk('public')->delete($this->existingImage);
                }
            }
            
            // Handle manifesto upload
            if ($this->manifesto) {
                $manifestoPath = $this->manifesto->store('election-manifestos', 'public');
                $data['manifesto_path'] = $manifestoPath;
                
                // Remove old manifesto if it exists and we're editing
                if ($this->isEditing && $this->existingManifesto) {
                    Storage::disk('public')->delete($this->existingManifesto);
                }
            }
            
            if ($this->isEditing) {
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
            } else {
                $data['election_position_id'] = $this->position->id;
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
            }
            
            DB::commit();
            $this->reset(['name', 'bio', 'image', 'manifesto', 'isEditing', 'candidateId', 'existingImage', 'existingManifesto', 'showForm']);
            $this->loadCandidates();
            
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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