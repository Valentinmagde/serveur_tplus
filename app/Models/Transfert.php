<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Transfert
 * 
 * @property int $id
 * @property int $expediteur
 * @property int $recepteur
 * @property float $montant
 * @property string $libelle
 * @property string $created_by
 * @property int $created_at
 * 
 * @property \App\Models\Compte $compte
 *
 * @package App\Models
 */
class Transfert extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'expediteur' => 'int',
		'recepteur' => 'int',
		'montant' => 'float',
		'created_at' => 'int'
	];

	protected $fillable = [
		'expediteur',
		'recepteur',
		'montant',
		'libelle',
		'created_by',
		'created_at'
	];

	public function compte()
	{
		return $this->belongsTo(\App\Models\Compte::class, 'recepteur');
	}
}
