<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Ag
 * 
 * @property int $id
 * @property int $cycles_id
 * @property int $membres_id
 * @property int $create_at
 * @property int $update_at
 * @property int $date_ag
 * @property string $lieu_ag
 * @property string $etat
 * @property string $file
 * @property int $date_cloture
 * 
 * @property \App\Models\Cycle $cycle
 * @property \App\Models\Membre $membre
 * @property \Illuminate\Database\Eloquent\Collection $presences
 * @property \Illuminate\Database\Eloquent\Collection $rapports
 * @property \Illuminate\Database\Eloquent\Collection $sanctions
 *
 * @package App\Models
 */
class Ag extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'cycles_id' => 'int',
		'membres_id' => 'int',
		'create_at' => 'int',
		'update_at' => 'int',
		'date_ag' => 'int',
		'date_cloture' => 'int'
	];

	protected $fillable = [
		'cycles_id',
		'membres_id',
		'create_at',
		'update_at',
		'date_ag',
		'lieu_ag',
		'etat',
		'file',
		'date_cloture'
	];

	public function cycle()
	{
		return $this->belongsTo(\App\Models\Cycle::class, 'cycles_id');
	}

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}

	public function presences()
	{
		return $this->hasMany(\App\Models\Presence::class, 'ags_id');
	}

	public function rapports()
	{
		return $this->hasMany(\App\Models\Rapport::class, 'ags_id');
	}

	public function sanctions()
	{
		return $this->hasMany(\App\Models\Sanction::class, 'ags_id');
	}
}
