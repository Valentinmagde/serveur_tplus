<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ActivitesGenerique
 * 
 * @property int $id
 * @property int $activites_id
 * 
 * @property \App\Models\Activite $activite
 *
 * @package App\Models
 */
class ActivitesGenerique extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'activites_id' => 'int'
	];

	protected $fillable = [
		'activites_id'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id');
	}
}
