<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class PresenceEvenement
 * 
 * @property int $membres_id
 * @property int $evenements_id
 * @property string $status
 * @property string $raison
 * 
 * @property \App\Models\Evenement $evenement
 * @property \App\Models\Membre $membre
 *
 * @package App\Models
 */
class PresenceEvenement extends Eloquent
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'membres_id' => 'int',
		'evenements_id' => 'int'
	];

	protected $fillable = [
		'status',
		'raison'
	];

	public function evenement()
	{
		return $this->belongsTo(\App\Models\Evenement::class, 'evenements_id');
	}

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}
}
