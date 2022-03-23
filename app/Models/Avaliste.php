<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Avaliste
 * 
 * @property int $id
 * @property int $membres_id1
 * @property int $membres_id2
 * 
 * @property \App\Models\Membre $membre
 *
 * @package App\Models
 */
class Avaliste extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'membres_id1' => 'int',
		'membres_id2' => 'int'
	];

	protected $fillable = [
		'membres_id1',
		'membres_id2'
	];

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id2');
	}
}
