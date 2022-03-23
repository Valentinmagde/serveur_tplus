<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class MiseDeFond
 * 
 * @property int $id
 * @property int $membres_id
 * @property int $mutuelles_id
 * @property float $montant
 * @property int $date_versement
 * @property int $date_created
 * @property int $date_updated
 * @property string $created_by
 * @property string $updated_by
 * 
 * @property \App\Models\Membre $membre
 * @property \App\Models\Mutuelle $mutuelle
 *
 * @package App\Models
 */
class MiseDeFond extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'membres_id' => 'int',
		'mutuelles_id' => 'int',
		'montant' => 'float',
		'date_versement' => 'int',
		'date_created' => 'int',
		'date_updated' => 'int'
	];

	protected $fillable = [
		'membres_id',
		'mutuelles_id',
		'montant',
		'date_versement',
		'date_created',
		'date_updated',
		'created_by',
		'updated_by'
	];

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}

	public function mutuelle()
	{
		return $this->belongsTo(\App\Models\Mutuelle::class, 'mutuelles_id');
	}
}
