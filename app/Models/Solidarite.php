<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Solidarite
 * 
 * @property int $id
 * @property int $activites_id
 * @property float $montant_fond_solidarite
 * @property int $date_created
 * @property int $date_updated
 * @property string $created_by
 * @property string $updated_by
 * @property string $delai_mise_a_niveau
 * 
 * @property \App\Models\Activite $activite
 * @property \Illuminate\Database\Eloquent\Collection $assistances
 *
 * @package App\Models
 */
class Solidarite extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'activites_id' => 'int',
		'montant_fond_solidarite' => 'float',
		'date_created' => 'int',
		'date_updated' => 'int'
	];

	protected $fillable = [
		'activites_id',
		'montant_fond_solidarite',
		'date_created',
		'date_updated',
		'created_by',
		'updated_by',
		'delai_mise_a_niveau'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id');
	}

	public function assistances()
	{
		return $this->hasMany(\App\Models\Assistance::class, 'solidarites_id');
	}
}
