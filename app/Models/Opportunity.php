<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    protected $guarded = [];

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }
}
