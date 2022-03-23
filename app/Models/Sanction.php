<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Sanction
 * 
 * @property int $id
 * @property int $membres_id
 * @property int $ags_id
 * @property int $montant
 * @property string $commentaire
 * @property string $type
 * 
 * @property \App\Models\Ag $ag
 * @property \App\Models\Membre $membre
 *
 * @package App\Models
 */
class Sanction extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'membres_id' => 'int',
		'ags_id' => 'int',
		'montant' => 'int'
	];

	protected $fillable = [
		'membres_id',
		'ags_id',
		'montant',
		'commentaire',
		'type'
	];

	public function ag()
	{
		return $this->belongsTo(\App\Models\Ag::class, 'ags_id');
	}

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}
}
