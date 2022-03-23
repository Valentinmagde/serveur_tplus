<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class LotsTontine
 * 
 * @property int $id
 * @property float $montant
 * @property int $date_bouffe
 * @property string $created_by
 * @property int $date_created
 * @property string $updated_by
 * @property int $date_updated
 * @property int $tontines_id
 * @property int $comptes_id
 * @property int $echeanciers_id
 * @property string $etat
 * @property string $type
 * @property int $montant_recu
 * @property float $enchere
 * 
 * @property \App\Models\Compte $compte
 * @property \App\Models\Echeancier $echeancier
 * @property \App\Models\Tontine $tontine
 *
 * @package App\Models
 */
class LotsTontine extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'montant' => 'float',
		'date_bouffe' => 'int',
		'date_created' => 'int',
		'date_updated' => 'int',
		'tontines_id' => 'int',
		'comptes_id' => 'int',
		'echeanciers_id' => 'int',
		'montant_recu' => 'int',
		'enchere' => 'float'
	];

	protected $fillable = [
		'montant',
		'date_bouffe',
		'created_by',
		'date_created',
		'updated_by',
		'date_updated',
		'tontines_id',
		'comptes_id',
		'echeanciers_id',
		'etat',
		'type',
		'montant_recu',
		'enchere'
	];

	public function compte()
	{
		return $this->belongsTo(\App\Models\Compte::class, 'comptes_id');
	}

	public function echeancier()
	{
		return $this->belongsTo(\App\Models\Echeancier::class, 'echeanciers_id');
	}

	public function tontine()
	{
		return $this->belongsTo(\App\Models\Tontine::class, 'tontines_id');
	}
}
