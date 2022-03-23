<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Virement
 * 
 * @property int $id
 * @property int $comptes_id
 * @property int $activites_id
 * @property string $type
 * @property float $montant
 * @property string $created_by
 * @property int $created_at
 * 
 * @property \App\Models\Activite $activite
 * @property \App\Models\Compte $compte
 *
 * @package App\Models
 */
class Virement extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'comptes_id' => 'int',
		'activites_id' => 'int',
		'montant' => 'float',
		'created_at' => 'int'
	];

	protected $fillable = [
		'comptes_id',
		'activites_id',
		'type',
		'montant',
		'created_by'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id');
	}

	public function compte()
	{
		return $this->belongsTo(\App\Models\Compte::class, 'comptes_id');
	}
}
