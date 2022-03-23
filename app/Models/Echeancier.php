<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Echeancier
 * 
 * @property int $id
 * @property int $membres_id
 * @property int $comptes_id
 * @property float $montant
 * @property string $debit_credit
 * @property string $libelle
 * @property int $date_limite
 * @property string $etat
 * @property string $created_by
 * @property int $date_created
 * @property int $priorite
 * @property float $montant_realise
 * @property string $serie
 * @property int $next_date_in
 * 
 * @property \App\Models\Compte $compte
 * @property \App\Models\Membre $membre
 * @property \Illuminate\Database\Eloquent\Collection $assistances
 * @property \Illuminate\Database\Eloquent\Collection $lots_tontines
 *
 * @package App\Models
 */
class Echeancier extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'membres_id' => 'int',
		'comptes_id' => 'int',
		'montant' => 'float',
		'date_limite' => 'int',
		'date_created' => 'int',
		'priorite' => 'int',
		'montant_realise' => 'float',
		'next_date_in' => 'int'
	];

	protected $fillable = [
		'membres_id',
		'comptes_id',
		'montant',
		'debit_credit',
		'libelle',
		'date_limite',
		'etat',
		'created_by',
		'date_created',
		'priorite',
		'montant_realise',
		'serie',
		'next_date_in',
		'type'
	];

	public function compte()
	{
		return $this->belongsTo(\App\Models\Compte::class, 'comptes_id');
	}

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}

	public function assistances()
	{
		return $this->hasMany(\App\Models\Assistance::class, 'echeances_id');
	}

	public function lots_tontines()
	{
		return $this->hasMany(\App\Models\LotsTontine::class, 'echeanciers_id');
	}
}
