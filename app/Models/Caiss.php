<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Caiss
 * 
 * @property int $id
 * @property int $activites_id1
 * @property string $en_caisse
 * @property string $etat
 * @property int $date_created
 * @property string $created_by
 * 
 * @property \App\Models\Activite $activite
 *
 * @package App\Models
 */
class Caiss extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'activites_id1' => 'int',
		'date_created' => 'int'
	];

	protected $fillable = [
		'activites_id1',
		'en_caisse',
		'etat',
		'date_created',
		'created_by'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id1');
	}
}
