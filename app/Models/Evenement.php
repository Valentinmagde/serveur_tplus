<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Evenement
 * 
 * @property int $id
 * @property int $activites_id
 * @property int $date_event
 * @property string $lieu_event
 * @property string $quoi
 * @property string $presence
 * @property int $date_fin
 * @property string $commentaire
 * @property string $serie
 * @property string $cycle
 * 
 * @property \App\Models\Activite $activite
 * @property \Illuminate\Database\Eloquent\Collection $presence_evenements
 *
 * @package App\Models
 */
class Evenement extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'activites_id' => 'int',
		'date_event' => 'int',
		'date_fin' => 'int'
	];

	protected $fillable = [
		'activites_id',
		'date_event',
		'lieu_event',
		'quoi',
		'presence',
		'date_fin',
		'commentaire',
		'serie',
		'cycle'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id');
	}

	public function presence_evenements()
	{
		return $this->hasMany(\App\Models\PresenceEvenement::class, 'evenements_id');
	}
}
