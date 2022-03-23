<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Nouvelle
 * 
 * @property int $id
 * @property int $associations_id
 * @property int $membres_id
 * @property string $titre
 * @property string $photo
 * @property string $description
 * @property string $categorie
 * @property int $date_nouvelle
 * @property int $create_at
 * @property int $update_at
 * @property string $etat
 * 
 * @property \App\Models\Association $association
 * @property \App\Models\Membre $membre
 * @property \Illuminate\Database\Eloquent\Collection $commentaire_nouvelles
 *
 * @package App\Models
 */
class Nouvelle extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'associations_id' => 'int',
		'membres_id' => 'int',
		'date_nouvelle' => 'int',
		'create_at' => 'int',
		'update_at' => 'int'
	];

	protected $fillable = [
		'associations_id',
		'membres_id',
		'titre',
		'photo',
		'description',
		'categorie',
		'date_nouvelle',
		'create_at',
		'update_at',
		'etat'
	];

	public function association()
	{
		return $this->belongsTo(\App\Models\Association::class, 'associations_id');
	}

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}

	public function commentaire_nouvelles()
	{
		return $this->hasMany(\App\Models\CommentaireNouvelle::class, 'nouvelles_id');
	}
}
