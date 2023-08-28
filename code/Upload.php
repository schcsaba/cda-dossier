<?php

namespace App\Models;

use App\Console\Commands\UploadTrigger;
use App\Models\Utils\UploadStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upload extends Model
{
    use HasUlids;

    protected $fillable = [
        'parameters',
        'client_app',
        'upload_type'
    ];

    protected $hidden = ['client_app','deleted_at'];

    protected $casts = ['parameters' => 'array'];

    /**
     * @return HasMany
     */
    public function steps(): HasMany
    {
        return $this->hasMany(UploadStep::class);
    }

    /**
     * @return HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(UploadResult::class);
    }

    /**
     * Status of the documents upload
     *
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->attributes['status'] = UploadStatus::STATUS_CODE[$status];
        $this->save();
    }

}
