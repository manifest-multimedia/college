<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chat_session_id',
        'user_id',
        'type',
        'is_document',
        'message',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'json',
        'is_document' => 'boolean',
    ];

    /**
     * Get the chat session that this message belongs to.
     */
    public function chatSession()
    {
        return $this->belongsTo(ChatSession::class);
    }

    /**
     * Get the user associated with this message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Check if this message is a document.
     */
    public function isDocument(): bool
    {
        return $this->is_document;
    }
    
    /**
     * Get the file extension of the document.
     */
    public function getFileExtension(): ?string
    {
        if (!$this->file_name) {
            return null;
        }
        
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }
    
    /**
     * Check if the document is an image.
     */
    public function isImage(): bool
    {
        if (!$this->is_document) {
            return false;
        }
        
        $imageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($this->mime_type, $imageTypes);
    }
    
    /**
     * Check if the document is a PDF.
     */
    public function isPdf(): bool
    {
        if (!$this->is_document) {
            return false;
        }
        
        return $this->mime_type === 'application/pdf';
    }
}
