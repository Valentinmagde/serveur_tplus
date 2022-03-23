<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Mutuelle
 * 
 * @property int $id
 * @property int $activites_id
 * @property float $mise_minimum
 * @property float $maximum_empruntable
 * @property string $type_maximum_empruntable
 * @property string $duree_pret
 * @property string $taux_interet
 * @property string $methode_calcul_interet
 * @property string $paiement_interet
 * @property string $remboursement
 * 
 * @property \App\Models\Activite $activite
 * @property \Illuminate\Database\Eloquent\Collection $credits
 * @property \Illuminate\Database\Eloquent\Collection $mise_de_fonds
 *
 * @package App\Models
 */
class Mutuelle extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'activites_id' => 'int',
		'mise_minimum' => 'float',
		'maximum_empruntable' => 'float'
	];

	protected $fillable = [
		'activites_id',
		'mise_minimum',
		'maximum_empruntable',
		'type_maximum_empruntable',
		'duree_pret',
		'taux_interet',
		'methode_calcul_interet',
		'paiement_interet',
		'remboursement'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id');
	}

	public function credits()
	{
		return $this->hasMany(\App\Models\Credit::class, 'mutuelles_id');
	}

	public function mise_de_fonds()
	{
		return $this->hasMany(\App\Models\MiseDeFond::class, 'mutuelles_id');
	}
}
