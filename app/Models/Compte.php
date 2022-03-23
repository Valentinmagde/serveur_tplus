<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Compte
 * 
 * @property int $id
 * @property int $activites_id
 * @property int $membres_id
 * @property float $dette
 * @property float $solde_anterieur
 * @property string $nombre_noms
 * @property float $montant_cotisation
 * @property float $solde
 * @property float $dette_c
 * @property float $dette_a
 * @property float $avoir
 * @property float $interet
 * 
 * @property \App\Models\Membre $membre
 * @property \App\Models\Activite $activite
 * @property \Illuminate\Database\Eloquent\Collection $dettes
 * @property \Illuminate\Database\Eloquent\Collection $echeanciers
 * @property \Illuminate\Database\Eloquent\Collection $lots_tontines
 * @property \Illuminate\Database\Eloquent\Collection $transactions
 * @property \Illuminate\Database\Eloquent\Collection $transferts
 * @property \Illuminate\Database\Eloquent\Collection $virements
 *
 * @package App\Models
 */
class Compte extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'activites_id' => 'int',
		'membres_id' => 'int',
		'dette' => 'float',
		'solde_anterieur' => 'float',
		'montant_cotisation' => 'float',
		'solde' => 'float',
		'dette_c' => 'float',
		'dette_a' => 'float',
		'avoir' => 'float',
		'interet' => 'float',
		'deleted_at' => 'int',
	];

	protected $fillable = [
		'activites_id',
		'membres_id',
		'dette',
		'solde_anterieur',
		'nombre_noms',
		'montant_cotisation',
		'solde',
		'dette_c',
		'dette_a',
		'avoir',
		'interet',
		'a_supprimer',
		'deleted_at',
	];

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id');
	}

	public function dettes()
	{
		return $this->hasMany(\App\Models\Dette::class, 'comptes_id');
	}

	public function echeanciers()
	{
		return $this->hasMany(\App\Models\Echeancier::class, 'comptes_id');
	}

	public function lots_tontines()
	{
		return $this->hasMany(\App\Models\LotsTontine::class, 'comptes_id');
	}

	public function transactions()
	{
		return $this->hasMany(\App\Models\Transaction::class, 'comptes_id');
	}

	public function transferts()
	{
		return $this->hasMany(\App\Models\Transfert::class, 'recepteur');
	}

	public function virements()
	{
		return $this->hasMany(\App\Models\Virement::class, 'comptes_id');
	}
}
