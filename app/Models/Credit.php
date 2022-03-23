<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Credit
 * 
 * @property int $id
 * @property int $membres_id
 * @property int $mutuelles_id
 * @property int $mutuelles_activites_id
 * @property int $date_demande
 * @property string $etat
 * @property string $echeance
 * @property float $montant_credit
 * @property float $montant_interet
 * @property float $montant_restant
 * @property int $date_limite
 * 
 * @property \App\Models\Membre $membre
 * @property \App\Models\Activite $activite
 * @property \App\Models\Mutuelle $mutuelle
 *
 * @package App\Models
 */
class Credit extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'membres_id' => 'int',
		'mutuelles_id' => 'int',
		'mutuelles_activites_id' => 'int',
		'date_demande' => 'int',
		'montant_credit' => 'float',
		'montant_interet' => 'float',
		'montant_restant' => 'float',
		'date_limite' => 'int'
	];

	protected $fillable = [
		'membres_id',
		'mutuelles_id',
		'mutuelles_activites_id',
		'date_demande',
		'etat',
		'echeance',
		'montant_credit',
		'montant_interet',
		'montant_restant',
		'date_limite'
	];

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'mutuelles_activites_id');
	}

	public function mutuelle()
	{
		return $this->belongsTo(\App\Models\Mutuelle::class, 'mutuelles_id');
	}
}
