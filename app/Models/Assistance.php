<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Assistance
 * 
 * @property int $id
 * @property int $membres_id
 * @property int $solidarites_id
 * @property int $solidarites_activites_id
 * @property string $type
 * @property float $montant_alloue
 * @property string $etat
 * @property int $echeances_id
 * @property int $date_evenement
 * @property int $date_created
 * @property int $date_updated
 * 
 * @property \App\Models\Echeancier $echeancier
 * @property \App\Models\Membre $membre
 * @property \App\Models\Activite $activite
 * @property \App\Models\Solidarite $solidarite
 * @property \Illuminate\Database\Eloquent\Collection $associations_has_service_payements
 *
 * @package App\Models
 */
class Assistance extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'membres_id' => 'int',
		'solidarites_id' => 'int',
		'solidarites_activites_id' => 'int',
		'montant_alloue' => 'float',
		'echeances_id' => 'int',
		'date_evenement' => 'int',
		'date_created' => 'int',
		'date_updated' => 'int'
	];

	protected $fillable = [
		'membres_id',
		'solidarites_id',
		'solidarites_activites_id',
		'type',
		'montant_alloue',
		'etat',
		'echeances_id',
		'date_evenement',
		'date_created',
		'date_updated'
	];

	public function echeancier()
	{
		return $this->belongsTo(\App\Models\Echeancier::class, 'echeances_id');
	}

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'solidarites_activites_id');
	}

	public function solidarite()
	{
		return $this->belongsTo(\App\Models\Solidarite::class, 'solidarites_id');
	}

	public function associations_has_service_payements()
	{
		return $this->hasMany(\App\Models\AssociationsHasServicePayement::class, 'associations_id');
	}
}
