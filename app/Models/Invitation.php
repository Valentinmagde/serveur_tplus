<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Invitation
 * 
 * @property int $id
 * @property int $associations_id
 * @property int $membres_id
 * @property string $code
 * 
 * @property \App\Models\Association $association
 * @property \App\Models\Membre $membre
 *
 * @package App\Models
 */
class Invitation extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'associations_id' => 'int',
		'membres_id' => 'int'
	];

	protected $fillable = [
		'associations_id',
		'membres_id',
		'code'
	];

	public function association()
	{
		return $this->belongsTo(\App\Models\Association::class, 'associations_id');
	}

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}
}
