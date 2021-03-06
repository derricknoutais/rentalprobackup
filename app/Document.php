<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['type', 'compagnie_id'];
    public function voitures()
    {
        return $this->belongsToMany('App\Voiture', 'voiture_documents', 'document_id', 'voiture_id');
    }
}
