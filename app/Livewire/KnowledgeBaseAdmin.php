<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class KnowledgeBaseAdmin extends Component
{
    use WithPagination;

    public $categories;
    public $showModal = false;
    public $editMode = false;
    public $articleId;
    
    // Form fields
    public $title = '';
    public $knowledge_base_category_id = '';
    public $excerpt = '';
    public $content = '';
    public $is_published = false;
    public $is_featured = false;
    public $meta_tags = [];
    
    // Filters
    public $searchTerm = '';
    public $categoryFilter = '';
    public $statusFilter = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'title' => 'required|string|max:255',
        'knowledge_base_category_id' => 'required|exists:knowledge_base_categories,id',
        'excerpt' => 'nullable|string|max:500',
        'content' => 'required|string',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function mount()
    {
        // Check authorization
        if (!auth()->user()->hasAnyRole(['System', 'IT Manager', 'Super Admin'])) {
            abort(403, 'Unauthorized access.');
        }

        $this->categories = KnowledgeBaseCategory::orderBy('order')->get();
    }

    public function render()
    {
        $query = KnowledgeBaseArticle::with(['category', 'creator', 'updater'])
            ->withTrashed();

        // Apply search filter
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('excerpt', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('content', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Apply category filter
        if ($this->categoryFilter) {
            $query->where('knowledge_base_category_id', $this->categoryFilter);
        }

        // Apply status filter
        if ($this->statusFilter === 'published') {
            $query->where('is_published', true);
        } elseif ($this->statusFilter === 'draft') {
            $query->where('is_published', false);
        } elseif ($this->statusFilter === 'trashed') {
            $query->onlyTrashed();
        }

        $articles = $query->latest('updated_at')->paginate(15);

        return view('livewire.knowledge-base-admin', [
            'articles' => $articles,
        ]);
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($articleId)
    {
        $article = KnowledgeBaseArticle::withTrashed()->findOrFail($articleId);
        
        $this->articleId = $article->id;
        $this->title = $article->title;
        $this->knowledge_base_category_id = $article->knowledge_base_category_id;
        $this->excerpt = $article->excerpt;
        $this->content = $article->content;
        $this->is_published = $article->is_published;
        $this->is_featured = $article->is_featured;
        $this->meta_tags = $article->meta_tags ?? [];
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'knowledge_base_category_id' => $this->knowledge_base_category_id,
            'excerpt' => $this->excerpt ?: Str::limit(strip_tags($this->content), 150),
            'content' => $this->content,
            'is_published' => $this->is_published,
            'is_featured' => $this->is_featured,
            'meta_tags' => $this->meta_tags,
        ];

        if ($this->editMode) {
            $article = KnowledgeBaseArticle::withTrashed()->findOrFail($this->articleId);
            $article->update([
                ...$data,
                'updated_by' => auth()->id(),
            ]);

            session()->flash('message', 'Article updated successfully.');
        } else {
            KnowledgeBaseArticle::create([
                ...$data,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'published_at' => $this->is_published ? now() : null,
            ]);

            session()->flash('message', 'Article created successfully.');
        }

        $this->closeModal();
    }

    public function togglePublish($articleId)
    {
        $article = KnowledgeBaseArticle::findOrFail($articleId);
        $article->update([
            'is_published' => !$article->is_published,
            'published_at' => !$article->is_published ? now() : $article->published_at,
            'updated_by' => auth()->id(),
        ]);

        $status = $article->is_published ? 'published' : 'unpublished';
        session()->flash('message', "Article {$status} successfully.");
    }

    public function toggleFeatured($articleId)
    {
        $article = KnowledgeBaseArticle::findOrFail($articleId);
        $article->update([
            'is_featured' => !$article->is_featured,
            'updated_by' => auth()->id(),
        ]);

        $status = $article->is_featured ? 'featured' : 'unfeatured';
        session()->flash('message', "Article {$status} successfully.");
    }

    public function deleteArticle($articleId)
    {
        $article = KnowledgeBaseArticle::findOrFail($articleId);
        $article->delete();

        session()->flash('message', 'Article moved to trash.');
    }

    public function restoreArticle($articleId)
    {
        $article = KnowledgeBaseArticle::withTrashed()->findOrFail($articleId);
        $article->restore();

        session()->flash('message', 'Article restored successfully.');
    }

    public function forceDeleteArticle($articleId)
    {
        $article = KnowledgeBaseArticle::withTrashed()->findOrFail($articleId);
        $article->forceDelete();

        session()->flash('message', 'Article permanently deleted.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'articleId',
            'title',
            'knowledge_base_category_id',
            'excerpt',
            'content',
            'is_published',
            'is_featured',
            'meta_tags',
        ]);
        $this->resetValidation();
    }
}
