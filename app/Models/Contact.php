<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * Get the profile image associated with the contact.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function profileImage()
    {
        return $this->belongsTo(Media::class, 'profile_image_id');
    }

    /**
     * Get all documents associated with the contact (Many-to-Many).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'contact_document');
    }
}
