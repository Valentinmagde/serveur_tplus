<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Dette
 * 
 * @property int $id
 * @property string $libelle
 * @property float $montant
 * @property int $date
 * @property int $comptes_id
 * @property string $type
 * 
 * @property \App\Models\Compte $compte
 *
 * @package App\Models
 */
class Dette extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'montant' => 'float',
		'date' => 'int',
		'comptes_id' => 'int'
	];

	protected $fillable = [
		'libelle',
		'montant',
		'date',
		'comptes_id',
		'type'
	];

	public function compte()
	{
		return $this->belongsTo(\App\Models\Compte::class, 'comptes_id');
	}
}
