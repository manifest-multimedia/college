<?php

namespace App\Livewire;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use Livewire\Component;

class KnowledgeBase extends Component
{
    public $searchTerm = '';

    public $selectedCategorySlug = null;

    public $selectedArticleSlug = null;

    public $view = 'categories'; // categories, category-articles, article

    public function mount()
    {
        // Initialize
    }

    public function render()
    {
        $categories = KnowledgeBaseCategory::where('is_active', true)
            ->withCount('publishedArticles')
            ->orderBy('order')
            ->get();

        $popularArticles = KnowledgeBaseArticle::published()
            ->with('category')
            ->popular(3)
            ->get();

        $categoryArticles = [];
        $selectedArticle = null;

        if ($this->selectedCategorySlug) {
            $category = KnowledgeBaseCategory::where('slug', $this->selectedCategorySlug)->first();
            if ($category) {
                $categoryArticles = $category->publishedArticles()
                    ->orderBy('views_count', 'desc')
                    ->get();
            }
        }

        if ($this->selectedArticleSlug) {
            $selectedArticle = KnowledgeBaseArticle::published()
                ->with(['category', 'creator'])
                ->where('slug', $this->selectedArticleSlug)
                ->first();

            if ($selectedArticle) {
                $selectedArticle->incrementViews();
            }
        }

        $searchResults = [];
        if (! empty($this->searchTerm)) {
            $searchResults = KnowledgeBaseArticle::published()
                ->with('category')
                ->where(function ($query) {
                    $query->where('title', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('content', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('excerpt', 'like', '%'.$this->searchTerm.'%');
                })
                ->get();
        }

        return view('livewire.knowledge-base', [
            'categories' => $categories,
            'popularArticles' => $popularArticles,
            'categoryArticles' => $categoryArticles,
            'selectedArticle' => $selectedArticle,
            'searchResults' => $searchResults,
        ]);
    }

    public function viewCategory($slug)
    {
        $this->selectedCategorySlug = $slug;
        $this->selectedArticleSlug = null;
        $this->view = 'category-articles';
        $this->searchTerm = '';
    }

    public function viewArticle($slug, $categorySlug = null)
    {
        $this->selectedArticleSlug = $slug;
        $this->selectedCategorySlug = $categorySlug;
        $this->view = 'article';
        $this->searchTerm = '';
    }

    public function backToCategories()
    {
        $this->selectedCategorySlug = null;
        $this->selectedArticleSlug = null;
        $this->view = 'categories';
        $this->searchTerm = '';
    }

    public function backToCategoryArticles()
    {
        $this->selectedArticleSlug = null;
        $this->view = 'category-articles';
    }

    public function markHelpful($articleId)
    {
        $article = KnowledgeBaseArticle::find($articleId);
        if ($article) {
            $article->markHelpful();
            session()->flash('success', 'Thank you for your feedback!');
        }
    }

    public function markNotHelpful($articleId)
    {
        $article = KnowledgeBaseArticle::find($articleId);
        if ($article) {
            $article->markNotHelpful();
            session()->flash('info', 'Thank you for your feedback. We\'ll work to improve this article.');
        }
    }

    public function updatedSearchTerm()
    {
        if (! empty($this->searchTerm)) {
            $this->view = 'search';
        } else {
            $this->backToCategories();
        }
    }
}
