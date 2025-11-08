<?php

namespace Modules\Page\Models;

use App\Models\BaseModel;
use App\Models\Traits\HasSlug;
use App\Trait\CustomFieldsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\VendorScope;

class Page extends BaseModel
{
    use CustomFieldsTrait;
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    protected $table = 'pages';

    protected $fillable = ['slug', 'name', 'description', 'sequence', 'status', 'show_for_booking', 'created_by', 'updated_by'];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Page\database\factories\PageFactory::new();
    }
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new VendorScope);

        // Ensure only one page per created_by can have show_for_booking = 1
        static::updating(function ($page) {
            if ($page->isDirty('show_for_booking') && $page->show_for_booking == 1) {
                // Set all other pages with same created_by to show_for_booking = 0
                static::where('created_by', $page->created_by)
                    ->where('id', '!=', $page->id)
                    ->update(['show_for_booking' => 0]);
            }
        });

        static::creating(function ($page) {
            if ($page->show_for_booking == 1) {
                // Set all other pages with same created_by to show_for_booking = 0
                static::where('created_by', $page->created_by)
                    ->update(['show_for_booking' => 0]);
            }
        });
    }
}
