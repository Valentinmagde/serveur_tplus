<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Projet
 * 
 * @property int $id
 * @property int $activites_id
 * @property int $date_debut
 * @property int $date_fin
 * @property string $pm
 * @property float $budget
 * @property int $create_at
 * @property string $etat
 * 
 * @property \App\Models\Activite $activite
 * @property \Illuminate\Database\Eloquent\Collection $taches
 *
 * @package App\Models
 */
class Projet extends Eloquent
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int',
		'activites_id' => 'int',
		'date_debut' => 'int',
		'date_fin' => 'int',
		'budget' => 'float',
		'create_at' => 'int'
	];

	protected $fillable = [
		'activites_id',
		'date_debut',
		'date_fin',
		'pm',
		'budget',
		'create_at',
		'etat'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id');
	}

	public function taches()
	{
		return $this->hasMany(\App\Models\Tach::class, 'projets_id');
	}
}
