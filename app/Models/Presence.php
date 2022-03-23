<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Presence
 * 
 * @property int $membres_id
 * @property int $ags_id
 * @property string $status
 * @property string $raison
 * 
 * @property \App\Models\Ag $ag
 * @property \App\Models\Membre $membre
 *
 * @package App\Models
 */
class Presence extends Eloquent
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'membres_id' => 'int',
		'ags_id' => 'int'
	];

	protected $fillable = [
		'membres_id',
		'ags_id',
		'status',
		'raison'
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
