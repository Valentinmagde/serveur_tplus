<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Rapport
 * 
 * @property int $id
 * @property int $ags_id
 * @property string $resume
 * @property string $etat
 * @property int $create_at
 * @property int $update_at
 * @property string $created_by
 * @property string $hote
 * @property string $presidence
 * @property string $secretaire
 * @property string $lieu
 * @property int $date_effective
 * 
 * @property \App\Models\Ag $ag
 * @property \Illuminate\Database\Eloquent\Collection $sections
 *
 * @package App\Models
 */
class Rapport extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'ags_id' => 'int',
		'create_at' => 'int',
		'update_at' => 'int',
		'date_effective' => 'int'
	];

	protected $fillable = [
		'ags_id',
		'resume',
		'etat',
		'create_at',
		'update_at',
		'created_by',
		'hote',
		'presidence',
		'secretaire',
		'lieu',
		'date_effective',
		'jitsi_room',
	];

	public function ag()
	{
		return $this->belongsTo(\App\Models\Ag::class, 'ags_id');
	}

	public function sections()
	{
		return $this->hasMany(\App\Models\Section::class, 'rapports_id');
	}
}
