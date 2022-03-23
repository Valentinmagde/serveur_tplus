<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Tach
 * 
 * @property int $id
 * @property int $projets_id
 * @property int $projets_activites_id
 * @property int $utilisateurs_id
 * @property string $nom
 * @property int $date_debut
 * @property int $date_fin
 * @property float $budget
 * @property string $etat
 * 
 * @property \App\Models\Activite $activite
 * @property \App\Models\Projet $projet
 * @property \App\Models\Utilisateur $utilisateur
 *
 * @package App\Models
 */
class Tach extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'projets_id' => 'int',
		'projets_activites_id' => 'int',
		'utilisateurs_id' => 'int',
		'date_debut' => 'int',
		'date_fin' => 'int',
		'budget' => 'float'
	];

	protected $fillable = [
		'projets_id',
		'projets_activites_id',
		'utilisateurs_id',
		'nom',
		'date_debut',
		'date_fin',
		'budget',
		'etat'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'projets_activites_id');
	}

	public function projet()
	{
		return $this->belongsTo(\App\Models\Projet::class, 'projets_id');
	}

	public function utilisateur()
	{
		return $this->belongsTo(\App\Models\Utilisateur::class, 'utilisateurs_id');
	}
}
