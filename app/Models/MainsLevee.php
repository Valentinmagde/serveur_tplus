<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class MainsLevee
 * 
 * @property int $id
 * @property int $activites_id
 * @property float $montant_minimum
 * @property int $date_limite
 * @property bool $obligatoire
 * @property int $membres_id
 * 
 * @property \App\Models\Activite $activite
 *
 * @package App\Models
 */
class MainsLevee extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'activites_id' => 'int',
		'montant_minimum' => 'float',
		'date_limite' => 'int',
		'obligatoire' => 'bool',
		'membres_id' => 'int'
	];

	protected $fillable = [
		'activites_id',
		'montant_minimum',
		'date_limite',
		'obligatoire',
		'membres_id'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id');
	}
}
